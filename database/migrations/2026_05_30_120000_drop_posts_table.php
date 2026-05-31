<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Posts moved from the database to markdown files under content/posts/.
 * This drops the now-unused `posts` table. `down()` recreates the original
 * schema so the migration stays reversible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('posts');
    }

    public function down(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->text('content')->nullable();
            $table->string('author')->nullable();
            $table->string('image')->nullable();
            $table->integer('read_time')->nullable();
            $table->json('tags')->nullable();
            $table->datetime('published_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('newsletter_sent_at')->nullable();
            $table->timestamps();

            $table->index(['profile_id', 'is_published']);
            $table->index('published_at');
            $table->index('slug');
        });
    }
};
