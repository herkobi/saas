import { show as tenantShow } from '@/routes/panel/tenants';
import { index as activitiesIndex } from '@/routes/panel/tenants/activities';
import { index as addonsIndex } from '@/routes/panel/tenants/addons';
import { index as featuresIndex } from '@/routes/panel/tenants/features';
import { index as paymentsIndex } from '@/routes/panel/tenants/payments';
import { show as subscriptionShow } from '@/routes/panel/tenants/subscription';
import { index as usersIndex } from '@/routes/panel/tenants/users';
import type { NavItem } from '@/types';

export function useTenantTabs(tenantId: string): NavItem[] {
    return [
        { title: 'Genel', href: tenantShow(tenantId), icon: 'Info' },
        { title: 'Abonelik', href: subscriptionShow(tenantId), icon: 'CreditCard' },
        { title: 'Ödemeler', href: paymentsIndex(tenantId), icon: 'Wallet' },
        { title: 'Kullanıcılar', href: usersIndex(tenantId), icon: 'Users' },
        { title: 'Özellikler', href: featuresIndex(tenantId), icon: 'SlidersHorizontal' },
        { title: 'Eklentiler', href: addonsIndex(tenantId), icon: 'Package' },
        { title: 'Aktiviteler', href: activitiesIndex(tenantId), icon: 'History' },
    ];
}
