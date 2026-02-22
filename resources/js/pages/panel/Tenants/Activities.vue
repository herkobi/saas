<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { History } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDateTime } from '@/composables/useFormatting';
import { useTenantTabs } from '@/composables/useTenantTabs';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index } from '@/routes/panel/tenants';
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
const tabs = useTenantTabs(props.tenant.id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: `/panel/tenants/${props.tenant.id}` },
    { title: 'Aktiviteler', href: activitiesIndex(props.tenant.id).url },
];
</script>

<template>
    <Head title="Aktiviteler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-lg font-semibold">{{ tenant.name }}</h1>
                <p class="text-sm text-muted-foreground">Aktivite geçmişi</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 overflow-x-auto border-b">
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="tab.href === activitiesIndex(tenant.id).url
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted-foreground/30 hover:text-foreground'"
                >
                    {{ tab.title }}
                </Link>
            </div>

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
                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <History class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">Henüz aktivite bulunmuyor</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="activities.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    {{ activities.from }}–{{ activities.to }} / {{ activities.total }} aktivite
                </p>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="!activities.links.prev" as-child>
                        <Link v-if="activities.links.prev" :href="activities.links.prev">Önceki</Link>
                        <span v-else>Önceki</span>
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!activities.links.next" as-child>
                        <Link v-if="activities.links.next" :href="activities.links.next">Sonraki</Link>
                        <span v-else>Sonraki</span>
                    </Button>
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
