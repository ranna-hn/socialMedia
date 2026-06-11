<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'body' => '<p>'.fake()->paragraph().'</p>',
            'user_id' => User::factory(),
            'group_id' => null,
            'comments_count' => 0,
        ];
    }

    public function inGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'group_id' => Group::factory(),
        ]);
    }
}
