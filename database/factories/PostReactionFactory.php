<?php

namespace Database\Factories;

use App\Http\Enums\PostReactionEnum;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostReaction>
 */
class PostReactionFactory extends Factory
{
    protected $model = PostReaction::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'type' => PostReactionEnum::Like->value,
            'created_at' => now(),
        ];
    }
}
