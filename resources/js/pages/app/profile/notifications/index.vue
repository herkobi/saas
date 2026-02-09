<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import Card from 'primevue/card';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/App.vue';
import { markAsRead, markAllAsRead } from '@/routes/app/profile/notifications';
import type { Notification, PaginatedData } from '@/types';

defineProps<{
    notifications: PaginatedData<Notification>;
    unreadCount: number;
}>();

const markRead = (notificationId: string) => {
    useForm({ notification_id: notificationId }).post(markAsRead.url(), {
        preserveScroll: true,
    });
};

const markAllRead = () => {
    useForm({}).post(markAllAsRead.url(), {
        preserveScroll: true,
    });
};

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
    <AppLayout title="Bildirimler">
        <div class="mx-auto max-w-3xl">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Bildirimler</h2>
                    <p class="mt-1 text-sm text-surface-500 dark:text-surface-400">
                        <span v-if="unreadCount > 0">{{ unreadCount }} okunmamış bildiriminiz var.</span>
                        <span v-else>Tüm bildirimleriniz okunmuş.</span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link href="/app/profile/notifications/archived">
                        <Button label="Arşiv" icon="pi pi-inbox" text size="small" />
                    </Link>
                    <Button v-if="unreadCount > 0" label="Tümünü Okundu İşaretle" icon="pi pi-check-circle" outlined size="small" @click="markAllRead" />
                </div>
            </div>

            <div v-if="notifications.data.length === 0" class="py-12 text-center">
                <i class="pi pi-bell-slash mb-3 text-4xl text-surface-300 dark:text-surface-600" />
                <p class="text-sm text-surface-500 dark:text-surface-400">Henüz bildiriminiz yok.</p>
            </div>

            <div v-else class="flex flex-col gap-3">
                <Card v-for="notification in notifications.data" :key="notification.id" :class="{ 'ring-1 ring-primary-200 dark:ring-primary-800': !notification.read_at }">
                    <template #content>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">{{ notification.data.title }}</span>
                                    <Badge v-if="!notification.read_at" severity="info" />
                                </div>
                                <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">{{ notification.data.message }}</p>
                                <span class="mt-2 block text-xs text-surface-400">{{ formatDate(notification.created_at) }}</span>
                            </div>
                            <div class="flex gap-1">
                                <Button v-if="!notification.read_at" icon="pi pi-check" text size="small" severity="secondary" @click="markRead(notification.id)" aria-label="Okundu işaretle" />
                                <Link v-if="notification.data.action_url" :href="notification.data.action_url">
                                    <Button icon="pi pi-arrow-right" text size="small" aria-label="Detaya git" />
                                </Link>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
