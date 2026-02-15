import type { InertiaLinkProps } from '@inertiajs/vue3';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: string;
    isActive?: boolean;
    badge?: string | number;
};

export type NavGroup = {
    title: string;
    items: NavItem[];
};
