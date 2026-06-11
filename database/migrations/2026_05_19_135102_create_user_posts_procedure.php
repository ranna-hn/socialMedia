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

        DB::unprepared('
            CREATE PROCEDURE GetUserPostsCount(IN userId BIGINT)
            BEGIN
                SELECT COUNT(*) AS total_posts
                FROM posts
                WHERE user_id = userId;
            END
        ');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS GetUserPostsCount');
    }
};
