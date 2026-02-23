<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CreditCard, TrendingUp, Clock, AlertTriangle } from 'lucide-vue-next';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
import { Badge } from '@/components/ui/badge';
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
import PanelLayout from '@/layouts/PanelLayout.vue';
import TenantLayout from '@/pages/panel/Tenants/layout/Layout.vue';
import { index, show as tenantShow } from '@/routes/panel/tenants';
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: tenantShow(props.tenant.id).url },
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
        <TenantLayout
            :tenant-id="tenant.id"
            :tenant-name="tenant.name"
            :tenant-code="tenant.code"
            :tenant-slug="tenant.slug"
        >
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

                    <EmptyState v-else :icon="CreditCard" message="Ödeme bulunamadı" />
                </CardContent>
            </Card>

            <SimplePagination :data="payments" label="ödeme" />
        </TenantLayout>
    </PanelLayout>
</template>
