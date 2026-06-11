<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\User;
use App\Support\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function store(User $user): RedirectResponse
    {
        $authUser = request()->user();

        abort_if($authUser->id === $user->id, 422, __('econature.follow.cannot_follow_self'));

        DB::transaction(function () use ($authUser, $user): void {
            $follow = Follower::firstOrCreate([
                'user_id' => $authUser->id,
                'followed_id' => $user->id,
            ]);

            if ($follow->wasRecentlyCreated) {
                AppNotification::send(
                    $user,
                    'user_followed',
                    __('econature.notifications.followed_you', ['username' => $authUser->username]),
                    route('profile', $authUser->username),
                    $authUser,
                );
            }
        });

        return back()->with('success', __('econature.follow.followed'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $authUser = request()->user();

        DB::transaction(function () use ($authUser, $user): void {
            Follower::where('user_id', $authUser->id)
                ->where('followed_id', $user->id)
                ->delete();
        });

        return back()->with('success', __('econature.follow.unfollowed'));
    }
}
