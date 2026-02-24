<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
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
import { useSubscriptionStatus } from '@/composables/useSubscriptionStatus';
import { usePaymentStatus } from '@/composables/usePaymentStatus';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index } from '@/routes/panel/subscriptions';
import type { BreadcrumbItem } from '@/types';
import type { Subscription, PlanPrice, Payment } from '@/types/billing';

type Props = {
    subscription: Subscription & {
        plan_price?: PlanPrice & { plan?: { id: string; name: string } };
        next_plan_price?: PlanPrice & { plan?: { id: string; name: string } };
        tenant?: { id: string; name: string };
    };
    payments: Payment[];
};

const props = defineProps<Props>();
const { statusLabel: subStatusLabel } = useSubscriptionStatus();
const { statusLabel: payStatusLabel } = usePaymentStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Abonelikler', href: index().url },
    { title: props.subscription.plan_price?.plan?.name ?? 'Detay', href: '#' },
];

function subBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active': return 'default';
        case 'trialing': return 'secondary';
        case 'past_due': return 'destructive';
        default: return 'outline';
    }
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

function intervalLabel(interval: string, count: number): string {
    const labels: Record<string, string> = { month: 'Aylık', year: 'Yıllık', day: 'Günlük' };
    return count > 1 ? `${count} ${labels[interval] ?? interval}` : labels[interval] ?? interval;
}
</script>

<template>
    <Head title="Abonelik Detay" />

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
                    <h1 class="text-lg font-semibold">Abonelik Detay</h1>
                    <p v-if="subscription.tenant" class="text-sm text-muted-foreground">
                        {{ subscription.tenant.name }}
                    </p>
                </div>
            </div>

            <!-- Subscription Info -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm font-medium">Abonelik Bilgileri</CardTitle>
                    <Badge :variant="subBadgeVariant(subscription.status)">
                        {{ subStatusLabel(subscription.status) }}
                    </Badge>
                </CardHeader>
                <CardContent>
                    <dl class="grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <dt class="text-muted-foreground">Plan</dt>
                            <dd class="font-medium">{{ subscription.plan_price?.plan?.name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Periyot</dt>
                            <dd class="font-medium">
                                {{ intervalLabel(subscription.plan_price?.interval ?? '', subscription.plan_price?.interval_count ?? 1) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Fiyat</dt>
                            <dd class="font-medium">
                                {{ formatCurrency(subscription.custom_price ?? subscription.plan_price?.price ?? 0) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Başlangıç</dt>
                            <dd class="font-medium">{{ formatDate(subscription.starts_at) }}</dd>
                        </div>
                        <div v-if="subscription.ends_at">
                            <dt class="text-muted-foreground">Bitiş</dt>
                            <dd class="font-medium">{{ formatDate(subscription.ends_at) }}</dd>
                        </div>
                        <div v-if="subscription.trial_ends_at">
                            <dt class="text-muted-foreground">Deneme Bitiş</dt>
                            <dd class="font-medium">{{ formatDate(subscription.trial_ends_at) }}</dd>
                        </div>
                        <div v-if="subscription.canceled_at">
                            <dt class="text-muted-foreground">İptal Tarihi</dt>
                            <dd class="font-medium">{{ formatDate(subscription.canceled_at) }}</dd>
                        </div>
                        <div v-if="subscription.grace_period_ends_at">
                            <dt class="text-muted-foreground">Ek Süre Bitiş</dt>
                            <dd class="font-medium">{{ formatDate(subscription.grace_period_ends_at) }}</dd>
                        </div>
                        <div v-if="subscription.next_plan_price">
                            <dt class="text-muted-foreground">Planlanmış Değişiklik</dt>
                            <dd class="font-medium">{{ subscription.next_plan_price.plan?.name ?? '—' }}</dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <!-- Payments -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Ödemeler</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <Table v-if="payments.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tutar</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Ödeme Tarihi</TableHead>
                                <TableHead>Oluşturulma</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in payments" :key="payment.id">
                                <TableCell class="font-medium">{{ formatCurrency(payment.amount) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="payBadgeVariant(payment.status)">
                                        {{ payStatusLabel(payment.status) }}
                                    </Badge>
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
                    <div v-else class="px-6 py-8 text-center">
                        <p class="text-sm text-muted-foreground">Ödeme bulunmuyor</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </PanelLayout>
</template>
