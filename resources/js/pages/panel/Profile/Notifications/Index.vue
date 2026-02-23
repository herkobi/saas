<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, BellOff, CheckCheck, ExternalLink, Archive } from 'lucide-vue-next';
import Heading from '@/components/common/Heading.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatDateTime } from '@/composables/useFormatting';
import AppLayout from '@/layouts/PanelLayout.vue';
import SettingsLayout from '@/pages/panel/Profile/layout/Layout.vue';
import { index } from '@/routes/panel/profile/notifications';
import { archived, markAsRead, markAllAsRead } from '@/routes/panel/profile/notifications';
import type { BreadcrumbItem, Notification, PaginatedData } from '@/types';

type Props = {
    notifications: PaginatedData<Notification>;
    unreadCount: number;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Bildirimler',
        href: index().url,
    },
];

function handleMarkAsRead(notificationId: string) {
    router.post(markAsRead().url, {
        notification_id: notificationId,
    }, {
        preserveScroll: true,
    });
}

function handleMarkAllAsRead() {
    router.post(markAllAsRead().url, {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Bildirimler" />

        <h1 class="sr-only">Bildirimler</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Bildirimler"
                        description="Hesabınıza ait bildirimleri görüntüleyin"
                    />

                    <div class="flex items-center gap-2">
                        <Button
                            v-if="unreadCount > 0"
                            variant="outline"
                            size="sm"
                            @click="handleMarkAllAsRead"
                        >
                            <CheckCheck class="mr-1.5 h-4 w-4" />
                            Tümünü okundu işaretle
                        </Button>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="archived()">
                                <Archive class="mr-1.5 h-4 w-4" />
                                Arşiv
                            </Link>
                        </Button>
                    </div>
                </div>

                <div v-if="unreadCount > 0" class="text-sm text-muted-foreground">
                    <Badge variant="secondary">{{ unreadCount }}</Badge>
                    okunmamış bildiriminiz var.
                </div>

                <EmptyState
                    v-if="notifications.data.length === 0"
                    :icon="BellOff"
                    message="Bildirim bulunmuyor"
                    description="Yeni bildirimler burada görünecek."
                    bordered
                />

                <div v-else class="space-y-3">
                    <Card
                        v-for="notification in notifications.data"
                        :key="notification.id"
                        :class="[
                            'transition-colors',
                            !notification.read_at && 'border-primary/20 bg-primary/[0.02] dark:bg-primary/[0.04]',
                        ]"
                    >
                        <CardHeader class="p-4 pb-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div
                                        :class="[
                                            'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full',
                                            notification.read_at
                                                ? 'bg-muted text-muted-foreground'
                                                : 'bg-primary/10 text-primary',
                                        ]"
                                    >
                                        <Bell class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <CardTitle class="text-sm font-medium leading-tight">
                                            {{ notification.data.title }}
                                        </CardTitle>
                                        <CardDescription class="mt-1 text-xs">
                                            {{ formatDateTime(notification.created_at) }}
                                        </CardDescription>
                                    </div>
                                </div>

                                <Button
                                    v-if="!notification.read_at"
                                    variant="ghost"
                                    size="sm"
                                    class="shrink-0 text-xs"
                                    @click="handleMarkAsRead(notification.id)"
                                >
                                    Okundu
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="px-4 pb-4 pl-[3.75rem]">
                            <p class="text-sm text-muted-foreground">
                                {{ notification.data.message }}
                            </p>
                            <Button
                                v-if="notification.data.action_url"
                                variant="link"
                                size="sm"
                                class="mt-2 h-auto p-0 text-xs"
                                as-child
                            >
                                <Link :href="notification.data.action_url">
                                    Detayları gör
                                    <ExternalLink class="ml-1 h-3 w-3" />
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <SimplePagination :data="notifications" label="bildirim" class="pt-2" />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
