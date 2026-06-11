<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        DB::transaction(function () use ($request, $post): void {
            $post->comments()->create([
                'comment' => $request->validated('comment'),
                'user_id' => $request->user()->id,
            ]);
        });

        return back()->with('success', __('econature.comments.created'));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $user = request()->user();
        $comment->loadMissing('post');

        abort_unless(
            $user->isAdmin()
            || $comment->user_id === $user->id
            || $comment->post->user_id === $user->id,
            403
        );

        $comment->delete();

        return back()->with('success', __('econature.comments.deleted'));
    }
}
