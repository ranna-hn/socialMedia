<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteGroupMemberRequest;
use App\Http\Requests\UpdateGroupMembershipRequest;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use App\Notifications\GroupInvitationNotification;
use App\Notifications\GroupMembershipApprovedNotification;
use App\Notifications\GroupMembershipRejectedNotification;
use App\Support\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupMembershipController extends Controller
{
    public function join(Group $group): RedirectResponse
    {
        $user = request()->user();

        $membership = DB::transaction(function () use ($group, $user): GroupUser {
            return GroupUser::updateOrCreate(
                [
                    'group_id' => $group->id,
                    'user_id' => $user->id,
                ],
                [
                    'created_by' => $user->id,
                    'status' => $group->auto_approval ? GroupUser::STATUS_APPROVED : GroupUser::STATUS_PENDING,
                    'role' => GroupUser::ROLE_MEMBER,
                    'token_used' => $group->auto_approval ? now() : null,
                ]
            );
        });

        if (! $group->auto_approval && ($membership->wasRecentlyCreated || $membership->wasChanged('status'))) {
            $adminIds = $group->approvedMembers()
                ->wherePivot('role', GroupUser::ROLE_ADMIN)
                ->pluck('users.id')
                ->push($group->user_id)
                ->unique();

            foreach ($adminIds as $adminId) {
                AppNotification::send(
                    $adminId,
                    'group_join_requested',
                    __('econature.notifications.group_join_requested', [
                        'username' => $user->username,
                        'group' => $group->name,
                    ]),
                    route('groups.show', $group),
                    $user,
                );
            }
        }

        $message = $group->auto_approval
            ? __('econature.groups.joined')
            : __('econature.groups.request_sent');

        return back()->with('success', $message);
    }

    public function invite(InviteGroupMemberRequest $request, Group $group): RedirectResponse
    {
        $inviter = $request->user();

        DB::transaction(function () use ($request, $group, $inviter): void {
            foreach (collect($request->validated('user_ids'))->unique() as $userId) {
                $membership = GroupUser::updateOrCreate(
                    [
                        'group_id' => $group->id,
                        'user_id' => $userId,
                    ],
                    [
                        'created_by' => $inviter->id,
                        'status' => GroupUser::STATUS_PENDING,
                        'role' => GroupUser::ROLE_MEMBER,
                        'token' => Str::random(64),
                        'token_expires_date' => now()->addDays(7),
                        'token_used' => null,
                    ]
                );

                $invitee = User::find($userId);
                $invitee?->notify(new GroupInvitationNotification($group, $inviter, $membership));

                if ($invitee) {
                    AppNotification::send(
                        $invitee,
                        'group_invitation',
                        __('econature.notifications.group_invitation_app', ['group' => $group->name]),
                        route('groups.show', $group),
                        $inviter,
                    );
                }
            }
        });

        return back()->with('success', __('econature.groups.invitation_sent'));
    }

    public function approve(Group $group, GroupUser $membership): RedirectResponse
    {
        $this->authorizeMembershipAction($group, $membership);

        DB::transaction(function () use ($membership): void {
            $membership->update([
                'status' => GroupUser::STATUS_APPROVED,
                'token_used' => now(),
            ]);

            $membership->loadMissing('user', 'group');
            $membership->user->notify(new GroupMembershipApprovedNotification($membership->group));
            AppNotification::send(
                $membership->user,
                'group_join_approved',
                __('econature.notifications.group_join_approved', ['group' => $membership->group->name]),
                route('groups.show', $membership->group),
                request()->user(),
            );
        });

        return back()->with('success', __('econature.groups.member_approved'));
    }

    public function reject(Group $group, GroupUser $membership): RedirectResponse
    {
        $this->authorizeMembershipAction($group, $membership);

        DB::transaction(function () use ($membership): void {
            $membership->update([
                'status' => GroupUser::STATUS_REJECTED,
            ]);

            $membership->loadMissing('user', 'group');
            $membership->user->notify(new GroupMembershipRejectedNotification($membership->group));
            AppNotification::send(
                $membership->user,
                'group_join_rejected',
                __('econature.notifications.group_join_rejected', ['group' => $membership->group->name]),
                route('groups.show', $membership->group),
                request()->user(),
            );
        });

        return back()->with('success', __('econature.groups.member_rejected'));
    }

    public function updateRole(UpdateGroupMembershipRequest $request, Group $group, GroupUser $membership): RedirectResponse
    {
        $membership->update([
            'role' => $request->validated('role'),
        ]);

        return back()->with('success', __('econature.groups.role_updated'));
    }

    public function destroy(Group $group, GroupUser $membership): RedirectResponse
    {
        $user = request()->user();
        $isSelf = $membership->user_id === $user->id;

        abort_unless($group->isAdmin($user) || $isSelf, 403);
        abort_if($membership->user_id === $group->user_id, 403, __('econature.groups.owner_cannot_leave'));
        abort_unless($membership->group_id === $group->id, 404);

        $membership->delete();

        return back()->with('success', __('econature.groups.member_removed'));
    }

    private function authorizeMembershipAction(Group $group, GroupUser $membership): void
    {
        abort_unless($group->isAdmin(request()->user()), 403);
        abort_unless($membership->group_id === $group->id, 404);
    }
}
