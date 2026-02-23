<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    Eye,
    Search,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { formatDate } from '@/composables/useFormatting';
import { useUserStatus } from '@/composables/useUserStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as usersIndex, show } from '@/routes/app/account/users';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { TenantUser } from '@/types/tenant';
import { useDebounceFn } from '@vueuse/core';

const props = defineProps<{
    users: PaginatedData<TenantUser>;
}>();

const { getRoleLabel, getRoleVariant, getStatusLabel, getStatusVariant } = useUserStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Kullanıcılar', href: usersIndex().url },
];

const search = ref('');

function applyFilters() {
    router.get(usersIndex().url, {
        search: search.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

const debouncedSearch = useDebounceFn(applyFilters, 300);
watch(search, debouncedSearch);

function goToPage(page: number) {
    router.get(usersIndex().url, {
        search: search.value || undefined,
        page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Kullanıcılar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <!-- Search -->
            <Card>
                <CardContent class="pt-6">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="İsim veya e-posta ile ara..."
                            class="pl-10 sm:max-w-xs"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Kullanıcı</TableHead>
                                <TableHead>Rol</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Katılma Tarihi</TableHead>
                                <TableHead class="w-[70px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users.data" :key="user.id">
                                <TableCell>
                                    <div>
                                        <p class="font-medium">{{ user.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getRoleVariant(user.pivot?.role ?? user.role)">
                                        {{ getRoleLabel(user.pivot?.role ?? user.role) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(user.pivot?.status ?? user.status)">
                                        {{ getStatusLabel(user.pivot?.status ?? user.status) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(user.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <Button variant="ghost" size="icon" as-child>
                                        <Link :href="show(user.id).url">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="users.data.length === 0">
                                <TableCell :colspan="5" class="py-12 text-center text-muted-foreground">
                                    Kullanıcı bulunamadı.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="flex justify-center">
                <Pagination
                    :total="users.total"
                    :items-per-page="users.per_page"
                    :default-page="users.current_page"
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
                                    :class="{ 'border-primary': item.value === users.current_page }"
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
