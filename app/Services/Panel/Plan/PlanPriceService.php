<?php

/**
 * Plan Price Service
 *
 * Handles business logic for plan price operations
 * including creation, updates, and deletion.
 *
 * @package    App\Services\Panel\Plan
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Panel\Plan;

use App\Contracts\Panel\Plan\PlanPriceServiceInterface;
use App\Events\PanelPlanPriceCreated;
use App\Events\PanelPlanPriceDeleted;
use App\Events\PanelPlanPriceUpdated;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\User;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Collection;

/**
 * Plan Price Service
 *
 * Service implementation for managing plan pricing configurations.
 */
class PlanPriceService implements PlanPriceServiceInterface
{
    /**
     * Get all prices for a plan.
     *
     * @param Plan $plan
     * @return Collection
     */
    public function getByPlan(Plan $plan): Collection
    {
        return $plan->prices()->orderBy('interval')->orderBy('interval_count')->get();
    }

    /**
     * Find a price by ID.
     *
     * @param string $id
     * @return PlanPrice|null
     */
    public function findById(string $id): ?PlanPrice
    {
        return PlanPrice::find($id);
    }

    /**
     * Create a new price for a plan.
     *
     * @param Plan $plan
     * @param array $data
     * @param User $user
     * @param string $ipAddress
     * @param string $userAgent
     * @return PlanPrice
     */
    public function create(Plan $plan, array $data, User $user, string $ipAddress, string $userAgent): PlanPrice
    {
        $price = $plan->prices()->create($data);

        PanelPlanPriceCreated::dispatch($price, $user, $ipAddress, $userAgent);

        return $price;
    }

    /**
     * Update a plan price.
     *
     * @param PlanPrice $price
     * @param array $data
     * @param User $user
     * @param string $ipAddress
     * @param string $userAgent
     * @return PlanPrice
     */
    public function update(PlanPrice $price, array $data, User $user, string $ipAddress, string $userAgent): PlanPrice
    {
        $oldData = $price->toArray();

        $price->update($data);

        PanelPlanPriceUpdated::dispatch($price, $user, $oldData, $ipAddress, $userAgent);

        return $price->fresh();
    }

    /**
     * Delete a plan price.
     *
     * @param PlanPrice $price
     * @param User $user
     * @param string $ipAddress
     * @param string $userAgent
     * @return void
     */
    public function delete(PlanPrice $price, User $user, string $ipAddress, string $userAgent): void
    {
        PanelPlanPriceDeleted::dispatch($price, $user, $ipAddress, $userAgent);

        $price->delete();
    }

    /**
     * Get price matrix for a plan (single currency).
     *
     * @param Plan $plan
     * @return array{currency:string,rows:array<int,array{key:string,label:string,interval:string,interval_count:int,price:?PlanPrice}>}
     */
    public function getMatrixByPlan(Plan $plan): array
    {
        return $this->buildMatrix($this->getByPlan($plan));
    }

    /**
     * Build matrix structure from price collection (single currency).
     *
     * @param Collection $prices
     * @return array{currency:string,rows:array<int,array{key:string,label:string,interval:string,interval_count:int,price:?PlanPrice}>}
     */
    public function buildMatrix(Collection $prices): array
    {
        $currency = CurrencyHelper::defaultCode();

        $grouped = $prices->groupBy(function (PlanPrice $p) {
            $intervalKey = is_object($p->interval) ? $p->interval->value : (string) $p->interval;
            $count = (int) ($p->interval_count ?? 1);

            return $intervalKey . ':' . $count;
        });

        $rows = $grouped->map(function (Collection $row, string $key) use ($currency) {
            /** @var PlanPrice|null $first */
            $first = $row->first();

            [$intervalKey, $countStr] = array_pad(explode(':', $key, 2), 2, '1');
            $count = (int) $countStr;

            $label = $intervalKey;

            if ($first && is_object($first->interval) && method_exists($first->interval, 'label')) {
                $label = (string) $first->interval->label();
            }

            if ($count !== 1) {
                $label .= ' (' . $count . ')';
            }

            $price = $row->firstWhere('currency', $currency) ?? $first;

            return [
                'key' => $key,
                'label' => $label,
                'interval' => (string) $intervalKey,
                'interval_count' => $count,
                'price' => $price,
            ];
        })->values()->all();

        return [
            'currency' => $currency,
            'rows' => $rows,
        ];
    }
}
