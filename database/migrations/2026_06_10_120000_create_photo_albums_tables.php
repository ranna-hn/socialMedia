<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('album_post_attachment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_album_id')->constrained('photo_albums')->cascadeOnDelete();
            $table->foreignId('post_attachment_id')->constrained('post_attachments')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['photo_album_id', 'post_attachment_id'], 'album_attachment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('album_post_attachment');
        Schema::dropIfExists('photo_albums');
    }
};
