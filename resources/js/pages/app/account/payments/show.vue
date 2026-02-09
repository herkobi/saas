<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';
import { formatCurrency, formatDateTime } from '@/composables/useFormatting';

interface PaymentDetail {
    id: string;
    amount: number;
    currency: string;
    status: string;
    status_label: string;
    status_badge: string;
    type_label: string;
    description: string;
    merchant_oid: string | null;
    paid_at: string | null;
    created_at: string;
}

defineProps<{
    payment: PaymentDetail;
}>();

</script>

<template>
    <AppLayout title="Ödeme Detayı">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/app/account/payments">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Ödeme Detayı</h2>
            </div>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Durum</span>
                            <Tag :value="payment.status_label" :severity="payment.status_badge as any" />
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Açıklama</span>
                            <span class="text-sm font-medium text-surface-900 dark:text-surface-0">{{ payment.description }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Tür</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ payment.type_label }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Tutar</span>
                            <span class="text-lg font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(payment.amount, payment.currency) }}</span>
                        </div>
                        <div v-if="payment.merchant_oid" class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">İşlem No</span>
                            <code class="text-xs text-surface-600 dark:text-surface-400">{{ payment.merchant_oid }}</code>
                        </div>
                        <div v-if="payment.paid_at" class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Ödeme Tarihi</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDateTime(payment.paid_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Oluşturulma</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDateTime(payment.created_at) }}</span>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
