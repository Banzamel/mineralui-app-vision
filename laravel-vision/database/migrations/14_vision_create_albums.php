<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the vision_albums table — a daily set of photos from a single camera.
 * The on-disk folder is referenced by file_manager_path_id. The photos_count column is computed by sync
 * so that the album list does not have to run COUNT(*) on every refresh.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('sec_companies')->onDelete('cascade');
            $table->foreignId('camera_id')->constrained('vision_cameras')->onDelete('cascade');

            $table->date('date');
            $table->string('folder_name');

            $table->unsignedBigInteger('file_manager_path_id')->nullable();
            $table->foreign('file_manager_path_id')->references('id')->on('mgr_file_paths')->nullOnDelete();

            $table->unsignedInteger('photos_count')->default(0);

            $table->timestamps();

            $table->unique(['camera_id', 'date']);
            $table->index(['company_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_albums');
    }
};
