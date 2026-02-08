<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PanelPlanArchived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Plan $plan,
        public readonly User $user,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
