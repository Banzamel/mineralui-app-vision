<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the `data` JSON column to vision_notifications.
 *
 * Listeners now persist a structured payload (e.g. `{actor_name: 'Anna'}` for user_login,
 * `{date, camera_name, album_id}` for album_created) so the frontend can render the
 * notification message via i18n in the user's language. The legacy `title` / `message`
 * columns stay populated with an English fallback — used by Web Push (rendered text on
 * the OS notification) and as a graceful degradation when the frontend doesn't recognise
 * the notification `type`.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('vision_notifications', function (Blueprint $table) {
            $table->json('data')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('vision_notifications', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
};
