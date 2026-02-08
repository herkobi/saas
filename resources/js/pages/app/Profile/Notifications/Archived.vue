<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import AppLayout from '@/layouts/App.vue';
import type { Notification, PaginatedData } from '@/types';

const props = defineProps<{
    archivedNotifications: PaginatedData<Notification>;
}>();

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <AppLayout title="Arşivlenmiş Bildirimler">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Arşivlenmiş Bildirimler</h2>
                    <p class="mt-1 text-sm text-surface-500 dark:text-surface-400">Eski bildirimleriniz burada listelenir.</p>
                </div>
                <Link href="/app/profile/notifications">
                    <Button label="Bildirimlere Dön" icon="pi pi-arrow-left" text size="small" />
                </Link>
            </div>

            <div v-if="archivedNotifications.data.length === 0" class="py-12 text-center">
                <i class="pi pi-inbox mb-3 text-4xl text-surface-300 dark:text-surface-600" />
                <p class="text-sm text-surface-500 dark:text-surface-400">Arşivlenmiş bildiriminiz yok.</p>
            </div>

            <div v-else class="flex flex-col gap-3">
                <Card v-for="notification in archivedNotifications.data" :key="notification.id">
                    <template #content>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">{{ notification.data.title }}</span>
                                <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">{{ notification.data.message }}</p>
                                <span class="mt-2 block text-xs text-surface-400">{{ formatDate(notification.created_at) }}</span>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
