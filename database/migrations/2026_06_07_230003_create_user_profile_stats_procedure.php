<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS GetUserProfileStats');

        DB::unprepared("
            CREATE PROCEDURE GetUserProfileStats(IN profileUserId BIGINT)
            BEGIN
                SELECT
                    users.id,
                    users.name,
                    users.username,
                    (
                        SELECT COUNT(*)
                        FROM posts
                        WHERE posts.user_id = users.id
                        AND posts.deleted_at IS NULL
                    ) AS posts_count,
                    users.followers_count,
                    users.following_count
                FROM users
                WHERE users.id = profileUserId;
            END
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS GetUserProfileStats');
    }
};
