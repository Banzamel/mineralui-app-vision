<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_install_state', function (Blueprint $table) {
            $table->id();
            $table->string('stage', 32)->unique();
            $table->timestamp('completed_at');
            $table->string('payload_digest', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_install_state');
    }
};
