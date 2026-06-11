<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMembershipController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileAlbumController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\XmlController;

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/api/weather-config', function () {
        return response()->json([
            'apiKey' => config('services.openweather.key'),
            'endpoint' => 'https://api.openweathermap.org/data/2.5/weather',
            'defaultLocation' => [
                'city' => 'Paris',
                'lat' => 48.8566,
                'lon' => 2.3522,
            ],
        ]);
    })->name('weather.config');

    Route::post('/locale/{locale}', function (string $locale) {
        abort_unless(in_array($locale, ['fr', 'en'], true), 404);
        session(['locale' => $locale]);

        return back();
    })->name('locale.switch');
    Route::get('/u/{user:username}', [ProfileController::class, 'index'])->name('profile');
    Route::get('/search', [SearchController::class, 'index'])->name('search.global');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

        Route::post('/profile/update-images', [ProfileController::class, 'updateImage'])
        ->name('profile.updateImages');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/albums', [ProfileAlbumController::class, 'store'])->name('profile.albums.store');
    Route::post('/profile/albums/{album}/photos', [ProfileAlbumController::class, 'addPhotos'])->name('profile.albums.photos.store');
    Route::put('/profile/albums/{album}/photos/reorder', [ProfileAlbumController::class, 'reorderPhotos'])->name('profile.albums.photos.reorder');

    Route::resource('groups', GroupController::class)->only(['store', 'show']);
    Route::post('/groups/{group}/cover', [GroupController::class, 'updateCover'])->name('groups.cover.update');
    Route::delete('/groups/{group}/cover', [GroupController::class, 'destroyCover'])->name('groups.cover.destroy');
    Route::post('/groups/{group}/join', [GroupMembershipController::class, 'join'])->name('groups.join');
    Route::post('/groups/{group}/invite', [GroupMembershipController::class, 'invite'])->name('groups.invite');
    Route::patch('/groups/{group}/members/{membership}/approve', [GroupMembershipController::class, 'approve'])->name('groups.members.approve');
    Route::patch('/groups/{group}/members/{membership}/reject', [GroupMembershipController::class, 'reject'])->name('groups.members.reject');
    Route::patch('/groups/{group}/members/{membership}/role', [GroupMembershipController::class, 'updateRole'])->name('groups.members.role');
    Route::delete('/groups/{group}/members/{membership}', [GroupMembershipController::class, 'destroy'])->name('groups.members.destroy');

    Route::post('/users/{user:username}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user:username}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');

    Route::post('/post', [PostController::class, 'store'])
    ->name('posts.create');

    Route::put('/post/{post}', [PostController::class, 'update'])
    ->name('posts.update');

    Route::delete('/post/{post}', [PostController::class, 'destroy'])
    ->name('posts.destroy');

    Route::post('/post/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/post/download/{attachment}', [PostController::class, 'downloadAttachment'])
    ->name('posts.download');

    Route::post('/post/{post}/reaction', [PostController::class, 'postReaction'])
    ->name('post.reaction');

    Route::get('/xml/posts/export', [XmlController::class, 'exportPosts'])->name('xml.posts.export');
    Route::get('/xml/users/export', [XmlController::class, 'exportUsers'])->name('xml.users.export');
    Route::post('/xml/import', [XmlController::class, 'import'])->name('xml.import');
});


require __DIR__.'/auth.php';
