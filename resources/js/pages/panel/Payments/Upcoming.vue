<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarClock,
    Clock,
    CreditCard,
    Eye,
    Search,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
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
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import { usePaymentStatus } from '@/composables/usePaymentStatus';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, show, upcoming } from '@/routes/panel/payments';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { PaymentListItem } from '@/types/panel';

type UpcomingPayment = PaymentListItem & {
    subscription_ends_at?: string;
};

type Props = {
    payments: PaginatedData<UpcomingPayment>;
    statistics: {
        total_count: number;
        total_amount: number;
        next_7_days: number;
        next_14_days: number;
    };
    filters: {
        search?: string;
    };
};

const props = defineProps<Props>();
const { statusLabel } = usePaymentStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ödemeler', href: index().url },
    { title: 'Yaklaşan Ödemeler', href: upcoming().url },
];

const search = ref(props.filters.search ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const params: Record<string, string> = {};
        if (val) params.search = val;
        router.get(upcoming().url, params, { preserveState: true, replace: true });
    }, 300);
});

function daysUntil(dateStr?: string): number | null {
    if (!dateStr) return null;
    const diff = new Date(dateStr).getTime() - Date.now();
    return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
}

function urgencyVariant(days: number | null): 'destructive' | 'secondary' | 'outline' {
    if (days === null) return 'outline';
    if (days <= 7) return 'destructive';
    if (days <= 14) return 'secondary';
    return 'outline';
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
    <Head title="Yaklaşan Ödemeler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="index().url">
                        <ArrowLeft class="h-4 w-4" />
                        <span class="hidden sm:inline">Geri</span>
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold">Yaklaşan Ödemeler</h1>
                    <p class="text-sm text-muted-foreground">30 gün içinde sona erecek aboneliklere ait ödemeler</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam Yaklaşan</CardTitle>
                        <CalendarClock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.total_count }}</div>
                        <p class="text-xs text-muted-foreground">ödeme</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Beklenen Tutar</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.total_amount) }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">7 Gün İçinde</CardTitle>
                        <Clock class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ statistics.next_7_days }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">14 Gün İçinde</CardTitle>
                        <Clock class="h-4 w-4 text-amber-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ statistics.next_14_days }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filter -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative w-64">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Ödeme veya müşteri ara..." class="pl-9" />
                </div>
            </div>

            <!-- Table -->
            <Card>
                <CardContent class="p-0">
                    <Table v-if="payments.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Müşteri</TableHead>
                                <TableHead>Tutar</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Bitiş Tarihi</TableHead>
                                <TableHead>Kalan Gün</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in payments.data" :key="payment.id">
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
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ payment.subscription_ends_at ? formatDate(payment.subscription_ends_at) : '—' }}
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="urgencyVariant(daysUntil(payment.subscription_ends_at))">
                                        {{ daysUntil(payment.subscription_ends_at) !== null ? `${daysUntil(payment.subscription_ends_at)} gün` : '—' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="show(payment.id).url">
                                            <Eye class="h-4 w-4" />
                                            <span class="hidden sm:inline">Görüntüle</span>
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <EmptyState v-else :icon="CalendarClock" message="Yaklaşan ödeme bulunamadı" />
                </CardContent>
            </Card>

            <SimplePagination :data="payments" label="ödeme" />
        </div>
    </PanelLayout>
</template>
