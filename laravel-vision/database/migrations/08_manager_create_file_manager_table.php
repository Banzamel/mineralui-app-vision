<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shared\Helpers\BlueprintHelper;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mgr_file_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('sec_companies')->onDelete('cascade');
            $table->string('hash', 31);

            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('mgr_file_paths')->onDelete('cascade');

            $table->nullableMorphs('owner');

            $table->string('type', 31);
            $table->string('storage', 31);
            $table->string('name');
            $table->string('path');

            $table->unsignedBigInteger('size');

            $table->timestamps();
            BlueprintHelper::usersStamps($table);
        });

        Schema::create('mgr_file_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('mgr_file_paths')->onDelete('cascade');

            $table->string('hash', 31);

            $table->string('mime_type');
            $table->string('extension');
            $table->json('metadata')->nullable();
            $table->string('checksum');

            $table->timestamps();
            BlueprintHelper::usersStamps($table);
        });

        Schema::create('mgr_file_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained('mgr_file_paths')->onDelete('cascade');

            $table->morphs('target');
            $table->string('url')->unique();

            $table->timestamps();
            BlueprintHelper::usersStamps($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mgr_file_links');
        Schema::dropIfExists('mgr_file_metas');
        Schema::dropIfExists('mgr_file_paths');
    }
};
