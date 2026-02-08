<?php

/**
 * Console Routes
 *
 * This file defines scheduled console commands and artisan closures.
 * All scheduled tasks run via Laravel's task scheduler (cron).
 *
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 *
 * @version    1.0.0
 *
 * @since      1.0.0
 */

use App\Jobs\AnonymizeOldActivitiesJob;
use App\Jobs\AnonymizeOldNotificationsJob;
use App\Jobs\ArchiveOldNotificationsJob;
use App\Jobs\CheckExpiredAddonsJob;
use App\Jobs\CheckExpiredSubscriptionsJob;
use App\Jobs\CheckTrialExpiryJob;
use App\Jobs\ExpireOldCheckoutsJob;
use App\Jobs\ExpireOldInvitationsJob;
use App\Jobs\ProcessScheduledDowngradesJob;
use App\Jobs\ResetMeteredUsageJob;
use App\Jobs\SendAddonExpiryReminderJob;
use App\Jobs\SendSubscriptionRenewalReminderJob;
use App\Jobs\SendTrialEndingReminderJob;
use App\Jobs\SoftDeleteOldActivitiesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Checkout & Payment Lifecycle
|--------------------------------------------------------------------------
|
| Checkout expiry is defined by:
| config('herkobi.subscription.checkout_expiry_minutes')
| Default: 30 minutes
|
| Job MUST run more frequently than expiry window.
|
*/
$checkoutExpiryMinutes = (int) config('herkobi.subscription.checkout_expiry_minutes', 30);

$checkoutJobFrequencyMinutes = max(1, intdiv($checkoutExpiryMinutes, 2));

Schedule::job(new ExpireOldCheckoutsJob)
    ->cron('*/'.$checkoutJobFrequencyMinutes.' * * * *');

/*
|--------------------------------------------------------------------------
| Subscription & Trial Lifecycle
|--------------------------------------------------------------------------
*/
Schedule::job(new CheckExpiredSubscriptionsJob)
    ->hourly();

Schedule::job(new CheckTrialExpiryJob)
    ->hourly();

/*
|--------------------------------------------------------------------------
| Subscription & Trial Reminders
|--------------------------------------------------------------------------
*/
Schedule::job(new SendSubscriptionRenewalReminderJob)
    ->daily();

Schedule::job(new SendTrialEndingReminderJob)
    ->daily();

/*
|--------------------------------------------------------------------------
| Addon Lifecycle
|--------------------------------------------------------------------------
*/
Schedule::job(new CheckExpiredAddonsJob)
    ->daily();

Schedule::job(new SendAddonExpiryReminderJob)
    ->daily();

/*
|--------------------------------------------------------------------------
| Invitation Lifecycle
|--------------------------------------------------------------------------
*/
Schedule::job(new ExpireOldInvitationsJob)
    ->daily();

/*
|--------------------------------------------------------------------------
| Retention & KVKK / GDPR Jobs
|--------------------------------------------------------------------------
*/
Schedule::job(new ArchiveOldNotificationsJob)
    ->daily();

Schedule::job(new AnonymizeOldNotificationsJob)
    ->daily();

Schedule::job(new AnonymizeOldActivitiesJob)
    ->daily();

Schedule::job(new SoftDeleteOldActivitiesJob)
    ->daily();

/*
|--------------------------------------------------------------------------
| Usage & Billing Maintenance
|--------------------------------------------------------------------------
*/
Schedule::job(new ResetMeteredUsageJob)
    ->daily();

Schedule::job(new ProcessScheduledDowngradesJob)
    ->daily();
