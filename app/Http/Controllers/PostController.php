<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\PostAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Enums\PostReactionEnum;
use App\Models\PostReaction;


class PostController extends Controller
{
    
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        DB::beginTransaction();

        $allFilePaths = [];

        try{
            $post= Post::create($data);

        /**
         *  @var \Illuminate\Http\UploadedFile[] $file
         */
        
        $files = $data['attachments'] ?? [];

        foreach($files as $file) {
            $path = $file->store('attachments/'.$post->id, 'public');
            $allFilePaths[] = $path;

            PostAttachment::create([
                'post_id' => $post->id,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'created_by' => $user->id
                ]);
        }
        DB::commit();

        } catch (\Exception $e) {
            foreach($allFilePaths as $path){
                Storage::disk('public')->delete($path);
            }

          DB::rollBack();
          throw $e;
        }
      
        return back();

    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $user = $request->user();
        $data = $request->validated();

        DB::beginTransaction();

        $allFilePaths = [];

        try{
            $post->update($data);

            $deleted_ids = $data['deleted_file_ids'] ?? [];

            //suprimer un attachment dans update/edit

            $attachments= PostAttachment::where('post_id', $post->id)
            ->whereIn('id', $deleted_ids)->get();

            foreach($attachments as $attachment){
                $attachment->delete();
            }

        /**
         *  @var \Illuminate\Http\UploadedFile[] $file
         */
        
        $files = $data['attachments'] ?? [];

        foreach($files as $file) {
            $path = $file->store('attachments/'.$post->id, 'public');
            $allFilePaths[] = $path;

            PostAttachment::create([
                'post_id' => $post->id,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'created_by' => $user->id
                ]);
        }
        DB::commit();

        } catch (\Exception $e) {
            foreach($allFilePaths as $path){
                Storage::disk('public')->delete($path);
            }

          DB::rollBack();
          throw $e;
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $id = Auth::id();
        if($post->user_id != $id){
            return response ("you don't have permission to delete this post", 403);
        }

        $post->delete();

        return back();
    }


    public function downloadAttachment(PostAttachment $attachment)
    {
        // a faire check permission pour enregister un attachment
        return response()->download(Storage::disk('public')
        ->path($attachment->path), $attachment->name);
        
        
        }


        public function postReaction(Request $request, Post $post)
        {
            $data = $request->validate([
                'reaction' => [Rule::enum(PostReactionEnum::class)]
            ]);

            $userId = Auth::id();

            $reaction = PostReaction::where('user_id', $userId)->where('post_id', $post->id)->first();

            if($reaction){
                $hasReaction = false;
                $reaction->delete();
            } else {
               $hasReaction = true; 
            

            PostReaction::create([
                'post_id' => $post->id,
                'user_id' => $userId,
                'type' => $data['reaction']
            ]);

            }


            $reactions = PostReaction::where('post_id', $post->id)->count();

            return response([
            'num_of_reactions' => $reactions,
            'current_user_has_reaction' => $hasReaction,
            ]);
        }

        

}
