<?php

namespace App\Jobs;

use App\Services\App\Account\UsageResetService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetMeteredUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function handle(UsageResetService $service): void
    {
        $service->resetExpiredUsages();
    }
}
