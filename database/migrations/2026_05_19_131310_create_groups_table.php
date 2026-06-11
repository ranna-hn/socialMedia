<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('cover_path', 1024)->nullable();
            $table->string('thumbnail_path', 1024)->nullable();
            $table->boolean('auto_approval')->default(true);
            $table->unsignedInteger('member_count')->default(0);
            $table->string('about', 255)->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
