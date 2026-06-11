<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Resources\GroupMembershipResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\User;
use App\Support\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $coverPath = null;

        try {
            $group = DB::transaction(function () use ($data, $request, &$coverPath): Group {
                if ($request->hasFile('cover')) {
                    $coverPath = $request->file('cover')->store('groups/covers', 'public');
                }

                $group = Group::create([
                    'name' => $data['name'],
                    'about' => $data['about'] ?? null,
                    'auto_approval' => $data['auto_approval'],
                    'cover_path' => $coverPath,
                    'user_id' => $request->user()->id,
                ]);

                GroupUser::create([
                    'group_id' => $group->id,
                    'user_id' => $request->user()->id,
                    'created_by' => $request->user()->id,
                    'status' => GroupUser::STATUS_APPROVED,
                    'role' => GroupUser::ROLE_ADMIN,
                    'token_used' => now(),
                ]);

                return $group;
            });
        } catch (\Throwable $e) {
            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }

            throw $e;
        }

        foreach ($request->user()->followers()->pluck('users.id') as $followerId) {
            AppNotification::send(
                $followerId,
                'group_created',
                __('econature.notifications.group_created', [
                    'username' => $request->user()->username,
                    'group' => $group->name,
                ]),
                route('groups.show', $group),
                $request->user(),
            );
        }

        return to_route('dashboard')->with('success', __('econature.groups.created'));
    }

    public function show(Group $group): Response
    {
        $user = request()->user();
        $userId = $user->id;

        $group->load(['owner', 'memberships' => fn ($query) => $query->where('user_id', $userId)]);

        $posts = Post::query()
            ->where('group_id', $group->id)
            ->with(['user', 'group.owner', 'attachments', 'comments.user'])
            ->withCount('reactions')
            ->with(['reactions' => fn ($query) => $query->where('user_id', $userId)])
            ->latest()
            ->paginate(20);

        $members = $group->memberships()
            ->with('user')
            ->where('status', GroupUser::STATUS_APPROVED)
            ->latest('created_at')
            ->get();

        $pending = $group->memberships()
            ->with('user')
            ->where('status', GroupUser::STATUS_PENDING)
            ->latest('created_at')
            ->get();

        $inviteUsers = User::query()
            ->where('id', '!=', $userId)
            ->whereNotIn('id', $group->memberships()->select('user_id'))
            ->orderBy('name')
            ->limit(100)
            ->get();

        return Inertia::render('Groups/Show', [
            'group' => new GroupResource($group),
            'posts' => PostResource::collection($posts),
            'members' => GroupMembershipResource::collection($members),
            'pendingMembers' => GroupMembershipResource::collection($pending),
            'inviteUsers' => UserResource::collection($inviteUsers),
        ]);
    }

    public function updateCover(Request $request, Group $group): RedirectResponse
    {
        abort_unless($group->isAdmin($request->user()), 403);

        $data = $request->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $coverPath = $data['cover']->store('groups/covers', 'public');
        $oldCoverPath = $group->cover_path;

        $group->update([
            'cover_path' => $coverPath,
        ]);

        if ($oldCoverPath) {
            Storage::disk('public')->delete($oldCoverPath);
        }

        return back()->with('success', __('econature.groups.cover_updated'));
    }

    public function destroyCover(Request $request, Group $group): RedirectResponse
    {
        abort_unless($group->isAdmin($request->user()), 403);

        $oldCoverPath = $group->cover_path;

        $group->update([
            'cover_path' => null,
        ]);

        if ($oldCoverPath) {
            Storage::disk('public')->delete($oldCoverPath);
        }

        return back()->with('success', __('econature.groups.cover_deleted'));
    }
}







