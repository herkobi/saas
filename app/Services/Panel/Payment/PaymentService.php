<?php

/**
 * Payment Service
 *
 * This service handles payment management operations
 * including listing, status updates, and invoice marking.
 *
 * @package    App\Services\Panel
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Services\Panel\Payment;

use App\Enums\PaymentStatus;
use App\Events\PanelPaymentMarkedAsInvoiced;
use App\Events\PanelPaymentStatusUpdated;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for payment management operations.
 *
 * Implements PaymentService to provide payment
 * listing, status updates, and invoice marking functionality.
 */
class PaymentService
{
    /**
     * Get paginated list of payments with optional filters.
     *
     * @param array<string, mixed> $filters Filter parameters
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated payment results
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::withoutTenantScope()->with(['tenant', 'subscription.price.plan', 'addon.feature']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('gateway_payment_id', 'like', "%{$filters['search']}%")
                  ->orWhereHas('tenant', function ($q2) use ($filters) {
                      $q2->where('code', 'like', "%{$filters['search']}%")
                         ->orWhere('slug', 'like', "%{$filters['search']}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (isset($filters['invoiced'])) {
            $filters['invoiced']
                ? $query->whereNotNull('invoiced_at')
                : $query->whereNull('invoiced_at');
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (!empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        return $query->orderBy($sortField, $sortDirection)->paginate($perPage)
            ->through(fn ($payment) => array_merge($payment->toArray(), [
                'status_label' => $payment->status->label(),
                'status_badge' => $payment->status->badge(),
            ]));
    }

    /**
     * Find a payment by ID.
     *
     * @param string $id The payment ULID
     * @return Payment|null The payment or null if not found
     */
    public function findById(string $id): ?Payment
    {
        return Payment::withoutTenantScope()->with(['tenant', 'subscription.price.plan', 'addon.feature'])->find($id);
    }

    /**
     * Update the status of a payment.
     *
     * @param Payment $payment The payment to update
     * @param PaymentStatus $status The new status
     * @param User $updatedBy The admin performing the update
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return Payment The updated payment instance
     */
    public function updateStatus(
        Payment $payment,
        PaymentStatus $status,
        User $updatedBy,
        string $ipAddress,
        string $userAgent
    ): Payment {
        $oldStatus = $payment->status;

        $updateData = ['status' => $status];

        if ($status === PaymentStatus::COMPLETED && !$payment->paid_at) {
            $updateData['paid_at'] = now();
        }

        if ($status === PaymentStatus::REFUNDED && !$payment->refunded_at) {
            $updateData['refunded_at'] = now();
        }

        $payment->update($updateData);

        PanelPaymentStatusUpdated::dispatch($payment, $oldStatus, $updatedBy, $ipAddress, $userAgent);

        return $payment->fresh();
    }

    /**
     * Mark a payment as invoiced.
     *
     * @param Payment $payment The payment to mark
     * @param User $markedBy The admin marking the payment
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return Payment The updated payment instance
     */
    public function markAsInvoiced(
        Payment $payment,
        User $markedBy,
        string $ipAddress,
        string $userAgent
    ): Payment {
        $payment->update(['invoiced_at' => now()]);

        PanelPaymentMarkedAsInvoiced::dispatch($payment, $markedBy, $ipAddress, $userAgent);

        return $payment->fresh();
    }

    /**
     * Mark multiple payments as invoiced.
     *
     * @param array<string> $paymentIds Array of payment IDs
     * @param User $markedBy The admin marking the payments
     * @param string $ipAddress IP address of the request
     * @param string $userAgent User agent of the request
     * @return int Number of payments marked
     */
    public function markManyAsInvoiced(
        array $paymentIds,
        User $markedBy,
        string $ipAddress,
        string $userAgent
    ): int {
        $count = Payment::withoutTenantScope()->whereIn('id', $paymentIds)
            ->whereNull('invoiced_at')
            ->where('status', PaymentStatus::COMPLETED->value)
            ->update(['invoiced_at' => now()]);

        foreach ($paymentIds as $paymentId) {
            $payment = Payment::withoutTenantScope()->find($paymentId);
            if ($payment && $payment->invoiced_at) {
                PanelPaymentMarkedAsInvoiced::dispatch($payment, $markedBy, $ipAddress, $userAgent);
            }
        }

        return $count;
    }

    /**
     * Get payment statistics with optional filters.
     *
     * @param array<string, mixed> $filters Filter parameters
     * @return array<string, mixed> Statistics data
     */
    public function getStatistics(array $filters = []): array
    {
        $query = Payment::withoutTenantScope();

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return [
            'total_count' => (clone $query)->count(),
            'completed_count' => (clone $query)->completed()->count(),
            'pending_count' => (clone $query)->pending()->count(),
            'failed_count' => (clone $query)->where('status', PaymentStatus::FAILED)->count(),
            'refunded_count' => (clone $query)->where('status', PaymentStatus::REFUNDED)->count(),
            'refunded_amount' => (clone $query)->where('status', PaymentStatus::REFUNDED)->sum('amount'),
            'total_revenue' => (clone $query)->completed()->sum('amount'),
            'pending_revenue' => (clone $query)->pending()->sum('amount'),
            'uninvoiced_count' => (clone $query)->whereNull('invoiced_at')->count(),
            'uninvoiced_amount' => (clone $query)->whereNull('invoiced_at')->sum('amount'),
        ];
    }

    /**
     * Get revenue data grouped by period.
     *
     * @param string $period Period type (day, week, month, year)
     * @param string|null $startDate Start date filter
     * @param string|null $endDate End date filter
     * @return Collection Revenue data collection
     */
    public function getFailedPayments(int $limit = 10): Collection
    {
        return Payment::withoutTenantScope()
            ->where('status', PaymentStatus::FAILED)
            ->with('tenant')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRevenueByPeriod(
        string $period = 'month',
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $query = Payment::withoutTenantScope()->where('status', PaymentStatus::COMPLETED);

        if ($startDate) {
            $query->where('paid_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('paid_at', '<=', $endDate);
        }

        $dateFormat = match ($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m',
        };

        return $query
            ->select(
                DB::raw("DATE_FORMAT(paid_at, '{$dateFormat}') as period"),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }
}
