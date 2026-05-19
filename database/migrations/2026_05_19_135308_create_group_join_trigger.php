<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer le trigger s'il existe déjà
        DB::unprepared('DROP TRIGGER IF EXISTS after_user_join_group');

        // Crée le trigger mis à jour pour table groups
        DB::unprepared('
            CREATE TRIGGER after_user_join_group
            AFTER INSERT ON group_users
            FOR EACH ROW
            BEGIN
                UPDATE groups
                SET member_count = member_count + 1
                WHERE id = NEW.group_id;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_user_join_group');
    }
};


