<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the vision_notifications table — per-user notifications (camera offline, backup done, etc.).
 * The link column may point to a frontend route (e.g. "/objects?camera=12").
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('sec_companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('sec_users')->onDelete('cascade');

            $table->string('type', 64);
            $table->string('severity', 16);
            $table->string('title');
            $table->text('message');
            // Structured payload used by the frontend i18n renderer (e.g. {actor_name})
            // — title/message stay populated with EN fallback for Web Push + graceful UI.
            $table->json('data')->nullable();
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_notifications');
    }
};
