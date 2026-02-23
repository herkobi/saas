<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    Calendar,
    CreditCard,
    Receipt,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatCurrency, formatDate, formatDateTime } from '@/composables/useFormatting';
import { usePaymentStatus } from '@/composables/usePaymentStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as paymentsIndex } from '@/routes/app/account/payments';
import type { BreadcrumbItem } from '@/types';

type PaymentDetail = {
    id: string;
    amount: number;
    currency: string;
    status: string;
    status_label: string;
    status_badge: string;
    gateway: string | null;
    gateway_payment_id: string | null;
    description: string | null;
    paid_at: string | null;
    refunded_at: string | null;
    invoiced_at: string | null;
    created_at: string;
    subscription?: {
        id: string;
        price: {
            plan: { name: string };
            price: number;
            currency: string;
            interval: string;
        };
    } | null;
    addon?: {
        id: string;
        name: string;
        feature: { name: string } | null;
    } | null;
};

const props = defineProps<{
    payment: PaymentDetail;
}>();

const { getStatusVariant } = usePaymentStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Ödemeler', href: paymentsIndex().url },
    { title: 'Ödeme Detayı', href: '#' },
];
</script>

<template>
    <Head title="Ödeme Detayı" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <!-- Payment Info -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="flex items-center gap-2">
                                    <CreditCard class="h-5 w-5 text-primary" />
                                    Ödeme Bilgileri
                                </CardTitle>
                                <Badge :variant="getStatusVariant(payment.status)">
                                    {{ payment.status_label }}
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Tutar</p>
                                    <p class="text-2xl font-bold">{{ formatCurrency(payment.amount, payment.currency) }}</p>
                                </div>
                                <div v-if="payment.description" class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Açıklama</p>
                                    <p class="font-medium">{{ payment.description }}</p>
                                </div>
                                <div v-if="payment.gateway" class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Ödeme Yöntemi</p>
                                    <p class="font-medium">{{ payment.gateway }}</p>
                                </div>
                                <div v-if="payment.gateway_payment_id" class="space-y-1">
                                    <p class="text-sm text-muted-foreground">İşlem No</p>
                                    <p class="font-mono text-sm">{{ payment.gateway_payment_id }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Related Subscription -->
                    <Card v-if="payment.subscription">
                        <CardHeader>
                            <CardTitle class="text-base">İlgili Abonelik</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">{{ payment.subscription.price.plan.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ formatCurrency(payment.subscription.price.price, payment.subscription.price.currency) }}
                                        / {{ payment.subscription.price.interval }}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Related Addon -->
                    <Card v-if="payment.addon">
                        <CardHeader>
                            <CardTitle class="text-base">İlgili Eklenti</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div>
                                <p class="font-medium">{{ payment.addon.name }}</p>
                                <p v-if="payment.addon.feature" class="text-sm text-muted-foreground">
                                    {{ payment.addon.feature.name }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="flex flex-col gap-4">
                    <!-- Dates -->
                    <Card>
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-2">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                                <CardTitle class="text-sm font-medium">Tarihler</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="text-sm space-y-3">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Oluşturulma</span>
                                <span>{{ formatDateTime(payment.created_at) }}</span>
                            </div>
                            <div v-if="payment.paid_at" class="flex justify-between">
                                <span class="text-muted-foreground">Ödeme</span>
                                <span>{{ formatDateTime(payment.paid_at) }}</span>
                            </div>
                            <div v-if="payment.refunded_at" class="flex justify-between">
                                <span class="text-muted-foreground">İade</span>
                                <span class="text-destructive">{{ formatDateTime(payment.refunded_at) }}</span>
                            </div>
                            <div v-if="payment.invoiced_at" class="flex justify-between">
                                <span class="text-muted-foreground">Faturalanma</span>
                                <span>{{ formatDateTime(payment.invoiced_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Summary -->
                    <Card>
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-2">
                                <Receipt class="h-4 w-4 text-muted-foreground" />
                                <CardTitle class="text-sm font-medium">Özet</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="text-sm space-y-2">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Durum</span>
                                <Badge :variant="getStatusVariant(payment.status)" class="text-xs">
                                    {{ payment.status_label }}
                                </Badge>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Para Birimi</span>
                                <span class="font-medium">{{ payment.currency }}</span>
                            </div>
                            <Separator />
                            <div class="flex justify-between font-medium">
                                <span>Toplam</span>
                                <span>{{ formatCurrency(payment.amount, payment.currency) }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
        </AccountLayout>
    </AppLayout>
</template>
