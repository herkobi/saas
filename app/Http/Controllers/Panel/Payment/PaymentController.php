<?php

/**
 * Payment Controller
 *
 * This controller handles payment management operations
 * for the panel including listing, status updates, and invoice marking.
 *
 * @package    App\Http\Controllers\Panel\Payment
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Payment;

use App\Services\Panel\Payment\PaymentService;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Payment\MarkInvoicedRequest;
use App\Http\Requests\Panel\Payment\PaymentFilterRequest;
use App\Http\Requests\Panel\Payment\UpdatePaymentRequest;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for payment management.
 *
 * Provides listing, status updates, and invoice marking
 * functionality for payments.
 */
class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param PaymentService $paymentService Service for payment operations
     */
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    /**
     * Display a listing of payments.
     *
     * @param PaymentFilterRequest $request
     * @return View
     */
    public function index(PaymentFilterRequest $request): Response
    {
        $payments = $this->paymentService->getPaginated(
            $request->validated(),
            $request->integer('per_page', 15)
        );

        $statistics = $this->paymentService->getStatistics($request->validated());

        return Inertia::render('panel/Payments/Index', [
            'payments' => $payments,
            'statistics' => $statistics,
            'filters' => $request->validated(),
            'statuses' => collect(PaymentStatus::cases())->map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    /**
     * Display the specified payment.
     *
     * @param Payment $payment
     * @return View
     */
    public function show(Payment $payment): Response
    {
        $payment = $this->paymentService->findById($payment->id);

        return Inertia::render('panel/Payments/Show', [
            'payment' => $payment,
            'statuses' => collect(PaymentStatus::cases())->map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    /**
     * Update the status of a payment.
     *
     * @param UpdatePaymentRequest $request
     * @param Payment $payment
     * @return RedirectResponse
     */
    public function updateStatus(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $status = PaymentStatus::from($request->validated('status'));

        $this->paymentService->updateStatus(
            $payment,
            $status,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Ödeme durumu güncellendi.');
    }

    /**
     * Mark a payment as invoiced.
     *
     * @param Request $request
     * @param Payment $payment
     * @return RedirectResponse
     */
    public function markAsInvoiced(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->isInvoiced()) {
            return back()->with('info', 'Bu ödeme zaten faturalandırılmış.');
        }

        if (!$payment->isCompleted()) {
            return back()->with('error', 'Sadece tamamlanmış ödemeler faturalandırılabilir.');
        }

        $this->paymentService->markAsInvoiced(
            $payment,
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Ödeme faturalandırıldı olarak işaretlendi.');
    }

    /**
     * Mark multiple payments as invoiced.
     *
     * @param MarkInvoicedRequest $request
     * @return RedirectResponse
     */
    public function markManyAsInvoiced(MarkInvoicedRequest $request): RedirectResponse
    {
        $count = $this->paymentService->markManyAsInvoiced(
            $request->validated('payment_ids'),
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', "{$count} ödeme faturalandırıldı olarak işaretlendi.");
    }
}
