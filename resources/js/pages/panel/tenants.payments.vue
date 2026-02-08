<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';
import type { PaginatedData } from '@/types';

const props = defineProps<{
    tenant: any;
    payments: PaginatedData<any>;
    statistics: Record<string, any>;
}>();

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <PanelLayout title="Hesap Ödemeleri">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link :href="`/panel/tenants/${tenant.id}`">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <div>
                    <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Ödemeler</h2>
                    <p class="text-sm text-surface-500">{{ tenant.name }}</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ statistics?.total_count ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Toplam Ödeme</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(statistics?.total_paid ?? 0) }}</p>
                            <p class="text-xs text-surface-500">Toplam Tutar</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-red-600">{{ statistics?.failed_count ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Başarısız</p>
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #content>
                    <DataTable :value="payments.data" stripedRows>
                        <Column field="description" header="Açıklama">
                            <template #body="{ data }">
                                <span class="text-sm font-medium">{{ data.description ?? '-' }}</span>
                            </template>
                        </Column>
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
