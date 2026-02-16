<script setup lang="ts">
import AppContent from '@/components/app/AppContent.vue';
import AppShell from '@/components/app/AppShell.vue';
import AppSidebar from '@/components/app/AppSidebar.vue';
import AppSidebarHeader from '@/components/app/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>
