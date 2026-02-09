<?php

/**
 * Payment Service Interface Contract
 *
 * This interface defines the contract for payment service
 * implementations, providing methods for payment management
 * within the panel domain.
 *
 * @package    App\Contracts\Panel\Payment
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Contracts\Panel\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface for payment service implementations.
 *
 * Defines the contract for managing payments from the panel,
 * including listing, status updates, and invoice marking operations.
 */
interface PaymentServiceInterface
{
    /**
     * Get paginated list of payments with optional filters.
     *
     * @param array<string, mixed> $filters Filter parameters
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated payment results
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a payment by ID.
     *
     * @param string $id The payment ULID
     * @return Payment|null The payment or null if not found
     */
    public function findById(string $id): ?Payment;

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
    ): Payment;

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
    ): Payment;

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
    ): int;

    /**
     * Get payment statistics with optional filters.
     *
     * @param array<string, mixed> $filters Filter parameters
     * @return array<string, mixed> Statistics data
     */
    public function getStatistics(array $filters = []): array;

    /**
     * Get revenue data grouped by period.
     *
     * @param string $period Period type (day, week, month, year)
     * @param string|null $startDate Start date filter
     * @param string|null $endDate End date filter
     * @return Collection Revenue data collection
     */
    public function getRevenueByPeriod(
        string $period = 'month',
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection;

    /**
     * Get recent failed payments.
     *
     * @param int $limit Maximum number of results
     * @return Collection Collection of failed payments with tenant relation
     */
    public function getFailedPayments(int $limit = 10): Collection;
}
