<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle,
    CreditCard,
    FileText,
    Hash,
    Receipt,
} from 'lucide-vue-next';
import { ref } from 'vue';
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
import { Separator } from '@/components/ui/separator';
import { formatCurrency, formatDate, formatDateTime } from '@/composables/useFormatting';
import { usePaymentStatus } from '@/composables/usePaymentStatus';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, updateStatus, markAsInvoiced } from '@/routes/panel/payments';
import type { BreadcrumbItem } from '@/types';
import type { Payment, Subscription, PlanPrice, Plan, Addon, Feature } from '@/types/billing';
import type { StatusOption } from '@/types/panel';

type Props = {
    payment: Payment & {
        status_label?: string;
        status_badge?: string;
        tenant?: { id: string; name: string; code: string; slug: string };
        subscription?: Subscription & {
            price?: PlanPrice & { plan?: Plan };
        };
        addon?: Addon & { feature?: Feature };
    };
    statuses: StatusOption[];
};

const props = defineProps<Props>();
const { statusLabel } = usePaymentStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ödemeler', href: index().url },
    { title: `#${props.payment.id.slice(-8)}`, href: '#' },
];

const selectedStatus = ref(props.payment.status);

function handleStatusUpdate() {
    router.put(updateStatus(props.payment.id).url, {
        status: selectedStatus.value,
    }, { preserveScroll: true });
}

function handleMarkInvoiced() {
    router.post(markAsInvoiced(props.payment.id).url, {}, { preserveScroll: true });
}

function payBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'completed': return 'default';
        case 'pending':
        case 'processing': return 'secondary';
        case 'failed':
        case 'cancelled': return 'destructive';
        case 'refunded': return 'outline';
        default: return 'outline';
    }
}
</script>

<template>
    <Head title="Ödeme Detay" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="index().url">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold">Ödeme Detay</h1>
                    <p v-if="payment.tenant" class="text-sm text-muted-foreground">
                        {{ payment.tenant.name }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left: Payment Info -->
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle class="text-sm font-medium">Ödeme Bilgileri</CardTitle>
                            <Badge :variant="payBadgeVariant(payment.status)">
                                {{ payment.status_label ?? statusLabel(payment.status) }}
                            </Badge>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-muted-foreground">Ödeme ID</dt>
                                    <dd class="font-mono text-xs font-medium">{{ payment.id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted-foreground">Tutar</dt>
                                    <dd class="text-lg font-bold">{{ formatCurrency(payment.amount) }}</dd>
                                </div>
                                <div v-if="payment.tenant">
                                    <dt class="text-muted-foreground">Müşteri</dt>
                                    <dd class="font-medium">{{ payment.tenant.name }}</dd>
                                </div>
                                <div v-if="payment.gateway">
                                    <dt class="text-muted-foreground">Ödeme Yöntemi</dt>
                                    <dd class="font-medium capitalize">{{ payment.gateway }}</dd>
                                </div>
                                <div v-if="payment.gateway_payment_id">
                                    <dt class="text-muted-foreground">Gateway ID</dt>
                                    <dd class="font-mono text-xs font-medium">{{ payment.gateway_payment_id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted-foreground">Para Birimi</dt>
                                    <dd class="font-medium">{{ payment.currency }}</dd>
                                </div>
                                <div v-if="payment.description" class="sm:col-span-2">
                                    <dt class="text-muted-foreground">Açıklama</dt>
                                    <dd class="font-medium">{{ payment.description }}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <!-- Related Subscription -->
                    <Card v-if="payment.subscription">
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">İlişkili Abonelik</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                <div v-if="payment.subscription.price?.plan">
                                    <dt class="text-muted-foreground">Plan</dt>
                                    <dd class="font-medium">{{ payment.subscription.price.plan.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted-foreground">Abonelik Durumu</dt>
                                    <dd class="font-medium capitalize">{{ payment.subscription.status }}</dd>
                                </div>
                                <div v-if="payment.subscription.starts_at">
                                    <dt class="text-muted-foreground">Başlangıç</dt>
                                    <dd class="font-medium">{{ formatDate(payment.subscription.starts_at) }}</dd>
                                </div>
                                <div v-if="payment.subscription.ends_at">
                                    <dt class="text-muted-foreground">Bitiş</dt>
                                    <dd class="font-medium">{{ formatDate(payment.subscription.ends_at) }}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <!-- Related Addon -->
                    <Card v-if="payment.addon">
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">İlişkili Eklenti</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-muted-foreground">Eklenti</dt>
                                    <dd class="font-medium">{{ payment.addon.name }}</dd>
                                </div>
                                <div v-if="payment.addon.feature">
                                    <dt class="text-muted-foreground">Özellik</dt>
                                    <dd class="font-medium">{{ payment.addon.feature.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted-foreground">Tip</dt>
                                    <dd class="font-medium capitalize">{{ payment.addon.addon_type }}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <!-- Dates -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Tarihler</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-muted-foreground">Oluşturulma</dt>
                                    <dd class="font-medium">{{ formatDateTime(payment.created_at) }}</dd>
                                </div>
                                <div v-if="payment.paid_at">
                                    <dt class="text-muted-foreground">Ödeme Tarihi</dt>
                                    <dd class="font-medium">{{ formatDateTime(payment.paid_at) }}</dd>
                                </div>
                                <div v-if="payment.refunded_at">
                                    <dt class="text-muted-foreground">İade Tarihi</dt>
                                    <dd class="font-medium">{{ formatDateTime(payment.refunded_at) }}</dd>
                                </div>
                                <div v-if="payment.invoiced_at">
                                    <dt class="text-muted-foreground">Faturalama Tarihi</dt>
                                    <dd class="font-medium">{{ formatDateTime(payment.invoiced_at) }}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right: Actions -->
                <div class="space-y-6">
                    <!-- Status Update -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Durum Güncelle</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Select v-model="selectedStatus">
                                <SelectTrigger>
                                    <SelectValue placeholder="Durum seçin" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="s in statuses"
                                        :key="String(s.value)"
                                        :value="String(s.value)"
                                    >
                                        {{ s.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button
                                class="w-full"
                                size="sm"
                                :disabled="selectedStatus === payment.status"
                                @click="handleStatusUpdate"
                            >
                                Durumu Güncelle
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Invoice Action -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Faturalama</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="payment.invoiced_at" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                <CheckCircle class="h-4 w-4" />
                                <span>Faturalandı — {{ formatDate(payment.invoiced_at) }}</span>
                            </div>
                            <div v-else class="space-y-2">
                                <p class="text-sm text-muted-foreground">
                                    Bu ödeme henüz faturalanmamış.
                                </p>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    :disabled="payment.status !== 'completed'"
                                    @click="handleMarkInvoiced"
                                >
                                    <FileText class="mr-1.5 h-4 w-4" />
                                    Faturala
                                </Button>
                                <p v-if="payment.status !== 'completed'" class="text-xs text-muted-foreground">
                                    Sadece tamamlanmış ödemeler faturalanabilir.
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Özet</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Tutar</span>
                                <span class="font-bold">{{ formatCurrency(payment.amount) }}</span>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Durum</span>
                                <Badge :variant="payBadgeVariant(payment.status)" class="text-xs">
                                    {{ payment.status_label ?? statusLabel(payment.status) }}
                                </Badge>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Fatura</span>
                                <span>{{ payment.invoiced_at ? 'Evet' : 'Hayır' }}</span>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Tarih</span>
                                <span>{{ formatDate(payment.created_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
