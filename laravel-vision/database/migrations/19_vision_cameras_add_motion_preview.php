<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the `motion_preview_enabled` flag to vision_cameras.
 * When true, the album view exposes an alternate "motion preview" mode that groups
 * consecutive motion-burst photos (≤ 5 s gap) into a single tile cycling through frames.
 * The flag is purely a UI/UX toggle — backend storage and album APIs are unchanged.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('vision_cameras', function (Blueprint $table) {
            $table->boolean('motion_preview_enabled')->default(false)->after('is_online');
        });
    }

    public function down(): void
    {
        Schema::table('vision_cameras', function (Blueprint $table) {
            $table->dropColumn('motion_preview_enabled');
        });
    }
};
