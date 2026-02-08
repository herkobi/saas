<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';

const props = defineProps<{
    subscription: any;
    payments: any[];
}>();

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <PanelLayout title="Abonelik Detayı">
        <div class="mx-auto max-w-3xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/panel/subscriptions">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Abonelik Detayı</h2>
            </div>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Hesap</span>
                            <Link v-if="subscription.tenant" :href="`/panel/tenants/${subscription.tenant.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                {{ subscription.tenant.name }}
                            </Link>
                            <span v-else class="text-sm text-surface-500">-</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Plan</span>
                            <span class="text-sm font-medium text-surface-900 dark:text-surface-0">{{ subscription.plan_name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Durum</span>
                            <Tag :value="subscription.status_label" :severity="(subscription.status_badge as any) ?? 'info'" />
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Fiyat</span>
                            <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">
                                {{ subscription.price ? formatCurrency(subscription.price.price, subscription.price.currency) : '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Başlangıç</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDate(subscription.starts_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Bitiş</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDate(subscription.ends_at) }}</span>
                        </div>
                        <div v-if="subscription.trial_ends_at" class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Deneme Bitiş</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDate(subscription.trial_ends_at) }}</span>
                        </div>
                        <div v-if="subscription.grace_ends_at" class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Ek Süre Bitiş</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDate(subscription.grace_ends_at) }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Related Payments -->
            <Card v-if="payments && payments.length > 0">
                <template #title><span class="text-base font-semibold">İlişkili Ödemeler</span></template>
                <template #content>
                    <DataTable :value="payments" stripedRows>
                        <Column field="amount" header="Tutar">
                            <template #body="{ data }">
                                <span class="text-sm font-semibold">{{ formatCurrency(data.amount, data.currency) }}</span>
                            </template>
                        </Column>
                        <Column field="status_label" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="data.status_label" :severity="(data.status_badge as any) ?? 'info'" />
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
    </PanelLayout>
</template>
