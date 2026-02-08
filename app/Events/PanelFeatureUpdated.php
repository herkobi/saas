<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PanelFeatureUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Feature $feature,
        public readonly User $user,
        public readonly array $oldData,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
