<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PhotoAlbumResource;
use App\Http\Resources\PostAttachmentResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\PostAttachment;

class ProfileController extends Controller
{
    public function index(User $user)
    {
        return $this->renderProfile($user);
    }

    public function edit(Request $request)
    {
        return $this->renderProfile($request->user());
    }

    private function renderProfile(User $user)
    {
        $authUser = request()->user();
        $userId = $authUser?->id;
        $user->setAttribute('followers_count', $user->followers()->count());
        $user->setAttribute('following_count', $user->followings()->count());

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->whereNull('group_id')
            ->with(['user', 'group.owner', 'attachments', 'comments.user'])
            ->withCount('reactions')
            ->with(['reactions' => fn ($query) => $query->where('user_id', $userId)])
            ->latest()
            ->paginate(20);

        $photos = PostAttachment::query()
            ->where('mime', 'like', 'image/%')
            ->whereHas('post', fn ($query) => $query
                ->where('user_id', $user->id)
                ->whereNull('group_id'))
            ->latest()
            ->get();

        $albums = $user->photoAlbums()
            ->with('photos')
            ->withCount('photos')
            ->get();

        $stats = DB::getDriverName() === 'mysql'
            ? collect(DB::select('CALL GetUserProfileStats(?)', [$user->id]))->first()
            : (object) [
                'posts_count' => $user->posts()->whereNull('group_id')->count(),
                'followers_count' => $user->followers_count ?? $user->followers()->count(),
                'following_count' => $user->following_count ?? $user->followings()->count(),
            ];

        $followers = $this->withFollowingState(
            $user->followers()
                ->withCount(['followers', 'followings'])
                ->orderBy('users.name')
                ->limit(50)
                ->get(),
            $authUser,
        );

        $followings = $this->withFollowingState(
            $user->followings()
                ->withCount(['followers', 'followings'])
                ->orderBy('users.name')
                ->limit(50)
                ->get(),
            $authUser,
        );

        return Inertia::render('Profile/View', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'success' => session('success'),
            'user' => new UserResource($user),
            'posts' => PostResource::collection($posts),
            'photos' => PostAttachmentResource::collection($photos),
            'albums' => PhotoAlbumResource::collection($albums),
            'followers' => UserResource::collection($followers),
            'followings' => UserResource::collection($followings),
            'stats' => $stats,
            'current_user_is_following' => $authUser ? $authUser->isFollowing($user) : false,
        ]);
    }

    private function withFollowingState($users, ?User $authUser)
    {
        if ($users->isEmpty()) {
            return $users;
        }

        $followingIds = collect();

        if ($authUser) {
            $followingIds = $authUser->followings()
                ->whereIn('users.id', $users->pluck('id'))
                ->pluck('users.id')
                ->map(fn ($id) => (int) $id)
                ->flip();
        }

        return $users->each(function (User $listedUser) use ($authUser, $followingIds): void {
            $isCurrentUser = $authUser && $authUser->id === $listedUser->id;

            $listedUser->setAttribute('is_current_user', $isCurrentUser);
            $listedUser->setAttribute(
                'is_following',
                ! $isCurrentUser && $followingIds->has((int) $listedUser->id),
            );
        });
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit')->with('success', 'Your profile details were updated.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateImage(Request $request)
    {
        $data = $request->validate([
            'cover'=>['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'avatar'=>['nullable', 'image']
        ]);

        $user = $request->user();

        $avatar =$data['avatar'] ?? null;
        /** @var \Illuminate\Http\UploadedFile $cover */

        $cover = $data['cover'] ?? null;

        $success = '';
        if($cover) {
            if($user->cover_path) {
            }

            $path =$cover->store('user-'.$user->id, 'public');

            $user->update(['cover_path' => $path]);
           $success = 'Your cover image was updated.';
            
        }

        if($avatar) {
            if($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path =$avatar->store('user-'.$user->id, 'public');

            $user->update(['avatar_path' => $path]);

            $success = 'Your avatar image was updated.';
            
        }
        
        return back()->with('success', $success);
    }
}
