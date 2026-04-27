<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vision application version
    |--------------------------------------------------------------------------
    | Exposed via GET /system/status. Bump on every release.
    */
    'version' => env('VISION_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Release notes
    |--------------------------------------------------------------------------
    | Newest entry first. Allowed change types: feature | improvement | fix | breaking.
    | Exposed via GET /releases and GET /releases/latest.
    */
    'releases' => [
        [
            'version' => '1.0.0',
            'released_at' => '2026-04-24',
            'title' => 'Initial Vision release',
            'changes' => [
                ['type' => 'feature', 'description' => 'Object tree with cameras and soft-delete support.'],
                ['type' => 'feature', 'description' => 'Daily auto-synced photo albums with retention.'],
                ['type' => 'feature', 'description' => 'Realtime via Reverb — object/camera/album events.'],
                ['type' => 'feature', 'description' => 'User administration panel: sessions, activity, password reset.'],
                ['type' => 'feature', 'description' => 'Audit log of mutations (Loggable) on key domain models.'],
            ],
        ],
    ],
];
