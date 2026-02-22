<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { formatDate } from '@/composables/useFormatting';
import { useUserStatus } from '@/composables/useUserStatus';
import { useTenantTabs } from '@/composables/useTenantTabs';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index } from '@/routes/panel/tenants';
import { index as usersIndex, updateStatus } from '@/routes/panel/tenants/users';
import type { BreadcrumbItem } from '@/types';
import type { TenantUser, Tenant } from '@/types/tenant';
import type { StatusOption } from '@/types/panel';

type Props = {
    tenant: Tenant;
    users: TenantUser[];
    statistics: {
        total: number;
        active: number;
        passive: number;
        draft: number;
    };
    statusOptions: StatusOption[];
};

const props = defineProps<Props>();
const { statusLabel, roleLabel, statusColor } = useUserStatus();
const tabs = useTenantTabs(props.tenant.id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: `/panel/tenants/${props.tenant.id}` },
    { title: 'Kullanıcılar', href: usersIndex(props.tenant.id).url },
];

function handleStatusChange(user: TenantUser, newStatus: string) {
    router.put(updateStatus({ tenant: props.tenant.id, user: user.id }).url, {
        status: Number(newStatus),
    }, { preserveScroll: true });
}

function statusBadgeVariant(status: number): 'default' | 'secondary' | 'outline' {
    switch (status) {
        case 1: return 'default';
        case 0: return 'outline';
        case 2: return 'secondary';
        default: return 'secondary';
    }
}

function roleBadgeVariant(role: number): 'default' | 'secondary' {
    return role === 1 ? 'default' : 'secondary';
}
</script>

<template>
    <Head title="Kullanıcılar" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-lg font-semibold">{{ tenant.name }}</h1>
                <p class="text-sm text-muted-foreground">Kullanıcı yönetimi</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 overflow-x-auto border-b">
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="tab.href === usersIndex(tenant.id).url
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted-foreground/30 hover:text-foreground'"
                >
                    {{ tab.title }}
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 sm:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Toplam</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Aktif</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ statistics.active }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Pasif</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.passive }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Taslak</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.draft }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Users Table -->
            <Card>
                <CardContent class="p-0">
                    <Table v-if="users.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Kullanıcı</TableHead>
                                <TableHead>Rol</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Katılım Tarihi</TableHead>
                                <TableHead class="text-right">Durum Değiştir</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users" :key="user.id">
                                <TableCell>
                                    <div>
                                        <p class="font-medium">{{ user.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="roleBadgeVariant(user.role)">
                                        {{ roleLabel(user.role) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="statusBadgeVariant(user.status)">
                                        {{ statusLabel(user.status) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(user.created_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <Select
                                        :model-value="String(user.status)"
                                        @update:model-value="(val: string) => handleStatusChange(user, val)"
                                    >
                                        <SelectTrigger class="w-28">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="opt in statusOptions"
                                                :key="opt.value"
                                                :value="String(opt.value)"
                                            >
                                                {{ opt.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <Users class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">Kullanıcı bulunamadı</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </PanelLayout>
</template>
