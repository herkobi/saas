<script setup lang="ts">
import {
    Building2,
    CreditCard,
    LayoutGrid,
    Package,
    RefreshCcw,
    Settings2,
} from 'lucide-vue-next';
import AppLogo from '@/components/common/AppLogo.vue';
import NavMain from '@/components/panel/NavMain.vue';
import NavUser from '@/components/panel/NavUser.vue';
import type { SidebarProps } from '@/components/ui/sidebar';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarRail,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/panel';
import { index as paymentsIndex } from '@/routes/panel/payments';
import { index as plansIndex } from '@/routes/panel/plans';
import { index as addonsIndex } from '@/routes/panel/plans/addons';
import { index as featuresIndex } from '@/routes/panel/plans/features';
import { index as settingsIndex } from '@/routes/panel/settings/general';
import { index as subscriptionsIndex } from '@/routes/panel/subscriptions';
import { index as tenantsIndex } from '@/routes/panel/tenants';

const props = withDefaults(defineProps<SidebarProps>(), {
    collapsible: 'icon',
});

const mainNav = [
    {
        title: 'Başlangıç',
        url: dashboard().url,
        icon: LayoutGrid,
    },
];

const tenantsNav = [
    {
        title: 'Hesaplar',
        url: '#',
        icon: Building2,
        items: [
            { title: 'Hesaplar', url: tenantsIndex().url },
            { title: 'Kullanıcılar', url: tenantsIndex().url },
        ],
    },
    {
        title: 'Abonelikler',
        url: '#',
        icon: RefreshCcw,
        items: [
            { title: 'Aktif Abonelikler', url: subscriptionsIndex().url },
            { title: 'Süresi Dolmuş Abonelikler', url: subscriptionsIndex().url },
        ],
    },
    {
        title: 'Ödemeler',
        url: '#',
        icon: CreditCard,
        items: [
            { title: 'Yaklaşan Ödemeler', url: paymentsIndex().url },
            { title: 'Tamamlanan Ödemeler', url: paymentsIndex().url },
        ],
    },
];

const plansNav = [
    {
        title: 'Planlar',
        url: '#',
        icon: Package,
        items: [
            { title: 'Planlar', url: plansIndex().url },
            { title: 'Özellikler', url: featuresIndex().url },
            { title: 'Eklentiler', url: addonsIndex().url },
        ],
    },
];

const managementNav = [
    {
        title: 'Genel Ayarlar',
        url: settingsIndex().url,
        icon: Settings2
    },
];
</script>

<template>
    <Sidebar v-bind="props">
        <SidebarHeader>
            <div class="flex items-center gap-2 px-2 py-1.5">
                <AppLogo />
            </div>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainNav" />
            <NavMain label="Hesap Yönetimi" :items="tenantsNav" />
            <NavMain label="Plan Yönetimi" :items="plansNav" />
            <NavMain label="Sistem Ayarları" :items="managementNav" />
        </SidebarContent>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
