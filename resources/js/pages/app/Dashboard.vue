<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import Badge from 'primevue/badge';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Message from 'primevue/message';
import ProgressBar from 'primevue/progressbar';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';
import type { AppPageProps } from '@/types';

interface FeatureUsage {
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

interface RecentPayment {
    id: string;
    type: string;
    type_label: string;
    description: string;
    amount: number;
    currency: string;
    status: string;
    status_label: string;
    status_badge: string;
    created_at: string;
}

interface SubscriptionData {
    status: { label: string; badge: string };
    plan: { name: string };
    price: { amount: number; currency: string; interval_label: string };
    ends_at: string | null;
}

const props = defineProps<{
    hasActiveSubscription: boolean;
    subscription: SubscriptionData | null;
    features: FeatureUsage[];
    statistics: {
        total_payments: number;
        total_amount: number;
        team_members: number;
    };
    recentPayments: RecentPayment[];
    canUpgrade: boolean;
}>();

const page = usePage<AppPageProps>();

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};
</script>

<template>
    <AppLayout title="Panel">
        <div class="flex flex-col gap-6">
            <!-- No Subscription Warning -->
            <Message v-if="!hasActiveSubscription" severity="warn" :closable="false">
                Aktif bir aboneliğiniz bulunmuyor. Hizmetlerden yararlanmak için bir plan seçin.
            </Message>

            <!-- Subscription Summary -->
            <Card v-if="subscription">
                <template #content>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-semibold text-surface-900 dark:text-surface-0">
                                    {{ subscription.plan.name }}
                                </span>
                                <Tag :value="subscription.status.label" :severity="subscription.status.badge as any" />
                            </div>
                            <span class="text-sm text-surface-500 dark:text-surface-400">
                                {{ formatCurrency(subscription.price.amount, subscription.price.currency) }} / {{ subscription.price.interval_label }}
                            </span>
                            <span v-if="subscription.ends_at" class="text-xs text-surface-400">
                                Bitiş: {{ formatDate(subscription.ends_at) }}
                            </span>
                        </div>
                        <div v-if="canUpgrade" class="flex gap-2">
                            <Link href="/account/plan-change">
                                <Button label="Plan Değiştir" icon="pi pi-arrow-right-arrow-left" outlined size="small" />
                            </Link>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Statistics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <template #content>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <i class="pi pi-credit-card text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ statistics.total_payments }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Toplam Ödeme</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                <i class="pi pi-wallet text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(statistics.total_amount) }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Toplam Tutar</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                                <i class="pi pi-users text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ statistics.team_members }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Ekip Üyesi</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Features Usage -->
            <Card v-if="features.length > 0">
                <template #title>
                    <span class="text-base font-semibold">Özellik Kullanımı</span>
                </template>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div v-for="feature in features" :key="feature.name" class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-surface-700 dark:text-surface-300">{{ feature.name }}</span>
                                <span v-if="feature.usage.type === 'boolean'" class="text-sm">
                                    <Tag :value="feature.usage.enabled ? 'Aktif' : 'Pasif'" :severity="feature.usage.enabled ? 'success' : 'secondary'" />
                                </span>
                                <span v-else-if="feature.usage.is_unlimited" class="text-xs text-surface-500">Limitsiz</span>
                                <span v-else class="text-xs text-surface-500">
                                    {{ feature.usage.used }} / {{ feature.usage.limit }} {{ feature.unit ?? '' }}
                                </span>
                            </div>
                            <ProgressBar
                                v-if="feature.usage.type === 'numeric' && !feature.usage.is_unlimited"
                                :value="feature.usage.percentage"
                                :showValue="false"
                                style="height: 6px"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Recent Payments -->
            <Card v-if="recentPayments.length > 0">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-semibold">Son Ödemeler</span>
                        <Link href="/account/payments">
                            <Button label="Tümünü Gör" text size="small" />
                        </Link>
                    </div>
                </template>
                <template #content>
                    <DataTable :value="recentPayments" :rows="5" stripedRows>
                        <Column field="description" header="Açıklama">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium">{{ data.description }}</span>
                                    <span class="text-xs text-surface-400">{{ data.type_label }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="amount" header="Tutar">
                            <template #body="{ data }">
                                <span class="text-sm font-semibold">{{ formatCurrency(data.amount, data.currency) }}</span>
                            </template>
                        </Column>
                        <Column field="status_label" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="data.status_label" :severity="data.status_badge as any" />
                            </template>
                        </Column>
                        <Column field="created_at" header="Tarih">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
