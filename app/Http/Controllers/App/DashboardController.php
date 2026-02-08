<?php

/**
 * Tenant Dashboard Controller
 *
 * This controller handles the main dashboard view for tenant users,
 * displaying key statistics and overview information.
 *
 * @package    App\Http\Controllers\App
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for tenant dashboard.
 *
 * Provides the main overview page for tenant users with
 * key metrics and statistics.
 */
class DashboardController extends Controller
{
    /**
     * Display the tenant dashboard.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $tenant = app(Tenant::class);
        $user = $request->user();

        // Subscription bilgileri
        $subscription = $tenant->subscription;
        $hasActiveSubscription = $tenant->hasActiveSubscription();

        // Subscription data hazırla
        $subscriptionData = null;
        $features = collect();
        $canUpgrade = false;

        if ($subscription) {
            $subscriptionData = [
                'status' => [
                    'label' => $subscription->status->label(),
                    'badge' => $subscription->status->badge(),
                ],
                'plan' => [
                    'name' => $subscription->price->plan->name,
                ],
                'price' => [
                    'amount' => $subscription->custom_price ?? $subscription->price->price,
                    'currency' => $subscription->custom_currency ?? $subscription->price->currency,
                    'interval_label' => $subscription->price->interval->label(),
                ],
                'ends_at' => $subscription->ends_at?->toISOString(),
            ];

            // Features
            $planFeatures = $subscription->price->plan->features;
            foreach ($planFeatures as $feature) {
                $limit = $tenant->getFeatureLimit($feature);
                $used = 0;

                if ($feature->isMetered() || $feature->isLimit()) {
                    $usage = $tenant->usages()
                        ->where('feature_id', $feature->id)
                        ->first();
                    $used = $usage?->used ?? 0;
                }

                $isUnlimited = $limit === PHP_INT_MAX;
                $percentage = 0;
                $remaining = 0;

                if (!$isUnlimited && $limit > 0) {
                    $percentage = min(100, round(($used / $limit) * 100));
                    $remaining = max(0, $limit - $used);
                }

                $features->push([
                    'name' => $feature->name,
                    'unit' => $feature->unit,
                    'usage' => [
                        'type' => $feature->isFeature() ? 'boolean' : 'numeric',
                        'enabled' => $limit > 0 || $isUnlimited,
                        'is_unlimited' => $isUnlimited,
                        'limit' => $limit,
                        'used' => $used,
                        'remaining' => $remaining,
                        'percentage' => $percentage,
                    ],
                ]);
            }

            // Check if upgrade available
            $canUpgrade = $subscription->status->isValid();
        }

        // Statistics
        $statistics = [
            'total_payments' => $tenant->payments()->completed()->count(),
            'total_amount' => $tenant->payments()->completed()->sum('amount'),
            'team_members' => $tenant->users()->count(),
        ];

        // Recent payments
        $recentPayments = $tenant->payments()
            ->with(['subscription.price.plan', 'addon'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'type' => $payment->subscription_id ? 'subscription' : 'addon',
                    'type_label' => $payment->subscription_id ? 'Abonelik' : 'Eklenti',
                    'description' => $payment->subscription_id
                        ? $payment->subscription->price->plan->name
                        : ($payment->addon->name ?? 'Ödeme'),
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'status_label' => $payment->status->label(),
                    'status_badge' => $payment->status->badge(),
                    'created_at' => $payment->created_at,
                ];
            });

        return Inertia::render('app/Dashboard', [
            'tenant' => $tenant,
            'hasActiveSubscription' => $hasActiveSubscription,
            'subscription' => $subscriptionData,
            'features' => $features,
            'statistics' => $statistics,
            'recentPayments' => $recentPayments,
            'canUpgrade' => $canUpgrade,
        ]);
    }
}
