export interface Tenant {
    id: string;
    name: string;
    slug: string;
    account: {
        company_name?: string;
        tax_office?: string;
        tax_number?: string;
        address?: string;
        city?: string;
        country?: string;
        phone?: string;
    };
    created_at: string;
    updated_at: string;
}

export interface TenantUser {
    id: string;
    name: string;
    email: string;
    role: 'owner' | 'staff';
    pivot?: {
        status?: number;
    };
    created_at: string;
}

export interface Invitation {
    id: string;
    tenant_id: string;
    email: string;
    role: 'staff';
    status: 'pending' | 'accepted' | 'expired' | 'revoked';
    invited_by: string;
    expires_at: string;
    created_at: string;
    updated_at: string;
}
