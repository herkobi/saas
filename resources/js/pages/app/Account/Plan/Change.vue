<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';

interface PlanOption {
    id: string;
    price: number;
    currency: string;
    interval: string;
    interval_label: string;
    plan: { id: string; name: string; description?: string };
}

const props = defineProps<{
    currentSubscription: {
        id: string;
        ends_at: string | null;
        price: {
            id: string;
            price: number;
            currency: string;
            interval: string;
            plan: { id: string; name: string };
        };
        next_price: {
            id: string;
            price: number;
            plan: { name: string };
        } | null;
    } | null;
    upgradeOptions: PlanOption[];
    downgradeOptions: PlanOption[];
}>();

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const cancelDowngradeForm = useForm({});
const cancelDowngrade = () => {
    cancelDowngradeForm.post('/app/account/plan-change/downgrade/cancel');
};
</script>

<template>
    <AppLayout title="Plan Değiştir">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Plan Değiştir</h2>

            <Message v-if="!currentSubscription" severity="warn" :closable="false">
                Aktif abonelik bulunamadı.
            </Message>

            <!-- Current Plan -->
            <Card v-if="currentSubscription">
                <template #title><span class="text-base font-semibold">Mevcut Plan</span></template>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-lg font-semibold text-surface-900 dark:text-surface-0">{{ currentSubscription.price.plan.name }}</span>
                            <span class="ml-2 text-sm text-surface-500">{{ formatCurrency(currentSubscription.price.price, currentSubscription.price.currency) }}</span>
                        </div>
                        <Tag value="Mevcut" severity="info" />
                    </div>
                    <div v-if="currentSubscription.next_price" class="mt-3 flex items-center justify-between rounded-lg bg-surface-100 p-3 dark:bg-surface-800">
                        <span class="text-sm text-surface-600 dark:text-surface-400">
                            Planlanan değişiklik: <strong>{{ currentSubscription.next_price.plan.name }}</strong>
                        </span>
                        <Button label="İptal Et" size="small" severity="danger" outlined :loading="cancelDowngradeForm.processing" @click="cancelDowngrade" />
                    </div>
                </template>
            </Card>

            <!-- Upgrade Options -->
            <div v-if="upgradeOptions.length > 0">
                <h3 class="mb-3 text-base font-semibold text-surface-900 dark:text-surface-0">Yükseltme Seçenekleri</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="option in upgradeOptions" :key="option.id">
                        <template #content>
                            <div class="flex flex-col gap-3">
                                <div>
                                    <span class="text-base font-semibold text-surface-900 dark:text-surface-0">{{ option.plan.name }}</span>
                                    <p v-if="option.plan.description" class="mt-1 text-xs text-surface-400">{{ option.plan.description }}</p>
                                </div>
                                <span class="text-lg font-bold text-surface-900 dark:text-surface-0">
                                    {{ formatCurrency(option.price, option.currency) }}
                                    <span class="text-xs font-normal text-surface-400">/ {{ option.interval_label }}</span>
                                </span>
                                <Link :href="`/app/account/checkout?planPriceId=${option.id}&type=upgrade`">
                                    <Button label="Yükselt" icon="pi pi-arrow-up" size="small" fluid />
                                </Link>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Downgrade Options -->
            <div v-if="downgradeOptions.length > 0">
                <h3 class="mb-3 text-base font-semibold text-surface-900 dark:text-surface-0">Düşürme Seçenekleri</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="option in downgradeOptions" :key="option.id">
                        <template #content>
                            <div class="flex flex-col gap-3">
                                <div>
                                    <span class="text-base font-semibold text-surface-900 dark:text-surface-0">{{ option.plan.name }}</span>
                                    <p v-if="option.plan.description" class="mt-1 text-xs text-surface-400">{{ option.plan.description }}</p>
                                </div>
                                <span class="text-lg font-bold text-surface-900 dark:text-surface-0">
                                    {{ formatCurrency(option.price, option.currency) }}
                                    <span class="text-xs font-normal text-surface-400">/ {{ option.interval_label }}</span>
                                </span>
                                <Link :href="`/app/account/checkout?planPriceId=${option.id}&type=downgrade`">
                                    <Button label="Düşür" icon="pi pi-arrow-down" severity="secondary" size="small" outlined fluid />
                                </Link>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
