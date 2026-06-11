<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(SearchRequest $request): JsonResponse
    {
        $query = trim((string) $request->validated('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'users' => [],
                'groups' => [],
                'posts' => [],
            ]);
        }

        $users = User::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'type' => 'user',
                'title' => $user->name,
                'subtitle' => '@'.$user->username,
                'url' => route('profile', $user->username),
            ]);

        $groups = Group::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('about', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (Group $group): array => [
                'id' => $group->id,
                'type' => 'group',
                'title' => $group->name,
                'subtitle' => $group->about,
                'url' => route('groups.show', $group),
            ]);

        $posts = Post::query()
            ->with('user')
            ->where('body', 'like', "%{$query}%")
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Post $post): array => [
                'id' => $post->id,
                'type' => 'post',
                'title' => Str::limit(strip_tags($post->body), 80),
                'subtitle' => $post->user?->name,
                'url' => $post->group_id
                    ? route('groups.show', $post->group)
                    : route('profile', $post->user?->username),
            ]);

        return response()->json(compact('users', 'groups', 'posts'));
    }
}
