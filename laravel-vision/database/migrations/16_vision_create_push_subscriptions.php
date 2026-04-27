<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the vision_push_subscriptions table — stores the user's browser web push subscription.
 * endpoint + p256dh/auth keys are everything minishlink/web-push needs to send a notification.
 * A single user can have multiple subscriptions (different browsers/devices).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('sec_users')->onDelete('cascade');
            $table->text('endpoint');
            $table->string('p256dh', 191);
            $table->string('auth', 191);
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'p256dh']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_push_subscriptions');
    }
};
