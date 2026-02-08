<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';

const props = defineProps<{
    payment: any;
    statuses: any[];
}>();

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <PanelLayout title="Ödeme Detayı">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/panel/payments">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Ödeme Detayı</h2>
            </div>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Hesap</span>
                            <Link v-if="payment.tenant" :href="`/panel/tenants/${payment.tenant.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                {{ payment.tenant.name }}
                            </Link>
                            <span v-else class="text-sm text-surface-500">-</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Açıklama</span>
                            <span class="text-sm font-medium text-surface-900 dark:text-surface-0">{{ payment.description ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Tutar</span>
                            <span class="text-lg font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(payment.amount, payment.currency) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Durum</span>
                            <Tag :value="payment.status_label" :severity="(payment.status_badge as any) ?? 'info'" />
                        </div>
                        <div v-if="payment.type_label" class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Tip</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ payment.type_label }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Ödeme Tarihi</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDate(payment.paid_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Oluşturulma</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDate(payment.created_at) }}</span>
                        </div>
                        <div v-if="payment.is_invoiced !== undefined" class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Fatura</span>
                            <Tag :value="payment.is_invoiced ? 'Faturalandı' : 'Faturalanmadı'" :severity="payment.is_invoiced ? 'success' : 'secondary'" />
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
