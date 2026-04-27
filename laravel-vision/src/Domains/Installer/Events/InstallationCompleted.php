<?php

namespace Installer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched after the install wizard completes successfully.
 */
class InstallationCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * @param int|null $companyId Identifier of the newly created company (when available).
     */
    public function __construct(
        public readonly ?int $companyId = null,
    ) {}
}
