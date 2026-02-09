<script setup lang="ts">
import Card from 'primevue/card';
import AppLayout from '@/layouts/App.vue';
import { formatCurrency } from '@/composables/useFormatting';

defineProps<{
    checkout: {
        id: string;
        merchant_oid: string;
        final_amount: number;
        currency: string;
    };
    planPrice: {
        plan: { name: string };
    };
    iframeUrl: string;
}>();
</script>

<template>
    <AppLayout title="Ödeme İşlemi">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <div class="text-center">
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Ödeme İşlemi</h2>
                <p class="mt-1 text-sm text-surface-500 dark:text-surface-400">
                    {{ planPrice.plan.name }} — {{ formatCurrency(checkout.final_amount, checkout.currency) }}
                </p>
            </div>

            <Card>
                <template #content>
                    <iframe
                        :src="iframeUrl"
                        frameborder="0"
                        class="h-[500px] w-full rounded-lg"
                        allowfullscreen
                    />
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
