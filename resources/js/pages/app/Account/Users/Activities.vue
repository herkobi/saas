<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    Activity,
    Clock,
} from 'lucide-vue-next';
import EmptyState from '@/components/common/EmptyState.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { formatDateTime } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as usersIndex, show, activities as activitiesRoute } from '@/routes/app/account/users';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData, Activity as ActivityType } from '@/types/common';
import type { TenantUser } from '@/types/tenant';

const props = defineProps<{
    user: TenantUser;
    activities: PaginatedData<ActivityType>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Kullanıcılar', href: usersIndex().url },
    { title: props.user.name, href: show(props.user.id).url },
    { title: 'Aktiviteler', href: '#' },
];

function goToPage(page: number) {
    router.get(activitiesRoute(props.user.id).url, { page }, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`${user.name} - Aktiviteler`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <h1 class="flex items-center gap-2 text-xl font-bold">
                <Activity class="h-5 w-5" />
                {{ user.name }} — Aktivite Geçmişi
            </h1>

            <!-- Activities -->
            <Card>
                <CardContent class="p-0">
                    <div v-if="activities.data.length > 0" class="divide-y">
                        <div
                            v-for="activity in activities.data"
                            :key="activity.id"
                            class="flex items-start gap-3 p-4"
                        >
                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">{{ activity.description }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <Badge variant="outline" class="text-xs">{{ activity.type }}</Badge>
                                    <span class="text-xs text-muted-foreground">{{ formatDateTime(activity.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <EmptyState v-else :icon="Activity" message="Aktivite kaydı bulunamadı" />
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="activities.last_page > 1" class="flex justify-center">
                <Pagination
                    :total="activities.total"
                    :items-per-page="activities.per_page"
                    :default-page="activities.current_page"
                    @update:page="goToPage"
                >
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />
                        <template v-for="(item, index) in items" :key="index">
                            <PaginationEllipsis v-if="item.type === 'ellipsis'" :index="index" />
                            <PaginationItem v-else :value="item.value" as-child>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-9 w-9"
                                    :class="{ 'border-primary': item.value === activities.current_page }"
                                >
                                    {{ item.value }}
                                </Button>
                            </PaginationItem>
                        </template>
                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
        </AccountLayout>
    </AppLayout>
</template>
