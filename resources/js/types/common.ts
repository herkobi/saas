export interface FlashMessage {
    success: string | null;
    error: string | null;
    warning: string | null;
    info: string | null;
}

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
    properties?: Record<string, any>;
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
