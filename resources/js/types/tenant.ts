export interface Tenant {
    id: string;
    name: string;
    code: string;
    slug: string;
    domain?: string | null;
    status?: string;
    account: {
        title?: string;
        company_name?: string;
        tax_office?: string;
        tax_number?: string;
        address?: string;
        city?: string;
        country?: string;
        phone?: string;
    };
    data?: Record<string, any>;
    created_at?: string;
    updated_at?: string;
}

export interface TenantUser {
    id: string;
    name: string;
    email: string;
    role: number;
    status: number;
    pivot?: {
        role?: number;
        status?: number;
    };
    created_at: string;
}

export interface Invitation {
    id: string;
    tenant_id: string;
    email: string;
    role: number;
    status: 'pending' | 'accepted' | 'expired' | 'revoked';
    invited_by: string;
    accepted_by?: string | null;
    expires_at: string;
    accepted_at?: string | null;
    invited_by_user?: { id: string; name: string };
    accepted_by_user?: { id: string; name: string };
    created_at: string;
    updated_at: string;
}
