<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostAttachment>
 */
class PostAttachmentFactory extends Factory
{
    protected $model = PostAttachment::class;

    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'name' => fake()->word().'.jpg',
            'path' => 'attachments/demo.jpg',
            'mime' => 'image/jpeg',
            'size' => fake()->numberBetween(1000, 500000),
            'created_by' => User::factory(),
        ];
    }
}
