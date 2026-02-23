<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PanelTenantSubscriptionCustomPriceUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly ?float $oldPrice,
        public readonly ?float $newPrice,
        public readonly User $admin,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
