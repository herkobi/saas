<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CreditCard,
    Eye,
    Search,
    TrendingUp,
    Users,
    Clock,
    AlertTriangle,
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
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import { useSubscriptionStatus } from '@/composables/useSubscriptionStatus';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, show } from '@/routes/panel/subscriptions';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { SubscriptionListItem, StatusOption } from '@/types/panel';

type PlanOption = {
    id: string;
    name: string;
    slug: string;
};

type Props = {
    subscriptions: PaginatedData<SubscriptionListItem>;
    statistics: {
        total: number;
        active: number;
        trialing: number;
        past_due: number;
        revenue_30d: number;
    };
    filters: {
        search?: string;
        status?: string;
        plan_id?: string;
    };
    plans: PlanOption[];
    statuses: StatusOption[];
};

const props = defineProps<Props>();
const { statusLabel } = useSubscriptionStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Abonelikler', href: index().url },
];

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const planFilter = ref(props.filters.plan_id ?? '');

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
    const p = override.plan_id !== undefined ? override.plan_id : planFilter.value;

    if (s) params.search = s;
    if (st) params.status = st;
    if (p) params.plan_id = p;

    router.get(index().url, params, { preserveState: true, replace: true });
}

function filterByStatus(val: string) {
    statusFilter.value = val;
    applyFilters({ status: val || undefined });
}

function filterByPlan(val: string) {
    planFilter.value = val;
    applyFilters({ plan_id: val || undefined });
}

function badgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active': return 'default';
        case 'trialing': return 'secondary';
        case 'past_due': return 'destructive';
        case 'canceled':
        case 'expired': return 'outline';
        default: return 'secondary';
    }
}
</script>

<template>
    <Head title="Abonelikler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-lg font-semibold">Abonelikler</h1>
                <p class="text-sm text-muted-foreground">Tüm abonelikleri görüntüleyin</p>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Aktif</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ statistics.active }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Deneme</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.trialing }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Gecikmiş</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ statistics.past_due }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">30 Gün Gelir</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.revenue_30d) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-64">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Müşteri ara..." class="pl-9" />
                </div>
                <Select :model-value="statusFilter" @update:model-value="filterByStatus">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Durum" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Tümü</SelectItem>
                        <SelectItem v-for="s in statuses" :key="String(s.value)" :value="String(s.value)">
                            {{ s.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select :model-value="planFilter" @update:model-value="filterByPlan">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Plan" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Tüm Planlar</SelectItem>
                        <SelectItem v-for="plan in plans" :key="plan.id" :value="plan.id">
                            {{ plan.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Table -->
            <Card>
                <CardContent class="p-0">
                    <Table v-if="subscriptions.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Müşteri</TableHead>
                                <TableHead>Plan</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Başlangıç</TableHead>
                                <TableHead>Bitiş</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="sub in subscriptions.data" :key="sub.id">
                                <TableCell class="font-medium">{{ sub.tenant_name }}</TableCell>
                                <TableCell>
                                    <Badge variant="secondary">{{ sub.plan_name }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="badgeVariant(sub.status)">
                                        {{ sub.status_label }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(sub.starts_at) }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ sub.ends_at ? formatDate(sub.ends_at) : '—' }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="show(sub.id).url">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <CreditCard class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">Abonelik bulunamadı</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="subscriptions.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    {{ subscriptions.from }}–{{ subscriptions.to }} / {{ subscriptions.total }} abonelik
                </p>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="!subscriptions.links.prev" as-child>
                        <Link v-if="subscriptions.links.prev" :href="subscriptions.links.prev">Önceki</Link>
                        <span v-else>Önceki</span>
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!subscriptions.links.next" as-child>
                        <Link v-if="subscriptions.links.next" :href="subscriptions.links.next">Sonraki</Link>
                        <span v-else>Sonraki</span>
                    </Button>
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
