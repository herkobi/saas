<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import AppLayout from '@/layouts/App.vue';
import type { Activity, PaginatedData, TenantUser } from '@/types';

const props = defineProps<{
    user: TenantUser;
    activities: PaginatedData<Activity>;
}>();

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppLayout title="Kullanıcı Aktiviteleri">
        <div class="mx-auto max-w-3xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link :href="`/app/account/users/${user.id}`">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <div>
                    <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Aktiviteler</h2>
                    <p class="text-sm text-surface-500 dark:text-surface-400">{{ user.name }}</p>
                </div>
            </div>

            <div v-if="activities.data.length === 0" class="py-12 text-center">
                <i class="pi pi-history mb-3 text-4xl text-surface-300 dark:text-surface-600" />
                <p class="text-sm text-surface-500 dark:text-surface-400">Henüz aktivite kaydı yok.</p>
            </div>

            <div v-else class="flex flex-col gap-3">
                <Card v-for="activity in activities.data" :key="activity.id">
                    <template #content>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-surface-900 dark:text-surface-0">{{ activity.description }}</span>
                                <span class="ml-2 text-xs text-surface-400">{{ activity.type }}</span>
                            </div>
                            <span class="whitespace-nowrap text-xs text-surface-400">{{ formatDate(activity.created_at) }}</span>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
