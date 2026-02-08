<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';
import type { Activity, PaginatedData } from '@/types';

interface PlanDistribution {
    name: string;
    subscriber_count: number;
    total_revenue: number;
}

interface ExpiringSubscription {
    id: string;
    ends_at: string;
    tenant: { id: string; name: string };
}

interface FailedPayment {
    id: string;
    amount: number;
    created_at: string;
    tenant: { id: string; name: string };
}

const props = defineProps<{
    totalTenants: number;
    activeTenants: number;
    paymentStats: Record<string, any>;
    subscriptionStats: Record<string, any>;
    recentPayments: PaginatedData<any>;
    recentTenants: PaginatedData<any>;
    recentActivities: Activity[];
    planDistribution: PlanDistribution[];
    expiringSubscriptions: ExpiringSubscription[];
    failedPayments: FailedPayment[];
}>();

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const formatDateTime = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <PanelLayout title="Panel">
        <div class="flex flex-col gap-6">
            <!-- Statistics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <template #content>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <i class="pi pi-building text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ totalTenants }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Toplam Hesap</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                <i class="pi pi-check-circle text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ activeTenants }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Aktif Hesap</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                                <i class="pi pi-credit-card text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ subscriptionStats?.active ?? 0 }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Aktif Abonelik</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                                <i class="pi pi-wallet text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(paymentStats?.total_revenue ?? 0) }}</p>
                                <p class="text-xs text-surface-500 dark:text-surface-400">Toplam Gelir</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Recent Tenants -->
                <Card>
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span class="text-base font-semibold">Son Hesaplar</span>
                            <Link href="/panel/tenants">
                                <Button label="Tümü" text size="small" />
                            </Link>
                        </div>
                    </template>
                    <template #content>
                        <DataTable :value="recentTenants.data" :rows="5" stripedRows>
                            <Column field="name" header="Hesap">
                                <template #body="{ data }">
                                    <Link :href="`/panel/tenants/${data.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                        {{ data.name }}
                                    </Link>
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

                <!-- Recent Payments -->
                <Card>
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span class="text-base font-semibold">Son Ödemeler</span>
                            <Link href="/panel/payments">
                                <Button label="Tümü" text size="small" />
                            </Link>
                        </div>
                    </template>
                    <template #content>
                        <DataTable :value="recentPayments.data" :rows="5" stripedRows>
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

            <!-- Plan Distribution -->
            <Card v-if="planDistribution.length > 0">
                <template #title><span class="text-base font-semibold">Plan Dağılımı</span></template>
                <template #content>
                    <DataTable :value="planDistribution" stripedRows>
                        <Column field="name" header="Plan" />
                        <Column field="subscriber_count" header="Abone Sayısı">
                            <template #body="{ data }">
                                <Tag :value="String(data.subscriber_count)" severity="info" />
                            </template>
                        </Column>
                        <Column field="total_revenue" header="Toplam Gelir">
                            <template #body="{ data }">
                                <span class="text-sm font-semibold">{{ formatCurrency(data.total_revenue) }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Expiring Subscriptions -->
                <Card v-if="expiringSubscriptions.length > 0">
                    <template #title><span class="text-base font-semibold text-amber-600">Süresi Dolan Abonelikler</span></template>
                    <template #content>
                        <div class="flex flex-col gap-2">
                            <div v-for="sub in expiringSubscriptions" :key="sub.id" class="flex items-center justify-between rounded-lg border border-surface-200 p-3 dark:border-surface-700">
                                <Link :href="`/panel/tenants/${sub.tenant.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                    {{ sub.tenant.name }}
                                </Link>
                                <span class="text-xs text-surface-500">{{ formatDate(sub.ends_at) }}</span>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Failed Payments -->
                <Card v-if="failedPayments.length > 0">
                    <template #title><span class="text-base font-semibold text-red-600">Başarısız Ödemeler</span></template>
                    <template #content>
                        <div class="flex flex-col gap-2">
                            <div v-for="payment in failedPayments" :key="payment.id" class="flex items-center justify-between rounded-lg border border-surface-200 p-3 dark:border-surface-700">
                                <Link :href="`/panel/tenants/${payment.tenant.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                    {{ payment.tenant.name }}
                                </Link>
                                <span class="text-sm font-semibold text-red-600">{{ formatCurrency(payment.amount) }}</span>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Recent Activities -->
            <Card v-if="recentActivities.length > 0">
                <template #title><span class="text-base font-semibold">Son Aktiviteler</span></template>
                <template #content>
                    <div class="flex flex-col gap-2">
                        <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start justify-between gap-3 border-b border-surface-100 py-2 last:border-0 dark:border-surface-800">
                            <div class="flex-1">
                                <span class="text-sm text-surface-900 dark:text-surface-0">{{ activity.description }}</span>
                                <span class="ml-2 text-xs text-surface-400">{{ activity.type }}</span>
                            </div>
                            <span class="whitespace-nowrap text-xs text-surface-400">{{ formatDateTime(activity.created_at) }}</span>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
