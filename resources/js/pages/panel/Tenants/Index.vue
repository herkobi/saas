<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Eye, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { formatDate } from '@/composables/useFormatting';
import { useSubscriptionStatus } from '@/composables/useSubscriptionStatus';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, show } from '@/routes/panel/tenants';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { TenantListItem } from '@/types/panel';

type PlanOption = {
    id: string;
    name: string;
    slug: string;
};

type Props = {
    tenants: PaginatedData<TenantListItem>;
    plans: PlanOption[];
    filters: {
        search?: string;
        subscription_status?: string;
        plan_id?: string;
        created_from?: string;
        created_to?: string;
    };
};

const props = defineProps<Props>();
const { statusLabel, statusColor } = useSubscriptionStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
];

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.subscription_status ?? '');
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
    const st = override.subscription_status !== undefined ? override.subscription_status : statusFilter.value;
    const p = override.plan_id !== undefined ? override.plan_id : planFilter.value;

    if (s) params.search = s;
    if (st) params.subscription_status = st;
    if (p) params.plan_id = p;

    router.get(index().url, params, { preserveState: true, replace: true });
}

function filterByStatus(val: string) {
    statusFilter.value = val;
    applyFilters({ subscription_status: val || undefined });
}

function filterByPlan(val: string) {
    planFilter.value = val;
    applyFilters({ plan_id: val || undefined });
}

function subscriptionBadgeVariant(status?: string): 'default' | 'secondary' | 'destructive' | 'outline' {
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
    <Head title="Müşteriler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Müşteriler</h1>
                    <p class="text-sm text-muted-foreground">Tüm hesapları görüntüleyin ve yönetin</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-64">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Müşteri ara..."
                        class="pl-9"
                    />
                </div>
                <Select :model-value="statusFilter" @update:model-value="filterByStatus">
                    <SelectTrigger class="w-44">
                        <SelectValue placeholder="Abonelik Durumu" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Tümü</SelectItem>
                        <SelectItem value="active">Aktif</SelectItem>
                        <SelectItem value="trialing">Deneme</SelectItem>
                        <SelectItem value="canceled">İptal</SelectItem>
                        <SelectItem value="past_due">Gecikmiş</SelectItem>
                        <SelectItem value="expired">Süresi Dolmuş</SelectItem>
                    </SelectContent>
                </Select>
                <Select :model-value="planFilter" @update:model-value="filterByPlan">
                    <SelectTrigger class="w-44">
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

            <Card>
                <CardContent class="p-0">
                    <Table v-if="tenants.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Müşteri</TableHead>
                                <TableHead>Abonelik</TableHead>
                                <TableHead>Plan</TableHead>
                                <TableHead>Kayıt Tarihi</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="tenant in tenants.data" :key="tenant.id">
                                <TableCell>
                                    <div>
                                        <p class="font-medium">{{ tenant.name }}</p>
                                        <p v-if="tenant.owner_name" class="text-xs text-muted-foreground">
                                            {{ tenant.owner_name }}
                                        </p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        v-if="tenant.subscription_status_label"
                                        :variant="subscriptionBadgeVariant(tenant.subscription_status)"
                                    >
                                        {{ tenant.subscription_status_label }}
                                    </Badge>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell>
                                    <Badge v-if="tenant.plan_name" variant="secondary">
                                        {{ tenant.plan_name }}
                                    </Badge>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(tenant.created_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="show(tenant.id).url">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <EmptyState v-else :icon="Building2" message="Müşteri bulunamadı" />
                </CardContent>
            </Card>

            <SimplePagination :data="tenants" label="müşteri" />
        </div>
    </PanelLayout>
</template>
