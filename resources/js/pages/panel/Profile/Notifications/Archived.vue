<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import PanelLayout from '@/layouts/Panel.vue';
import type { Notification, PaginatedData } from '@/types';

const props = defineProps<{
    archivedNotifications: PaginatedData<Notification>;
}>();

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <PanelLayout title="Arşivlenmiş Bildirimler">
        <div class="mx-auto max-w-3xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/panel/profile/notifications">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Arşivlenmiş Bildirimler</h2>
            </div>

            <div v-if="archivedNotifications.data.length === 0" class="py-12 text-center">
                <i class="pi pi-inbox mb-3 text-4xl text-surface-300 dark:text-surface-600" />
                <p class="text-sm text-surface-500 dark:text-surface-400">Arşivlenmiş bildirim bulunmuyor.</p>
            </div>

            <div v-else class="flex flex-col gap-3">
                <Card v-for="notification in archivedNotifications.data" :key="notification.id">
                    <template #content>
                        <div>
                            <p class="text-sm font-medium text-surface-900 dark:text-surface-0">{{ notification.data.title }}</p>
                            <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">{{ notification.data.message }}</p>
                            <p class="mt-1 text-xs text-surface-400">{{ formatDate(notification.created_at) }}</p>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </PanelLayout>
</template>
