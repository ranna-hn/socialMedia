<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Follower;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $posts = Post::query()
        ->with(['user', 'group.owner', 'attachments', 'comments.user'])
        ->withCount('reactions')
        ->with(['reactions' => function ($query) use ($userId){
            $query->where('user_id', $userId); }])
            ->whereNull('group_id')
            ->latest()
            ->paginate(20);

        $memberGroupIds = GroupUser::query()
            ->where('user_id', $userId)
            ->where('status', GroupUser::STATUS_APPROVED)
            ->pluck('group_id');

        $groups = Group::query()
            ->where('user_id', $userId)
            ->orWhereIn('id', $memberGroupIds)
            ->with(['owner', 'memberships' => fn ($query) => $query->where('user_id', $userId)])
            ->orderByDesc('created_at')
            ->get();

        $friends = User::query()
            ->where('id', '!=', $userId)
            ->where(function ($query) use ($userId) {
                $query
                    ->whereIn('id', Follower::query()
                        ->select('followed_id')
                        ->where('user_id', $userId))
                    ->orWhereIn('id', Follower::query()
                        ->select('user_id')
                        ->where('followed_id', $userId));
            })
            ->withCount(['followers', 'followings'])
            ->orderBy('users.name')
            ->get();

        return Inertia::render('Home',[
            'posts' => PostResource::collection($posts),
            'groups' => GroupResource::collection($groups),
            'friends' => UserResource::collection($friends),
            'followings' => UserResource::collection($friends),
        ]);
        
    }
}

