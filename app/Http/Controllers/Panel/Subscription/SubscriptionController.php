<?php

/**
 * Subscription Controller
 *
 * This controller handles subscription management operations
 * for the panel including listing, filtering, and viewing details.
 *
 * @package    App\Http\Controllers\Panel\Subscription
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Subscription;

use App\Services\Panel\Subscription\SubscriptionService;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\Subscription\SubscriptionFilterRequest;
use App\Models\Plan;
use App\Models\Subscription;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for subscription management.
 *
 * Provides listing and viewing functionality for subscriptions
 * with comprehensive filtering and statistics.
 */
class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param SubscriptionService $subscriptionService Service for subscription operations
     */
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    /**
     * Display a listing of subscriptions.
     *
     * @param SubscriptionFilterRequest $request
     * @return View
     */
    public function index(SubscriptionFilterRequest $request): Response
    {
        $subscriptions = $this->subscriptionService->getPaginated(
            $request->validated(),
            $request->integer('per_page', 15)
        );

        $statistics = $this->subscriptionService->getStatistics($request->validated());

        $plans = Plan::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('panel/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'statistics' => $statistics,
            'filters' => $request->validated(),
            'plans' => $plans,
            'statuses' => collect(SubscriptionStatus::cases())->map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    /**
     * Display the specified subscription.
     *
     * @param Subscription $subscription
     * @return View
     */
    public function show(Subscription $subscription): Response
    {
        $subscription = $this->subscriptionService->findById($subscription->id);
        $payments = $this->subscriptionService->getPayments($subscription);

        return Inertia::render('panel/Subscriptions/Show', [
            'subscription' => $subscription,
            'payments' => $payments,
        ]);
    }
}
