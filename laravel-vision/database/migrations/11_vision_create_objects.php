<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the vision_objects tree table.
 * An object is e.g. a building/apartment/garage — any node in the hierarchy that
 * cameras can be attached to. Each record belongs to a specific company (company_id) and may have a parent.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('sec_companies')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('vision_objects')->onDelete('cascade');

            $table->string('name');
            $table->string('slug');
            $table->string('type', 32);
            $table->string('address', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('main_photo_path')->nullable();
            $table->unsignedInteger('depth')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_objects');
    }
};
