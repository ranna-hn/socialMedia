<?php

namespace App\Http\Controllers;

use App\Models\PhotoAlbum;
use App\Models\PostAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileAlbumController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->photoAlbums()->create($data);

        return back()->with('success', 'Album created.');
    }

    public function addPhotos(Request $request, PhotoAlbum $album): RedirectResponse
    {
        abort_unless($album->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'photo_ids' => ['required', 'array', 'min:1'],
            'photo_ids.*' => ['integer', 'exists:post_attachments,id'],
        ]);

        $photoIds = PostAttachment::query()
            ->whereIn('id', $data['photo_ids'])
            ->where('mime', 'like', 'image/%')
            ->whereHas('post', fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->whereNull('group_id'))
            ->pluck('id');

        if ($photoIds->isEmpty()) {
            return back()->withErrors([
                'photo_ids' => 'Select photos from your profile.',
            ]);
        }

        $existingPhotoIds = $album->photos()
            ->whereIn('post_attachments.id', $photoIds)
            ->pluck('post_attachments.id');

        $newPhotoIds = $photoIds->diff($existingPhotoIds)->values();
        $nextPosition = ((int) DB::table('album_post_attachment')
            ->where('photo_album_id', $album->id)
            ->max('position')) + 1;

        $attachData = [];
        foreach ($newPhotoIds as $photoId) {
            $attachData[$photoId] = ['position' => $nextPosition++];
        }

        if ($attachData !== []) {
            $album->photos()->attach($attachData);
        }

        return back()->with('success', 'Photos added to album.');
    }

    public function reorderPhotos(Request $request, PhotoAlbum $album): RedirectResponse
    {
        abort_unless($album->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'photo_ids' => ['required', 'array', 'min:1'],
            'photo_ids.*' => ['integer'],
        ]);

        $currentPhotoIds = $album->photos()
            ->pluck('post_attachments.id')
            ->values();

        $orderedPhotoIds = collect($data['photo_ids'])
            ->unique()
            ->filter(fn (int $photoId): bool => $currentPhotoIds->contains($photoId))
            ->values();

        $remainingPhotoIds = $currentPhotoIds->diff($orderedPhotoIds)->values();
        $finalPhotoIds = $orderedPhotoIds->merge($remainingPhotoIds)->values();

        DB::transaction(function () use ($album, $finalPhotoIds): void {
            foreach ($finalPhotoIds as $index => $photoId) {
                $album->photos()->updateExistingPivot($photoId, [
                    'position' => $index + 1,
                ]);
            }
        });

        return back()->with('success', 'Album order updated.');
    }
}
