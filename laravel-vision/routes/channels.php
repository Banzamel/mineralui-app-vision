<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

$companyChannel = config('broadcasting.channel', 'edu') . '.company.{companyId}';

Broadcast::channel($companyChannel, function ($user, $companyId) {
    if ((int) $user->company_id !== (int) $companyId) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar_url' => $user->avatar_url,
    ];
});

// Per-user private channel — used for notifications addressed to a specific account.
Broadcast::channel('vision.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
