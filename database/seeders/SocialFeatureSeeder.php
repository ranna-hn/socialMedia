<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Follower;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class SocialFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'EchoNature Admin',
            'email' => 'admin@echonature.test',
            'username' => 'echonature-admin',
        ]);

        $users = User::factory(8)->create();

        $groups = Group::factory(4)->create([
            'user_id' => $admin->id,
        ]);

        foreach ($groups as $group) {
            GroupUser::factory()->admin()->create([
                'group_id' => $group->id,
                'user_id' => $admin->id,
                'created_by' => $admin->id,
            ]);

            foreach ($users->random(4) as $user) {
                GroupUser::factory()->approved()->create([
                    'group_id' => $group->id,
                    'user_id' => $user->id,
                    'created_by' => $admin->id,
                ]);
            }
        }

        $posts = Post::factory(16)->create([
            'user_id' => fn () => $users->random()->id,
        ]);

        foreach ($groups as $group) {
            Post::factory(3)->create([
                'group_id' => $group->id,
                'user_id' => $users->random()->id,
            ]);
        }

        foreach ($posts as $post) {
            Comment::factory(2)->create([
                'post_id' => $post->id,
                'user_id' => $users->random()->id,
            ]);

            PostReaction::factory()->create([
                'post_id' => $post->id,
                'user_id' => $users->random()->id,
            ]);
        }

        foreach ($users as $user) {
            $followed = $users->where('id', '!=', $user->id)->random();

            Follower::factory()->create([
                'user_id' => $user->id,
                'followed_id' => $followed->id,
            ]);
        }
    }
}
