<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import AppLayout from '@/layouts/App.vue';

interface CheckoutPlanPrice {
    id: string;
    price: number;
    currency: string;
    interval: string;
    interval_count: number;
    plan: { id: string; name: string; description?: string };
}

const props = defineProps<{
    planPrice: CheckoutPlanPrice;
    type: string;
    amounts: {
        base_amount: number;
        tax_amount: number;
        total_amount: number;
        credit_amount?: number;
    };
    billingInfo: Record<string, any>;
}>();

const form = useForm({});

const submit = () => {
    form.post('/app/account/checkout');
};

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};
</script>

<template>
    <AppLayout title="Ödeme">
        <div class="mx-auto max-w-lg flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Ödeme Özeti</h2>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Plan</span>
                            <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">{{ planPrice.plan.name }}</span>
                        </div>
                        <p v-if="planPrice.plan.description" class="text-xs text-surface-400">{{ planPrice.plan.description }}</p>

                        <hr class="border-surface-200 dark:border-surface-700" />

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Ara Toplam</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatCurrency(amounts.base_amount, planPrice.currency) }}</span>
                        </div>
                        <div v-if="amounts.credit_amount" class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Kredi</span>
                            <span class="text-sm text-green-600">-{{ formatCurrency(amounts.credit_amount, planPrice.currency) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">KDV (%20)</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatCurrency(amounts.tax_amount, planPrice.currency) }}</span>
                        </div>

                        <hr class="border-surface-200 dark:border-surface-700" />

                        <div class="flex items-center justify-between">
                            <span class="text-base font-semibold text-surface-900 dark:text-surface-0">Toplam</span>
                            <span class="text-lg font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(amounts.total_amount, planPrice.currency) }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <Button label="Ödemeye Geç" icon="pi pi-credit-card" :loading="form.processing" fluid @click="submit" />
        </div>
    </AppLayout>
</template>
