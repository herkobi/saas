<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import Card from 'primevue/card';
import { Link } from '@inertiajs/vue3';
import PanelLayout from '@/layouts/Panel.vue';
import type { Notification, PaginatedData } from '@/types';

defineProps<{
    notifications: PaginatedData<Notification>;
    unreadCount: number;
}>();

const markAsRead = (notificationId: string) => {
    useForm({ notification_id: notificationId }).post('/panel/profile/notifications/mark-as-read', {
        preserveScroll: true,
    });
};

const markAllAsRead = () => {
    useForm({}).post('/panel/profile/notifications/mark-all-as-read', {
        preserveScroll: true,
    });
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <PanelLayout title="Bildirimler">
        <div class="mx-auto max-w-3xl flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Bildirimler</h2>
                    <Badge v-if="unreadCount > 0" :value="unreadCount" severity="danger" />
                </div>
                <div class="flex gap-2">
                    <Link href="/panel/profile/notifications/archived">
                        <Button label="Arşiv" icon="pi pi-inbox" outlined size="small" />
                    </Link>
                    <Button v-if="unreadCount > 0" label="Tümünü Okundu İşaretle" icon="pi pi-check-circle" text size="small" @click="markAllAsRead" />
                </div>
            </div>

            <div v-if="notifications.data.length === 0" class="py-12 text-center">
                <i class="pi pi-bell-slash mb-3 text-4xl text-surface-300 dark:text-surface-600" />
                <p class="text-sm text-surface-500 dark:text-surface-400">Bildiriminiz bulunmuyor.</p>
            </div>

            <div v-else class="flex flex-col gap-3">
                <Card v-for="notification in notifications.data" :key="notification.id" :class="{ '!border-l-4 !border-l-primary-500': !notification.read_at }">
                    <template #content>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-surface-900 dark:text-surface-0">{{ notification.data.title }}</p>
                                <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">{{ notification.data.message }}</p>
                                <p class="mt-1 text-xs text-surface-400">{{ formatDate(notification.created_at) }}</p>
                            </div>
                            <Button v-if="!notification.read_at" icon="pi pi-check" text size="small" severity="secondary" @click="markAsRead(notification.id)" aria-label="Okundu İşaretle" />
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </PanelLayout>
</template>
