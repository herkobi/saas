import type { NavItem } from '@/types';

export function useTenantTabs(tenantId: string): NavItem[] {
    return [
        { title: 'Genel', href: `/panel/tenants/${tenantId}`, icon: 'pi pi-info-circle' },
        { title: 'Abonelik', href: `/panel/tenants/${tenantId}/subscription`, icon: 'pi pi-credit-card' },
        { title: 'Ödemeler', href: `/panel/tenants/${tenantId}/payments`, icon: 'pi pi-wallet' },
        { title: 'Kullanıcılar', href: `/panel/tenants/${tenantId}/users`, icon: 'pi pi-users' },
        { title: 'Özellikler', href: `/panel/tenants/${tenantId}/features`, icon: 'pi pi-sliders-h' },
        { title: 'Eklentiler', href: `/panel/tenants/${tenantId}/addons`, icon: 'pi pi-box' },
        { title: 'Aktiviteler', href: `/panel/tenants/${tenantId}/activities`, icon: 'pi pi-history' },
    ];
}
