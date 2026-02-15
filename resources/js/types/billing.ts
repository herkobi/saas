export interface Plan {
    id: string;
    name: string;
    slug: string;
    description?: string;
    is_active: boolean;
    trial_days: number;
    created_at: string;
    updated_at: string;
}

export interface PlanPrice {
    id: string;
    plan_id: string;
    plan?: Plan;
    interval: 'monthly' | 'yearly' | 'lifetime';
    price: number;
    currency: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Subscription {
    id: string;
    tenant_id: string;
    plan_price_id: string;
    plan_price?: PlanPrice;
    status: 'active' | 'trial' | 'expired' | 'grace_period' | 'cancelled';
    trial_ends_at: string | null;
    starts_at: string;
    ends_at: string | null;
    grace_ends_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Payment {
    id: string;
    tenant_id: string;
    amount: number;
    currency: string;
    status: 'pending' | 'paid' | 'failed' | 'refunded' | 'cancelled';
    paid_at: string | null;
    created_at: string;
}

export interface Addon {
    id: string;
    name: string;
    slug: string;
    price: number;
    currency: string;
    is_active: boolean;
}

export interface Feature {
    id: string;
    name: string;
    slug: string;
    code: string;
    description?: string;
    type: string;
    type_label?: string;
    unit?: string;
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
