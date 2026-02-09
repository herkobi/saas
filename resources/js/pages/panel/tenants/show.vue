<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';
import { formatDate, formatDateTime } from '@/composables/useFormatting';
import type { Activity, PaginatedData } from '@/types';

defineProps<{
    tenant: any;
    statistics: Record<string, any>;
    activities: PaginatedData<Activity>;
}>();

</script>

<template>
    <PanelLayout title="Hesap Detayı">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/panel/tenants">
                        <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">{{ tenant.name }}</h2>
                        <p class="text-sm text-surface-500">{{ formatDate(tenant.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ statistics?.users_count ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Kullanıcılar</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ statistics?.payments_count ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Ödemeler</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ statistics?.addons_count ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Eklentiler</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <Tag :value="statistics?.subscription_status_label ?? 'Yok'" :severity="(statistics?.subscription_status_badge as any) ?? 'secondary'" />
                            <p class="mt-1 text-xs text-surface-500">Abonelik</p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Navigation Links -->
            <div class="flex flex-wrap gap-2">
                <Link :href="`/panel/tenants/${tenant.id}/subscription`">
                    <Button label="Abonelik" icon="pi pi-credit-card" outlined size="small" />
                </Link>
                <Link :href="`/panel/tenants/${tenant.id}/payments`">
                    <Button label="Ödemeler" icon="pi pi-wallet" outlined size="small" />
                </Link>
                <Link :href="`/panel/tenants/${tenant.id}/users`">
                    <Button label="Kullanıcılar" icon="pi pi-users" outlined size="small" />
                </Link>
                <Link :href="`/panel/tenants/${tenant.id}/features`">
                    <Button label="Özellikler" icon="pi pi-list" outlined size="small" />
                </Link>
                <Link :href="`/panel/tenants/${tenant.id}/addons`">
                    <Button label="Eklentiler" icon="pi pi-box" outlined size="small" />
                </Link>
            </div>

            <!-- Recent Activities -->
            <Card>
                <template #title><span class="text-base font-semibold">Son Aktiviteler</span></template>
                <template #content>
                    <div v-if="activities.data.length === 0" class="py-6 text-center">
                        <p class="text-sm text-surface-500">Henüz aktivite yok.</p>
                    </div>
                    <div v-else class="flex flex-col gap-2">
                        <div v-for="activity in activities.data" :key="activity.id" class="flex items-start justify-between gap-3 border-b border-surface-100 py-2 last:border-0 dark:border-surface-800">
                            <div class="flex-1">
                                <span class="text-sm text-surface-900 dark:text-surface-0">{{ activity.description }}</span>
                                <span class="ml-2 text-xs text-surface-400">{{ activity.type }}</span>
                            </div>
                            <span class="whitespace-nowrap text-xs text-surface-400">{{ formatDateTime(activity.created_at) }}</span>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
