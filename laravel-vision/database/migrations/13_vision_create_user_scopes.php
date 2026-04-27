<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the vision_user_scopes table — a map of "what a given user sees".
 * Scope type: building (object = building), address (address as a string), camera (specific camera).
 * A row means the user has access to that single resource. No records = no visibility.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_user_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('sec_users')->onDelete('cascade');
            $table->string('type', 16);
            $table->string('scope_id', 255);
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_user_scopes');
    }
};
