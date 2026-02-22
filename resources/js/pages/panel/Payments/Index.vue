<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle,
    Clock,
    CreditCard,
    Eye,
    FileText,
    Search,
    TrendingUp,
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
import { Checkbox } from '@/components/ui/checkbox';
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
import { usePaymentStatus } from '@/composables/usePaymentStatus';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, show, markAsInvoiced, markManyAsInvoiced } from '@/routes/panel/payments';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { PaymentListItem, StatusOption } from '@/types/panel';

type Props = {
    payments: PaginatedData<PaymentListItem>;
    statistics: {
        total_count: number;
        completed_count: number;
        pending_count: number;
        failed_count: number;
        refunded_count: number;
        refunded_amount: number;
        total_revenue: number;
        pending_revenue: number;
        uninvoiced_count: number;
        uninvoiced_amount: number;
    };
    filters: {
        search?: string;
        status?: string;
        invoiced?: string;
        date_from?: string;
        date_to?: string;
    };
    statuses: StatusOption[];
};

const props = defineProps<Props>();
const { statusLabel } = usePaymentStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ödemeler', href: index().url },
];

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const invoicedFilter = ref(props.filters.invoiced ?? '');
const selectedIds = ref<string[]>([]);

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
    const inv = override.invoiced !== undefined ? override.invoiced : invoicedFilter.value;

    if (s) params.search = s;
    if (st) params.status = st;
    if (inv) params.invoiced = inv;

    router.get(index().url, params, { preserveState: true, replace: true });
}

function filterByStatus(val: string) {
    statusFilter.value = val;
    applyFilters({ status: val || undefined });
}

function filterByInvoiced(val: string) {
    invoicedFilter.value = val;
    applyFilters({ invoiced: val || undefined });
}

function toggleSelect(id: string) {
    const idx = selectedIds.value.indexOf(id);
    if (idx >= 0) {
        selectedIds.value.splice(idx, 1);
    } else {
        selectedIds.value.push(id);
    }
}

function handleMarkInvoiced(paymentId: string) {
    router.post(markAsInvoiced(paymentId).url, {}, { preserveScroll: true });
}

function handleBulkInvoice() {
    if (selectedIds.value.length === 0) return;
    router.post(markManyAsInvoiced().url, {
        payment_ids: selectedIds.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = []; },
    });
}

function payBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'completed': return 'default';
        case 'pending':
        case 'processing': return 'secondary';
        case 'failed':
        case 'cancelled': return 'destructive';
        default: return 'outline';
    }
}
</script>

<template>
    <Head title="Ödemeler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Ödemeler</h1>
                    <p class="text-sm text-muted-foreground">Tüm ödemeleri görüntüleyin ve yönetin</p>
                </div>
                <Button
                    v-if="selectedIds.length > 0"
                    size="sm"
                    @click="handleBulkInvoice"
                >
                    <FileText class="mr-1.5 h-4 w-4" />
                    Seçilenleri Faturala ({{ selectedIds.length }})
                </Button>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam Gelir</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.total_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">{{ statistics.completed_count }} tamamlanan</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Bekleyen</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.pending_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">{{ statistics.pending_count }} ödeme</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Başarısız</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ statistics.failed_count }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Faturalanmamış</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.uninvoiced_count }}</div>
                        <p class="text-xs text-muted-foreground">{{ formatCurrency(statistics.uninvoiced_amount) }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-64">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Ödeme veya müşteri ara..." class="pl-9" />
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
                <Select :model-value="invoicedFilter" @update:model-value="filterByInvoiced">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Faturalama" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Tümü</SelectItem>
                        <SelectItem value="1">Faturalanmış</SelectItem>
                        <SelectItem value="0">Faturalanmamış</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Table -->
            <Card>
                <CardContent class="p-0">
                    <Table v-if="payments.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-10"></TableHead>
                                <TableHead>Müşteri</TableHead>
                                <TableHead>Tutar</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Fatura</TableHead>
                                <TableHead>Tarih</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in payments.data" :key="payment.id">
                                <TableCell>
                                    <Checkbox
                                        :checked="selectedIds.includes(payment.id)"
                                        @update:checked="toggleSelect(payment.id)"
                                    />
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ payment.tenant_name ?? '—' }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ formatCurrency(payment.amount) }}
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="payBadgeVariant(payment.status)">
                                        {{ payment.status_label }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge v-if="payment.invoiced_at" variant="outline" class="text-green-600 dark:text-green-400">
                                        <CheckCircle class="mr-1 h-3 w-3" />
                                        Evet
                                    </Badge>
                                    <Button
                                        v-else
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 px-2 text-xs"
                                        @click="handleMarkInvoiced(payment.id)"
                                    >
                                        Faturala
                                    </Button>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(payment.created_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="show(payment.id).url">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <CreditCard class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">Ödeme bulunamadı</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="payments.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    {{ payments.from }}–{{ payments.to }} / {{ payments.total }} ödeme
                </p>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="!payments.links.prev" as-child>
                        <Link v-if="payments.links.prev" :href="payments.links.prev">Önceki</Link>
                        <span v-else>Önceki</span>
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!payments.links.next" as-child>
                        <Link v-if="payments.links.next" :href="payments.links.next">Sonraki</Link>
                        <span v-else>Sonraki</span>
                    </Button>
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
