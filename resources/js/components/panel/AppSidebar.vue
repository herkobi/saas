<script setup lang="ts">
import {
    Building2,
    CreditCard,
    LayoutGrid,
    Package,
    Settings2,
    Users,
    Clock,
    Zap,
    Plus,
    Building,
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
import { index as companySettingsIndex } from '@/routes/panel/settings/company';
import { index as settingsIndex } from '@/routes/panel/settings/general';
import { index as subscriptionsIndex } from '@/routes/panel/subscriptions';
import { index as tenantsIndex } from '@/routes/panel/tenants';
import { index as usersIndex } from '@/routes/panel/users';

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
        url: tenantsIndex().url,
        icon: Building2,
    },
    {
        title: 'Abonelikler',
        url: subscriptionsIndex().url,
        icon: CreditCard,
    },
    {
        title: 'Kullanıcılar',
        url: usersIndex().url,
        icon: Users,
    },
];

const paymentsNav = [
    {
        title: 'Ödemeler',
        url: paymentsIndex().url,
        icon: CreditCard,
    },
    {
        title: 'Yaklaşan Ödemeler',
        url: `${paymentsIndex().url}?upcoming=1`,
        icon: Clock,
    },
];

const plansNav = [
    {
        title: 'Planlar',
        url: plansIndex().url,
        icon: Package,
    },
    {
        title: 'Özellikler',
        url: featuresIndex().url,
        icon: Zap,
    },
    {
        title: 'Eklentiler',
        url: addonsIndex().url,
        icon: Plus,
    },
];

const managementNav = [
    {
        title: 'Genel Ayarlar',
        url: settingsIndex().url,
        icon: Settings2,
    },
    {
        title: 'Firma Bilgileri',
        url: companySettingsIndex().url,
        icon: Building,
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
            <NavMain label="Ödemeler" :items="paymentsNav" />
            <NavMain label="Plan Yönetimi" :items="plansNav" />
            <NavMain label="Sistem" :items="managementNav" />
        </SidebarContent>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
