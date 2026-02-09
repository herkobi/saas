<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Message from 'primevue/message';
import ProgressBar from 'primevue/progressbar';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';

interface SubscriptionDetail {
    id: string;
    status: { label: string; badge: string };
    plan: { id: string; name: string; description?: string };
    price: { amount: number; currency: string; interval: string; interval_label: string };
    starts_at: string;
    ends_at: string | null;
    trial_ends_at: string | null;
    grace_ends_at: string | null;
}

interface FeatureDetail {
    name: string;
    unit: string | null;
    usage: {
        type: 'boolean' | 'numeric';
        enabled: boolean;
        is_unlimited: boolean;
        limit: number;
        used: number;
        remaining: number;
        percentage: number;
    };
}

interface AvailablePlan {
    id: string;
    name: string;
    description?: string;
    prices: Array<{
        id: string;
        price: number;
        currency: string;
        interval: string;
        interval_label: string;
    }>;
}

const props = defineProps<{
    subscription: SubscriptionDetail | null;
    features: FeatureDetail[];
    availablePlans: AvailablePlan[];
    canUpgrade: boolean;
    canDowngrade: boolean;
}>();

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppLayout title="Abonelik">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Abonelik</h2>

            <Message v-if="!subscription" severity="warn" :closable="false">
                Aktif bir aboneliğiniz bulunmuyor.
            </Message>

            <!-- Current Subscription -->
            <Card v-if="subscription">
                <template #title><span class="text-base font-semibold">Mevcut Plan</span></template>
                <template #content>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold text-surface-900 dark:text-surface-0">{{ subscription.plan.name }}</span>
                                <Tag :value="subscription.status.label" :severity="subscription.status.badge as any" />
                            </div>
                            <span class="text-sm text-surface-500">
                                {{ formatCurrency(subscription.price.amount, subscription.price.currency) }} / {{ subscription.price.interval_label }}
                            </span>
                            <span v-if="subscription.ends_at" class="text-xs text-surface-400">Bitiş: {{ formatDate(subscription.ends_at) }}</span>
                            <span v-if="subscription.trial_ends_at" class="text-xs text-surface-400">Deneme bitiş: {{ formatDate(subscription.trial_ends_at) }}</span>
                        </div>
                        <div v-if="canUpgrade || canDowngrade" class="flex gap-2">
                            <Link href="/app/account/plan-change">
                                <Button label="Plan Değiştir" icon="pi pi-arrow-right-arrow-left" outlined size="small" />
                            </Link>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Features -->
            <Card v-if="features.length > 0">
                <template #title><span class="text-base font-semibold">Özellikler</span></template>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div v-for="feature in features" :key="feature.name" class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-surface-700 dark:text-surface-300">{{ feature.name }}</span>
                                <span v-if="feature.usage.type === 'boolean'">
                                    <Tag :value="feature.usage.enabled ? 'Aktif' : 'Pasif'" :severity="feature.usage.enabled ? 'success' : 'secondary'" />
                                </span>
                                <span v-else-if="feature.usage.is_unlimited" class="text-xs text-surface-500">Limitsiz</span>
                                <span v-else class="text-xs text-surface-500">{{ feature.usage.used }} / {{ feature.usage.limit }} {{ feature.unit ?? '' }}</span>
                            </div>
                            <ProgressBar v-if="feature.usage.type === 'numeric' && !feature.usage.is_unlimited" :value="feature.usage.percentage" :showValue="false" style="height: 6px" />
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
