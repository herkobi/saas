<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PanelSettingUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly array $oldData,
        public readonly array $newData,
        public readonly User $user,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
