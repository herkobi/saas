<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\PlanPrice;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PanelPlanPriceCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly PlanPrice $price,
        public readonly User $user,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
