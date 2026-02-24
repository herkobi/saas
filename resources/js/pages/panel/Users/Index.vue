<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, Users } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate, formatDateTime } from '@/composables/useFormatting';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index } from '@/routes/panel/users';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';

type UserListItem = {
    id: string;
    name: string;
    email: string;
    status: number;
    status_label: string;
    status_badge: string;
    user_type: string;
    user_type_label: string;
    tenants: { id: string; name: string; role: number }[];
    last_login_at: string | null;
    created_at: string;
};

type StatusOption = {
    value: number;
    label: string;
};

type UserTypeOption = {
    value: string;
    label: string;
};

type Props = {
    users: PaginatedData<UserListItem>;
    statistics: {
        total_count: number;
        active_count: number;
        passive_count: number;
        draft_count: number;
        admin_count: number;
        tenant_count: number;
    };
    filters: {
        search?: string;
        status?: string;
        user_type?: string;
    };
    statuses: StatusOption[];
    userTypes: UserTypeOption[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kullanıcılar', href: index().url },
];

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const typeFilter = ref(props.filters.user_type ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters({ search: val || undefined });
    }, 300);
});

function applyFilters(override: Record<string, string | undefined> = {}) {
    const params: Record<string, string> = {};
    const s = override.search !== undefined ? override.search : search.value;
    const st = override.status !== undefined ? override.status : statusFilter.value;
    const t = override.user_type !== undefined ? override.user_type : typeFilter.value;

    if (s) params.search = s;
    if (st) params.status = st;
    if (t) params.user_type = t;

    router.get(index().url, params, { preserveState: true, replace: true });
}

function filterByStatus(val: string) {
    statusFilter.value = val;
    applyFilters({ status: val || undefined });
}

function filterByType(val: string) {
    typeFilter.value = val;
    applyFilters({ user_type: val || undefined });
}

function userStatusBadgeVariant(badge: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (badge) {
        case 'success': return 'default';
        case 'warning': return 'secondary';
        case 'secondary': return 'outline';
        default: return 'secondary';
    }
}
</script>

<template>
    <Head title="Kullanıcılar" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Kullanıcılar</h1>
                    <p class="text-sm text-muted-foreground">Tüm hesaplardaki kullanıcıları görüntüleyin</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <p class="text-sm text-muted-foreground">Toplam</p>
                        <p class="text-2xl font-bold">{{ statistics.total_count }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p class="text-sm text-muted-foreground">Aktif</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ statistics.active_count }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p class="text-sm text-muted-foreground">Yönetici</p>
                        <p class="text-2xl font-bold">{{ statistics.admin_count }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4">
                        <p class="text-sm text-muted-foreground">Müşteri</p>
                        <p class="text-2xl font-bold">{{ statistics.tenant_count }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-64">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Ad veya e-posta ara..."
                        class="pl-9"
                    />
                </div>
                <Select :model-value="statusFilter" @update:model-value="filterByStatus">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Durum" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Tümü</SelectItem>
                        <SelectItem
                            v-for="s in statuses"
                            :key="String(s.value)"
                            :value="String(s.value)"
                        >
                            {{ s.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select :model-value="typeFilter" @update:model-value="filterByType">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Tip" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Tümü</SelectItem>
                        <SelectItem
                            v-for="t in userTypes"
                            :key="t.value"
                            :value="t.value"
                        >
                            {{ t.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <Card>
                <CardContent class="p-0">
                    <Table v-if="users.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Ad</TableHead>
                                <TableHead>E-posta</TableHead>
                                <TableHead>Hesap</TableHead>
                                <TableHead>Tip</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Son Giriş</TableHead>
                                <TableHead>Kayıt Tarihi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users.data" :key="user.id">
                                <TableCell class="font-medium">
                                    {{ user.name }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ user.email }}
                                </TableCell>
                                <TableCell>
                                    <div v-if="user.tenants.length > 0" class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="tenant in user.tenants"
                                            :key="tenant.id"
                                            variant="outline"
                                            class="text-xs"
                                        >
                                            {{ tenant.name }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary" class="text-xs">
                                        {{ user.user_type_label }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="userStatusBadgeVariant(user.status_badge)" class="text-xs">
                                        {{ user.status_label }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ user.last_login_at ? formatDateTime(user.last_login_at) : '—' }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(user.created_at) }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <EmptyState v-else :icon="Users" message="Kullanıcı bulunamadı" />
                </CardContent>
            </Card>

            <SimplePagination :data="users" label="kullanıcı" />
        </div>
    </PanelLayout>
</template>
