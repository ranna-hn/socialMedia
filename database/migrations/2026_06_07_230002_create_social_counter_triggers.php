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

        DB::unprepared('DROP TRIGGER IF EXISTS after_user_join_group');
        DB::unprepared('DROP TRIGGER IF EXISTS group_users_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS group_users_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS group_users_after_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS comments_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS comments_after_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS followers_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS followers_after_delete');

        DB::unprepared("
            CREATE TRIGGER group_users_after_insert
            AFTER INSERT ON group_users
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'approved' THEN
                    UPDATE groups
                    SET member_count = member_count + 1
                    WHERE id = NEW.group_id;
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER group_users_after_update
            AFTER UPDATE ON group_users
            FOR EACH ROW
            BEGIN
                IF OLD.status <> 'approved' AND NEW.status = 'approved' THEN
                    UPDATE groups
                    SET member_count = member_count + 1
                    WHERE id = NEW.group_id;
                END IF;

                IF OLD.status = 'approved' AND NEW.status <> 'approved' THEN
                    UPDATE groups
                    SET member_count = IF(member_count > 0, member_count - 1, 0)
                    WHERE id = NEW.group_id;
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER group_users_after_delete
            AFTER DELETE ON group_users
            FOR EACH ROW
            BEGIN
                IF OLD.status = 'approved' THEN
                    UPDATE groups
                    SET member_count = IF(member_count > 0, member_count - 1, 0)
                    WHERE id = OLD.group_id;
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER comments_after_insert
            AFTER INSERT ON comments
            FOR EACH ROW
            BEGIN
                UPDATE posts
                SET comments_count = comments_count + 1
                WHERE id = NEW.post_id;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER comments_after_delete
            AFTER DELETE ON comments
            FOR EACH ROW
            BEGIN
                UPDATE posts
                SET comments_count = IF(comments_count > 0, comments_count - 1, 0)
                WHERE id = OLD.post_id;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER followers_after_insert
            AFTER INSERT ON followers
            FOR EACH ROW
            BEGIN
                UPDATE users
                SET following_count = following_count + 1
                WHERE id = NEW.user_id;

                UPDATE users
                SET followers_count = followers_count + 1
                WHERE id = NEW.followed_id;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER followers_after_delete
            AFTER DELETE ON followers
            FOR EACH ROW
            BEGIN
                UPDATE users
                SET following_count = IF(following_count > 0, following_count - 1, 0)
                WHERE id = OLD.user_id;

                UPDATE users
                SET followers_count = IF(followers_count > 0, followers_count - 1, 0)
                WHERE id = OLD.followed_id;
            END
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS group_users_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS group_users_after_update');
        DB::unprepared('DROP TRIGGER IF EXISTS group_users_after_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS comments_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS comments_after_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS followers_after_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS followers_after_delete');
    }
};
