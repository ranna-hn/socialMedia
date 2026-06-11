<?php

namespace Database\Factories;

use App\Models\Follower;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Follower>
 */
class FollowerFactory extends Factory
{
    protected $model = Follower::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'followed_id' => User::factory(),
            'created_at' => now(),
        ];
    }
}
