<script setup lang="ts">
import { LayoutGrid } from 'lucide-vue-next';
import AppLogo from '@/components/common/AppLogo.vue';
import NavMain from '@/components/app/NavMain.vue';
import NavUser from '@/components/app/NavUser.vue';
import type { SidebarProps } from '@/components/ui/sidebar';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarRail,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/app';

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
        </SidebarContent>
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
