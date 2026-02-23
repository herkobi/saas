<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArchiveX, Bell, ArrowLeft } from 'lucide-vue-next';
import Heading from '@/components/common/Heading.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/pages/app/Profile/layout/Layout.vue';
import { index, archived } from '@/routes/app/profile/notifications';
import type { BreadcrumbItem, Notification, PaginatedData } from '@/types';

type Props = {
    archivedNotifications: PaginatedData<Notification>;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Bildirimler',
        href: index().url,
    },
    {
        title: 'Arşiv',
        href: archived().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Arşivlenmiş bildirimler" />

        <h1 class="sr-only">Arşivlenmiş Bildirimler</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <div class="flex items-center justify-between">
                    <Heading
                        variant="small"
                        title="Arşivlenmiş bildirimler"
                        description="Eski bildirimlerinizi görüntüleyin"
                    />

                    <Button variant="outline" size="sm" as-child>
                        <Link :href="index()">
                            <ArrowLeft class="mr-1.5 h-4 w-4" />
                            Bildirimlere dön
                        </Link>
                    </Button>
                </div>

                <EmptyState
                    v-if="archivedNotifications.data.length === 0"
                    :icon="ArchiveX"
                    message="Arşivlenmiş bildirim bulunmuyor"
                    description="Eski bildirimler burada görünecek."
                    bordered
                />

                <div v-else class="space-y-3">
                    <Card
                        v-for="notification in archivedNotifications.data"
                        :key="notification.id"
                        class="opacity-75"
                    >
                        <CardHeader class="p-4 pb-2">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground">
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
                        </CardHeader>
                        <CardContent class="px-4 pb-4 pl-[3.75rem]">
                            <p class="text-sm text-muted-foreground">
                                {{ notification.data.message }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <SimplePagination :data="archivedNotifications" label="bildirim" class="pt-2" />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
