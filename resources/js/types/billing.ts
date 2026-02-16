export interface Plan {
    id: string;
    name: string;
    slug: string;
    description?: string;
    is_free: boolean;
    is_active: boolean;
    is_public: boolean;
    sort_order: number;
    tenant_id?: string | null;
    grace_period_days?: number;
    grace_period_policy?: 'none' | 'restricted' | 'blocked';
    upgrade_proration_type?: 'immediate' | 'end_of_period' | null;
    downgrade_proration_type?: 'immediate' | 'end_of_period' | null;
    archived_at?: string | null;
    prices?: PlanPrice[];
    features?: PlanFeature[];
    created_at: string;
    updated_at: string;
}

export interface PlanPrice {
    id: string;
    plan_id: string;
    plan?: Plan;
    interval: 'month' | 'year' | 'day';
    interval_count: number;
    price: number;
    currency: string;
    trial_days: number;
    created_at: string;
    updated_at: string;
}

export interface PlanFeature {
    id: string;
    plan_id: string;
    feature_id: string;
    value: string | null;
    feature?: Feature;
}

export interface Subscription {
    id: string;
    tenant_id: string;
    plan_price_id: string;
    next_plan_price_id?: string | null;
    plan_price?: PlanPrice;
    next_plan_price?: PlanPrice;
    status: 'active' | 'trialing' | 'canceled' | 'past_due' | 'expired';
    custom_price?: number | null;
    custom_currency?: string | null;
    starts_at: string;
    ends_at: string | null;
    trial_ends_at: string | null;
    canceled_at: string | null;
    grace_period_ends_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Payment {
    id: string;
    tenant_id: string;
    subscription_id?: string | null;
    addon_id?: string | null;
    gateway?: string;
    gateway_payment_id?: string | null;
    amount: number;
    currency: string;
    status: 'pending' | 'processing' | 'completed' | 'failed' | 'refunded' | 'cancelled';
    description?: string | null;
    paid_at: string | null;
    refunded_at?: string | null;
    invoiced_at?: string | null;
    created_at: string;
    updated_at?: string;
}

export interface Checkout {
    id: string;
    tenant_id: string;
    plan_price_id?: string | null;
    addon_id?: string | null;
    payment_id?: string | null;
    merchant_oid: string;
    type: 'new' | 'renew' | 'upgrade' | 'downgrade' | 'addon' | 'addon_renew';
    status: 'pending' | 'processing' | 'completed' | 'failed' | 'expired' | 'cancelled';
    quantity: number;
    amount: number;
    proration_credit: number;
    final_amount: number;
    currency: string;
    paytr_token?: string | null;
    billing_info?: Record<string, any>;
    metadata?: Record<string, any>;
    expires_at: string | null;
    completed_at: string | null;
    plan_price?: PlanPrice;
    addon?: Addon;
    created_at: string;
    updated_at: string;
}

export interface Addon {
    id: string;
    name: string;
    slug: string;
    description?: string;
    feature_id: string;
    addon_type: 'increment' | 'unlimited' | 'boolean';
    value?: number | null;
    price: number;
    currency: string;
    is_recurring: boolean;
    interval?: 'month' | 'year' | 'day' | null;
    interval_count?: number;
    is_active: boolean;
    is_public: boolean;
    feature?: Feature;
    created_at?: string;
    updated_at?: string;
}

export interface TenantAddon {
    id: string;
    tenant_id: string;
    addon_id: string;
    quantity: number;
    custom_price?: number | null;
    custom_currency?: string | null;
    started_at: string;
    expires_at: string | null;
    is_active: boolean;
    metadata?: Record<string, any>;
    addon?: Addon;
}

export interface Feature {
    id: string;
    name: string;
    slug: string;
    code: string;
    description?: string;
    type: 'limit' | 'feature' | 'metered';
    type_label?: string;
    unit?: string;
    reset_period?: 'daily' | 'weekly' | 'monthly' | 'yearly' | null;
    is_active: boolean;
    plans?: Plan[];
    created_at: string;
    updated_at: string;
}

export interface FeatureUsage {
    name: string;
    unit: string | null;
    usage: {
        type: 'boolean' | 'numeric';
        enabled: boolean;
        is_unlimited: boolean;
        limit: number;
        used: number;
        remaining: number;
        percentage: number;
    };
}
