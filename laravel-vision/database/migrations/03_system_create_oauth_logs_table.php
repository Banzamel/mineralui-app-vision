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
        Schema::create('oauth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('sec_users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('sec_companies')->onDelete('cascade');
            $table->string('action');
            $table->string('model')->nullable();
            $table->string('table')->nullable();
            $table->string('database')->nullable();
            $table->unsignedBigInteger('row_id')->nullable();
            $table->json('changes')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_logs');
    }
};
