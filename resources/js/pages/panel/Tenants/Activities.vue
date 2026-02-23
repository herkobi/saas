<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { History } from 'lucide-vue-next';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDateTime } from '@/composables/useFormatting';
import PanelLayout from '@/layouts/PanelLayout.vue';
import TenantLayout from '@/pages/panel/Tenants/layout/Layout.vue';
import { index, show as tenantShow } from '@/routes/panel/tenants';
import { index as activitiesIndex } from '@/routes/panel/tenants/activities';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData, Activity } from '@/types/common';
import type { Tenant } from '@/types/tenant';

type Props = {
    tenant: Tenant;
    activities: PaginatedData<Activity>;
    statistics: {
        total: number;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: tenantShow(props.tenant.id).url },
    { title: 'Aktiviteler', href: activitiesIndex(props.tenant.id).url },
];
</script>

<template>
    <Head title="Aktiviteler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <TenantLayout
            :tenant-id="tenant.id"
            :tenant-name="tenant.name"
            :tenant-code="tenant.code"
            :tenant-slug="tenant.slug"
        >
            <!-- Activities -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">
                        Aktiviteler
                        <span v-if="statistics.total" class="ml-1 text-muted-foreground font-normal">({{ statistics.total }})</span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="activities.data.length > 0" class="space-y-4">
                        <div
                            v-for="activity in activities.data"
                            :key="activity.id"
                            class="flex items-start gap-3 border-b pb-4 last:border-0 last:pb-0"
                        >
                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted">
                                <History class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm leading-snug">{{ activity.description }}</p>
                                <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                    <span>{{ formatDateTime(activity.created_at) }}</span>
                                    <span v-if="activity.type" class="rounded bg-muted px-1.5 py-0.5">{{ activity.type }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <EmptyState v-else :icon="History" message="Henüz aktivite bulunmuyor" />
                </CardContent>
            </Card>

            <SimplePagination :data="activities" label="aktivite" />
        </TenantLayout>
    </PanelLayout>
</template>
