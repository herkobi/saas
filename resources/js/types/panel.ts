// Dashboard tipleri
export interface PlanDistribution {
    name: string;
    subscriber_count: number;
    total_revenue: number;
}

export interface ExpiringSubscription {
    id: string;
    ends_at: string;
    tenant: { id: string; name: string };
}

export interface FailedPayment {
    id: string;
    amount: number;
    created_at: string;
    tenant: { id: string; name: string };
}

// List item tipleri
export interface TenantListItem {
    id: string;
    name: string;
    owner_name?: string;
    owner_email?: string;
    subscription_status?: string;
    subscription_status_label?: string;
    subscription_status_badge?: string;
    plan_name?: string;
    created_at: string;
}

export interface PlanListItem {
    id: string;
    name: string;
    slug: string;
    is_active: boolean;
    is_public: boolean;
    is_free: boolean;
    prices_count: number;
    features_count: number;
    created_at: string;
}

export interface FeatureListItem {
    id: string;
    name: string;
    slug: string;
    code: string;
    type: string;
    type_label: string;
    unit?: string;
    is_active: boolean;
    created_at: string;
}

export interface AddonListItem {
    id: string;
    name: string;
    slug: string;
    price: number;
    currency: string;
    addon_type: string;
    addon_type_label: string;
    is_recurring: boolean;
    is_active: boolean;
    is_public: boolean;
    feature?: { id: string; name: string };
    created_at: string;
}

export interface EffectiveFeature {
    id: string;
    name: string;
    slug: string;
    type: string;
    value: any;
    source: string;
}

export interface StatusOption {
    value: number | string;
    label: string;
}

// Subscription list item
export interface SubscriptionListItem {
    id: string;
    tenant_name: string;
    tenant_id: string;
    plan_name: string;
    price_label?: string;
    status: string;
    status_label: string;
    status_badge?: string;
    starts_at: string;
    ends_at: string | null;
    trial_ends_at: string | null;
    created_at: string;
}

// Payment list item
export interface PaymentListItem {
    id: string;
    tenant_name?: string;
    tenant_id?: string;
    amount: number;
    currency: string;
    status: string;
    status_label: string;
    status_badge?: { variant?: string };
    gateway?: string;
    description?: string | null;
    paid_at: string | null;
    invoiced_at: string | null;
    created_at: string;
}

// Tenant statistics
export interface TenantStatistics {
    total_users: number;
    total_payments: number;
    total_revenue: number;
    subscription_status?: string;
    current_plan?: string;
    subscription_ends_at?: string | null;
    created_at: string;
}
