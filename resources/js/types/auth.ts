export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    two_factor_enabled: boolean;
    created_at: string;
    updated_at: string;
}

export interface Auth {
    user: User | null;
}

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
