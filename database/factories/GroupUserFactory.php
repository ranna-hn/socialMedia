<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GroupUser>
 */
class GroupUserFactory extends Factory
{
    protected $model = GroupUser::class;

    public function definition(): array
    {
        return [
            'status' => fake()->randomElement([
                GroupUser::STATUS_APPROVED,
                GroupUser::STATUS_PENDING,
                GroupUser::STATUS_REJECTED,
            ]),
            'role' => GroupUser::ROLE_MEMBER,
            'token' => Str::random(64),
            'token_expires_date' => now()->addDays(7),
            'token_used' => null,
            'user_id' => User::factory(),
            'group_id' => Group::factory(),
            'created_by' => User::factory(),
            'created_at' => now(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GroupUser::STATUS_APPROVED,
            'token_used' => now(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => GroupUser::ROLE_ADMIN,
            'status' => GroupUser::STATUS_APPROVED,
        ]);
    }
}
