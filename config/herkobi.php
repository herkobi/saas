<?php

/**
 * Herkobi Configuration
 *
 * This configuration file defines the architectural decisions for the Herkobi SaaS platform.
 * These settings determine the core behavior of multi-tenancy, team management,
 * and system-wide features.
 *
 * @author     Herkobi
 * @copyright  2025 Herkobi
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Management
    |--------------------------------------------------------------------------
    |
    | Configure how tenants are created and managed in the system.
    | These settings affect the core multi-tenancy behavior.
    |
    */

    'tenant' => [

        /**
         * Allow Team Members
         *
         * Determines whether tenants can have multiple users (team functionality).
         *
         * true  → Team collaboration enabled (owner can invite users)
         * false → Single-user tenants only (micro SaaS)
         *
         * When disabled:
         * - "Invite Member" button hidden in dashboard
         * - Team management features disabled
         * - Each tenant has only one user (the owner)
         */
        'allow_team_members' => env('HERKOBI_ALLOW_TEAM_MEMBERS', false),

        /**
         * Allow Multiple Tenants
         *
         * Determines whether a user can create multiple tenants.
         *
         * true  → Users can create multiple tenants ("Create New Tenant" button visible)
         * false → Users limited to one tenant (their first tenant)
         *
         * When disabled:
         * - "Create New Tenant" button hidden
         * - Tenant switcher hidden (if user has only one tenant)
         *
         * Note: Users can still be invited to multiple tenants by other owners.
         * This setting only controls tenant CREATION, not membership.
         */
        'allow_multiple_tenants' => env('HERKOBI_ALLOW_MULTIPLE_TENANTS', false),

    ],

    /*
    |--------------------------------------------------------------------------
    | Invitation Settings
    |--------------------------------------------------------------------------
    |
    | Configure invitation lifecycle for team member invitations.
    |
    */

    'invitation' => [

        /**
         * Invitation Expiry Days
         *
         * Number of days before a pending invitation expires.
         */
        'expires_days' => (int) env('HERKOBI_INVITATION_EXPIRES_DAYS', 7),

    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Settings
    |--------------------------------------------------------------------------
    |
    | Configure subscription lifecycle automation including reminders,
    | grace periods, and checkout expiration.
    |
    */

    'subscription' => [

        /**
         * Renewal Reminder Days
         *
         * Days before subscription expiry to send renewal reminders.
         * Reminders are sent at each specified interval.
         */
        'renewal_reminder_days' => [7, 3, 1],

        /**
         * Trial Reminder Days
         *
         * Days before trial expiry to send warning reminders.
         */
        'trial_reminder_days' => [3, 1],

        /**
         * Checkout Expiry Minutes
         *
         * Minutes after which pending checkout sessions are marked as expired.
         */
        'checkout_expiry_minutes' => 30,

    ],

    /*
    |--------------------------------------------------------------------------
    | Proration Settings
    |--------------------------------------------------------------------------
    |
    | Default proration behavior for plan changes.
    | These can be overridden per-plan via the plans table.
    |
    | Options: 'immediate' | 'end_of_period'
    |   immediate     → Anında geçiş + proration kredi hesaplama
    |   end_of_period → Dönem sonunda geçiş, ödeme o zaman alınır
    |
    */

    'proration' => [

        /**
         * Upgrade Behavior
         *
         * How plan upgrades are handled by default.
         * 'immediate': Pay the difference now, switch immediately
         * 'end_of_period': Switch at period end, pay then
         */
        'upgrade_behavior' => 'immediate',

        /**
         * Downgrade Behavior
         *
         * How plan downgrades are handled by default.
         * 'immediate': Switch immediately, credit/refund remaining
         * 'end_of_period': Switch at period end, no payment needed
         */
        'downgrade_behavior' => 'end_of_period',

    ],

    /*
    |--------------------------------------------------------------------------
    | Payment & Tax Settings
    |--------------------------------------------------------------------------
    |
    | Default country, currency and tax rate settings for payment processing.
    | These settings can be extended to multi-country support in the future.
    |
    */

    'payment' => [

        /**
         * Default Country (ISO 3166-1 alpha-2)
         */
        'country' => env('DEFAULT_COUNTRY', 'TR'),

        /**
         * Default Currency (ISO 4217)
         */
        'currency' => env('DEFAULT_CURRENCY', 'TRY'),

        /**
         * Default Currency Symbol (₺, $, €, etc.)
         */
        'currency_symbol' => env('DEFAULT_CURRENCY_SYMBOL', '₺'),

        /**
         * Default Tax Rate and Name (percentage)
         *
         * Example: 20 for 20% VAT/KDV
         */
        'tax_rate' => (float) env('DEFAULT_TAX_RATE', 20),
        'tax_name' => env('DEFAULT_TAX_NAME', 'KDV'),

        /**
         * Default Payment Gateway
         */
        'gateway' => env('DEFAULT_GATEWAY', 'paytr'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Addon Settings
    |--------------------------------------------------------------------------
    |
    | Configure addon lifecycle automation including expiry reminders
    | and auto-renewal behavior.
    |
    */

    'addon' => [

        /**
         * Expiry Reminder Days
         *
         * Days before addon expiry to send warning reminders.
         * Only applies to recurring addons with expires_at set.
         */
        'expiry_reminder_days' => [7, 3, 1],

        /**
         * Auto Renew
         *
         * Whether to automatically renew recurring addons on expiry.
         * Requires stored card/token payment support from the gateway.
         *
         * true  → Auto-renew and charge on expiry
         * false → Deactivate on expiry, send reminder notifications
         */
        'auto_renew' => false,

    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention & Privacy (KVKK/GDPR Compliance)
    |--------------------------------------------------------------------------
    |
    | Configure data retention policies and anonymization rules.
    |
    */

    'retention' => [

        /**
         * Activity Logs
         */
        'activities' => [
            // Anonymize activities older than this (days)
            'anonymize_after_days' => 730, // 2 years

            // Soft delete activities older than this (days)
            // null = never auto-delete
            'soft_delete_after_days' => null,
        ],

        /**
         * Notifications
         */
        'notifications' => [
            // Archive notifications older than this (days)
            'archive_after_days' => 90, // 3 months

            // Anonymize archived notifications older than this (days)
            'anonymize_after_days' => 730, // 2 years
        ],

    ],

];
