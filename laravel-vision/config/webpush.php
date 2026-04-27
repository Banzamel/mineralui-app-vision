<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VAPID identity
    |--------------------------------------------------------------------------
    |
    | Public/private keypair generated once via `Minishlink\WebPush\VAPID::createVapidKeys()`.
    | The public key also has to be exposed to the frontend (see VITE_VAPID_PUBLIC_KEY) so
    | the browser's PushManager can subscribe. Subject must be a contact URL (mailto: works).
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@example.com'),
        'publicKey' => env('VAPID_PUBLIC_KEY'),
        'privateKey' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults applied to every notification
    |--------------------------------------------------------------------------
    |
    | TTL — how long the push service should hold the message if the device is offline.
    | 1 day is enough for the kind of "new album" / "user logged in" alerts we send.
    |
    */

    'defaults' => [
        'TTL' => 86400,
        'urgency' => 'normal',
    ],
];
