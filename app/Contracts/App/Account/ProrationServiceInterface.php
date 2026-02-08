<?php

declare(strict_types=1);

namespace App\Contracts\App\Account;

use App\Enums\ProrationType;
use App\Models\PlanPrice;
use App\Models\Subscription;

interface ProrationServiceInterface
{
    /**
     * @return array{credit: float, new_amount: float, final_amount: float, days_remaining: int, daily_rate_old: float, daily_rate_new: float}
     */
    public function calculate(Subscription $subscription, PlanPrice $newPlanPrice, ProrationType $type = ProrationType::IMMEDIATE): array;

    public function getDaysRemaining(Subscription $subscription): int;

    public function getTotalDays(Subscription $subscription): int;

    public function calculateNewPlanDays(PlanPrice $planPrice): int;

    public function getDailyRate(Subscription $subscription): float;

    public function getUnusedValue(Subscription $subscription): float;
}
