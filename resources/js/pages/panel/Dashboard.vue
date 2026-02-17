<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Building2,
    Clock,
    CreditCard,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
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
import { formatCurrency, formatDate, formatDateTime } from '@/composables/useFormatting';
import AppLayout from '@/layouts/PanelLayout.vue';
import { dashboard } from '@/routes/panel';
import { index as paymentsIndex } from '@/routes/panel/payments';
import { index as tenantsIndex } from '@/routes/panel/tenants';
import type { BreadcrumbItem } from '@/types';
import type {
    ExpiringSubscription,
    FailedPayment,
    PlanDistribution,
} from '@/types/panel';

type Props = {
    totalTenants: number;
    activeTenants: number;
    paymentStats: {
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
    subscriptionStats: {
        total: number;
        active: number;
        trialing: number;
        past_due: number;
        revenue_30d: number;
    };
    recentPayments: {
        data: {
            id: string;
            amount: number;
            status: string;
            status_label: string;
            status_badge?: { variant?: string };
            paid_at: string | null;
            created_at: string;
            tenant: { id: string; name: string };
        }[];
    };
    recentTenants: {
        data: {
            id: string;
            name: string;
            code: string;
            owner_name?: string;
            plan_name?: string;
            subscription_status_label?: string;
            created_at: string;
        }[];
    };
    recentActivities: {
        id: string;
        type: string;
        description: string;
        created_at: string;
        user?: { id: string; name: string };
    }[];
    planDistribution: PlanDistribution[];
    expiringSubscriptions: ExpiringSubscription[];
    failedPayments: FailedPayment[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Başlangıç',
        href: dashboard().url,
    },
];

function paymentStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'completed':
            return 'default';
        case 'pending':
        case 'processing':
            return 'secondary';
        case 'failed':
        case 'cancelled':
            return 'destructive';
        default:
            return 'outline';
    }
}

function daysUntil(dateStr: string): number {
    const now = new Date();
    const target = new Date(dateStr);
    return Math.ceil((target.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
}
</script>

<template>
    <Head title="Başlangıç" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Stat Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam Müşteri</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalTenants }}</div>
                        <p class="text-xs text-muted-foreground">{{ activeTenants }} aktif</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Aktif Abonelik</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ subscriptionStats.active }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ subscriptionStats.trialing }} deneme, {{ subscriptionStats.past_due }} gecikmiş
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Son 30 Gün</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(subscriptionStats.revenue_30d) }}</div>
                        <p class="text-xs text-muted-foreground">{{ paymentStats.completed_count }} ödeme</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam Gelir</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(paymentStats.total_revenue) }}</div>
                        <p class="text-xs text-muted-foreground">{{ paymentStats.pending_count }} bekleyen</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Expiring Subscriptions Alert -->
            <Card v-if="expiringSubscriptions.length > 0" class="border-amber-200 dark:border-amber-900/50">
                <CardHeader class="pb-3">
                    <div class="flex items-center gap-2">
                        <Clock class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                        <CardTitle class="text-sm font-medium">Süresi Dolan Abonelikler</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                        <div
                            v-for="sub in expiringSubscriptions"
                            :key="sub.id"
                            class="flex items-center gap-2 text-sm"
                        >
                            <span class="font-medium">{{ sub.tenant.name }}</span>
                            <Badge variant="outline" class="text-amber-700 dark:text-amber-400">
                                {{ daysUntil(sub.ends_at) }} gün
                            </Badge>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Two Column Layout: Left (tables) / Right (activities) -->
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Left: Tables -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <!-- Son Ödemeler -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm font-medium">Son Ödemeler</CardTitle>
                            <Link
                                :href="paymentsIndex().url"
                                class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                            >
                                Tümü
                                <ArrowRight class="h-3 w-3" />
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <Table v-if="recentPayments.data.length > 0 || failedPayments.length > 0">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Müşteri</TableHead>
                                        <TableHead>Durum</TableHead>
                                        <TableHead class="text-right">Tutar</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="payment in recentPayments.data" :key="payment.id">
                                        <TableCell>
                                            <p class="font-medium">{{ payment.tenant.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ formatDate(payment.created_at) }}</p>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="paymentStatusVariant(payment.status)">
                                                {{ payment.status_label }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right font-medium">
                                            {{ formatCurrency(payment.amount) }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                            <p v-else class="text-sm text-muted-foreground">Henüz ödeme bulunmuyor.</p>
                        </CardContent>
                    </Card>

                    <!-- Son Müşteriler -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm font-medium">Son Müşteriler</CardTitle>
                            <Link
                                :href="tenantsIndex().url"
                                class="inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
                            >
                                Tümü
                                <ArrowRight class="h-3 w-3" />
                            </Link>
                        </CardHeader>
                        <CardContent>
                            <Table v-if="recentTenants.data.length > 0">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Müşteri</TableHead>
                                        <TableHead>Plan</TableHead>
                                        <TableHead class="text-right">Tarih</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="tenant in recentTenants.data" :key="tenant.id">
                                        <TableCell>
                                            <p class="font-medium">{{ tenant.name }}</p>
                                            <p v-if="tenant.owner_name" class="text-xs text-muted-foreground">
                                                {{ tenant.owner_name }}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            <Badge v-if="tenant.plan_name" variant="secondary">
                                                {{ tenant.plan_name }}
                                            </Badge>
                                            <span v-else class="text-xs text-muted-foreground">—</span>
                                        </TableCell>
                                        <TableCell class="text-right text-sm text-muted-foreground">
                                            {{ formatDate(tenant.created_at) }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                            <p v-else class="text-sm text-muted-foreground">Henüz müşteri bulunmuyor.</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right: Activities -->
                <Card class="h-fit lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Son Aktiviteler</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentActivities.length > 0" class="space-y-4">
                            <div
                                v-for="activity in recentActivities.slice(0, 10)"
                                :key="activity.id"
                                class="flex flex-col gap-0.5 text-sm"
                            >
                                <p class="leading-snug">{{ activity.description }}</p>
                                <p class="text-xs text-muted-foreground">
                                    <span v-if="activity.user">{{ activity.user.name }} &middot; </span>
                                    {{ formatDateTime(activity.created_at) }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Henüz aktivite bulunmuyor.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
