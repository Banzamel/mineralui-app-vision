<?php

namespace Installer\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Reserve model for installer state in the database - currently the state lives in a JSON file; this model is declared for future use.
 */
class InstallState extends Model
{
    protected $table = 'vision_install_state';

    protected $fillable = [
        'stage',
        'completed_at',
        'payload_digest',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];
}
