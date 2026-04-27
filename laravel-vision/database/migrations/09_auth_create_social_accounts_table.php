<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auth_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('sec_users')->onDelete('cascade');
            $table->string('provider');
            $table->string('provider_id');
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_social_accounts');
    }
};
