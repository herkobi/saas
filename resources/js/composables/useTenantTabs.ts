import type { NavItem } from '@/types';

export function useTenantTabs(tenantId: string): NavItem[] {
    return [
        { title: 'Genel', href: `/panel/tenants/${tenantId}`, icon: 'Info' },
        { title: 'Abonelik', href: `/panel/tenants/${tenantId}/subscription`, icon: 'CreditCard' },
        { title: 'Ödemeler', href: `/panel/tenants/${tenantId}/payments`, icon: 'Wallet' },
        { title: 'Kullanıcılar', href: `/panel/tenants/${tenantId}/users`, icon: 'Users' },
        { title: 'Özellikler', href: `/panel/tenants/${tenantId}/features`, icon: 'SlidersHorizontal' },
        { title: 'Eklentiler', href: `/panel/tenants/${tenantId}/addons`, icon: 'Package' },
        { title: 'Aktiviteler', href: `/panel/tenants/${tenantId}/activities`, icon: 'History' },
    ];
}
