<?php

namespace App\Jobs;

use App\Enums\CheckoutStatus;
use App\Models\Checkout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireOldCheckoutsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 30;

    public function handle(): void
    {
        Checkout::query()
            ->where('status', CheckoutStatus::PENDING)
            ->where('expires_at', '<=', now())
            ->update([
                'status' => CheckoutStatus::EXPIRED,
            ]);
    }
}
