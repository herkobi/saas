<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, TrendingUp, Clock, AlertTriangle } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { useTenantTabs } from '@/composables/useTenantTabs';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index } from '@/routes/panel/tenants';
import { index as paymentsIndex } from '@/routes/panel/tenants/payments';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { Payment } from '@/types/billing';
import type { Tenant } from '@/types/tenant';

type Props = {
    tenant: Tenant;
    payments: PaginatedData<Payment & { status_label?: string; status_badge?: { variant?: string } }>;
    statistics: {
        total_count: number;
        completed_count: number;
        pending_count: number;
        failed_count: number;
        total_revenue: number;
    };
};

const props = defineProps<Props>();
const { statusLabel, statusColor } = usePaymentStatus();
const tabs = useTenantTabs(props.tenant.id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: `/panel/tenants/${props.tenant.id}` },
    { title: 'Ödemeler', href: paymentsIndex(props.tenant.id).url },
];

function paymentBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
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
            <div>
                <h1 class="text-lg font-semibold">{{ tenant.name }}</h1>
                <p class="text-sm text-muted-foreground">Ödeme geçmişi</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 overflow-x-auto border-b">
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="tab.href === paymentsIndex(tenant.id).url
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted-foreground/30 hover:text-foreground'"
                >
                    {{ tab.title }}
                </Link>
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
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Tamamlanan</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.completed_count }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Bekleyen</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.pending_count }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Başarısız</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.failed_count }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Payments Table -->
            <Card>
                <CardContent class="p-0">
                    <Table v-if="payments.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tutar</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Gateway</TableHead>
                                <TableHead>Ödeme Tarihi</TableHead>
                                <TableHead>Oluşturulma</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in payments.data" :key="payment.id">
                                <TableCell class="font-medium">
                                    {{ formatCurrency(payment.amount) }}
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="paymentBadgeVariant(payment.status)">
                                        {{ payment.status_label ?? statusLabel(payment.status) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ payment.gateway ?? '—' }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ payment.paid_at ? formatDate(payment.paid_at) : '—' }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(payment.created_at) }}
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
