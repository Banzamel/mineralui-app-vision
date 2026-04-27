<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the vision_cameras table.
 * A camera belongs to one object (object_id) and one company (company_id).
 * The RTSP stream password is stored in a column encrypted via Laravel Crypt (APP_KEY).
 * The file_manager_path_id field points to the folder where FileManager keeps the camera's photos.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_cameras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('sec_companies')->onDelete('cascade');
            $table->foreignId('object_id')->constrained('vision_objects')->onDelete('cascade');

            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('slug');
            $table->string('address', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('stream_url', 500)->nullable();
            $table->string('stream_login')->nullable();
            $table->text('stream_password_encrypted')->nullable();
            $table->string('main_photo_path')->nullable();

            $table->unsignedBigInteger('file_manager_path_id')->nullable();
            $table->foreign('file_manager_path_id')->references('id')->on('mgr_file_paths')->nullOnDelete();

            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'object_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_cameras');
    }
};
