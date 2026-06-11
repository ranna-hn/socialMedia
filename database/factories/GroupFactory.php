<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'cover_path' => null,
            'thumbnail_path' => null,
            'auto_approval' => fake()->boolean(70),
            'member_count' => 0,
            'about' => fake()->sentence(10),
            'user_id' => User::factory(),
        ];
    }
}
