export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
}

export interface Activity {
    id: string;
    type: string;
    description: string;
    log?: Record<string, any>;
    user_id?: string;
    user_type?: 'admin' | 'tenant';
    tenant_id?: string | null;
    created_at: string;
}

export interface Notification {
    id: string;
    data: {
        title: string;
        message: string;
        action_url?: string;
    };
    read_at: string | null;
    created_at: string;
}
