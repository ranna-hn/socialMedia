<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('user')->after('password')->index();
            }

            if (! Schema::hasColumn('users', 'followers_count')) {
                $table->unsignedInteger('followers_count')->default(0)->after('avatar_path');
            }

            if (! Schema::hasColumn('users', 'following_count')) {
                $table->unsignedInteger('following_count')->default(0)->after('followers_count');
            }
        });

        if (Schema::hasColumn('users', 'username') && ! $this->indexExists('users', 'users_username_unique')) {
            $this->ensureUniqueTextColumn('users', 'username', 'user');

            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        }

        Schema::table('groups', function (Blueprint $table) {
            if (! Schema::hasColumn('groups', 'member_count')) {
                $table->unsignedInteger('member_count')->default(0)->after('auto_approval');
            }
        });

        $this->ensureUniqueTextColumn('groups', 'slug', 'group');
        $this->normalizeGroupMembershipValues();

        Schema::table('groups', function (Blueprint $table) {
            if (! $this->indexExists('groups', 'groups_slug_unique')) {
                $table->unique('slug');
            }

            if (! $this->indexExists('groups', 'groups_user_id_index')) {
                $table->index('user_id');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'comments_count')) {
                $table->unsignedInteger('comments_count')->default(0)->after('group_id');
            }

            if (! $this->indexExists('posts', 'posts_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }

            if (! $this->indexExists('posts', 'posts_group_id_created_at_index')) {
                $table->index(['group_id', 'created_at']);
            }
        });

        $this->removeDuplicateRows('followers', ['user_id', 'followed_id']);
        $this->removeDuplicateRows('group_users', ['user_id', 'group_id']);
        $this->removeDuplicateRows('post_reactions', ['user_id', 'post_id']);

        Schema::table('followers', function (Blueprint $table) {
            if (! $this->indexExists('followers', 'followers_user_id_followed_id_unique')) {
                $table->unique(['user_id', 'followed_id']);
            }

            if (! $this->indexExists('followers', 'followers_followed_id_index')) {
                $table->index('followed_id');
            }
        });

        Schema::table('group_users', function (Blueprint $table) {
            if (! $this->indexExists('group_users', 'group_users_user_id_group_id_unique')) {
                $table->unique(['user_id', 'group_id']);
            }

            if (! $this->indexExists('group_users', 'group_users_group_id_status_index')) {
                $table->index(['group_id', 'status']);
            }
        });

        Schema::table('post_reactions', function (Blueprint $table) {
            if (! $this->indexExists('post_reactions', 'post_reactions_user_id_post_id_unique')) {
                $table->unique(['user_id', 'post_id']);
            }
        });

        Schema::table('comments', function (Blueprint $table) {
            if (! $this->indexExists('comments', 'comments_post_id_created_at_index')) {
                $table->index(['post_id', 'created_at']);
            }
        });

        DB::statement("
            UPDATE groups
            SET member_count = (
                SELECT COUNT(*)
                FROM group_users
                WHERE group_users.group_id = groups.id
                AND group_users.status = 'approved'
            )
        ");

        DB::statement("
            UPDATE posts
            SET comments_count = (
                SELECT COUNT(*)
                FROM comments
                WHERE comments.post_id = posts.id
            )
        ");

        DB::statement("
            UPDATE users
            SET followers_count = (
                SELECT COUNT(*)
                FROM followers
                WHERE followers.followed_id = users.id
            ),
            following_count = (
                SELECT COUNT(*)
                FROM followers
                WHERE followers.user_id = users.id
            )
        ");
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if ($this->indexExists('comments', 'comments_post_id_created_at_index')) {
                $table->dropIndex('comments_post_id_created_at_index');
            }
        });

        Schema::table('post_reactions', function (Blueprint $table) {
            if ($this->indexExists('post_reactions', 'post_reactions_user_id_post_id_unique')) {
                $table->dropUnique('post_reactions_user_id_post_id_unique');
            }
        });

        Schema::table('group_users', function (Blueprint $table) {
            if ($this->indexExists('group_users', 'group_users_user_id_group_id_unique')) {
                $table->dropUnique('group_users_user_id_group_id_unique');
            }

            if ($this->indexExists('group_users', 'group_users_group_id_status_index')) {
                $table->dropIndex('group_users_group_id_status_index');
            }
        });

        Schema::table('followers', function (Blueprint $table) {
            if ($this->indexExists('followers', 'followers_user_id_followed_id_unique')) {
                $table->dropUnique('followers_user_id_followed_id_unique');
            }

            if ($this->indexExists('followers', 'followers_followed_id_index')) {
                $table->dropIndex('followers_followed_id_index');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if ($this->indexExists('posts', 'posts_user_id_created_at_index')) {
                $table->dropIndex('posts_user_id_created_at_index');
            }

            if ($this->indexExists('posts', 'posts_group_id_created_at_index')) {
                $table->dropIndex('posts_group_id_created_at_index');
            }

            if (Schema::hasColumn('posts', 'comments_count')) {
                $table->dropColumn('comments_count');
            }
        });

        Schema::table('groups', function (Blueprint $table) {
            if ($this->indexExists('groups', 'groups_slug_unique')) {
                $table->dropUnique('groups_slug_unique');
            }

            if ($this->indexExists('groups', 'groups_user_id_index')) {
                $table->dropIndex('groups_user_id_index');
            }

            if (Schema::hasColumn('groups', 'member_count')) {
                $table->dropColumn('member_count');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'users_username_unique')) {
                $table->dropUnique('users_username_unique');
            }

            foreach (['role', 'followers_count', 'following_count'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('$table')");

            return collect($indexes)->contains(fn (object $row): bool => $row->name === $index);
        }

        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $database = DB::getDatabaseName();

        return (bool) DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    /**
     * Keep the oldest row for each unique business key before adding indexes.
     */
    private function removeDuplicateRows(string $table, array $columns): void
    {
        $rows = DB::table($table)
            ->select(['id', ...$columns])
            ->orderBy('id')
            ->get();

        $seen = [];
        $deleteIds = [];

        foreach ($rows as $row) {
            $key = collect($columns)
                ->map(fn (string $column): string => (string) $row->{$column})
                ->implode('|');

            if (isset($seen[$key])) {
                $deleteIds[] = $row->id;
                continue;
            }

            $seen[$key] = true;
        }

        if ($deleteIds !== []) {
            DB::table($table)->whereIn('id', $deleteIds)->delete();
        }
    }

    private function ensureUniqueTextColumn(string $table, string $column, string $prefix): void
    {
        $rows = DB::table($table)
            ->select(['id', $column])
            ->orderBy('id')
            ->get();

        $seen = [];

        foreach ($rows as $row) {
            $value = trim((string) $row->{$column});
            $base = $value !== '' ? Str::slug($value) : $prefix.'-'.$row->id;
            $candidate = $base !== '' ? $base : $prefix.'-'.$row->id;

            if (isset($seen[$candidate])) {
                $candidate = $candidate.'-'.$row->id;
            }

            while (isset($seen[$candidate])) {
                $candidate = $prefix.'-'.$row->id.'-'.Str::random(4);
            }

            $seen[$candidate] = true;

            if ($candidate !== $value) {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update([$column => $candidate]);
            }
        }
    }

    private function normalizeGroupMembershipValues(): void
    {
        DB::table('group_users')
            ->whereIn('status', ['approuve', 'approuvé', 'approved'])
            ->update(['status' => 'approved']);

        DB::table('group_users')
            ->whereIn('status', ['en_attente', 'pending'])
            ->update(['status' => 'pending']);

        DB::table('group_users')
            ->whereIn('status', ['refuse', 'refusé', 'rejected'])
            ->update(['status' => 'rejected']);

        DB::table('group_users')
            ->whereNotIn('role', ['admin', 'member'])
            ->update(['role' => 'member']);
    }
};
