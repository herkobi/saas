export interface User {
    id: string;
    name: string;
    email: string;
    type: string;
    status: string;
    two_factor_enabled: boolean;
    email_verified_at?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface AuthTenant {
    id: string;
    name: string;
    role: string;
    status: number;
}

export interface Auth {
    user: User | null;
    tenants: AuthTenant[];
    can_create_tenant: boolean;
}

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
