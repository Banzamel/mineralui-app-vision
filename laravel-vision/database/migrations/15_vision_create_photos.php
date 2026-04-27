<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the vision_photos table — individual photos inside an album.
 * The physical file is represented by file_manager_meta_id (FileManagerMeta).
 * Width/height/size/taken_at are produced by the file scanner (AlbumSyncService).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('vision_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('vision_albums')->onDelete('cascade');

            $table->unsignedBigInteger('file_manager_meta_id')->nullable();
            $table->foreign('file_manager_meta_id')->references('id')->on('mgr_file_metas')->nullOnDelete();

            $table->string('filename');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('bytes')->nullable();
            $table->string('mime', 64)->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();

            $table->index(['album_id', 'taken_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_photos');
    }
};
