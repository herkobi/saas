export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    two_factor_enabled: boolean;
    created_at: string;
    updated_at: string;
}

export interface AuthTenant {
    id: string;
    name: string;
    role: string;
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
