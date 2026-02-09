<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import type { PaginatedData } from '@/types';

interface PaymentItem {
    id: string;
    type: string;
    type_label: string;
    description: string;
    amount: number;
    currency: string;
    status: string;
    status_label: string;
    status_badge: string;
    paid_at: string | null;
    created_at: string;
}

defineProps<{
    payments: PaginatedData<PaymentItem>;
    statistics: {
        total_payments: number;
        total_amount: number;
        total_paid: number;
    };
    filters: Record<string, any>;
}>();

</script>

<template>
    <AppLayout title="Ödemeler">
        <div class="mx-auto max-w-5xl flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Ödemeler</h2>

            <!-- Statistics -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <template #content>
                        <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ statistics.total_payments }}</p>
                        <p class="text-xs text-surface-500">Toplam Ödeme</p>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(statistics.total_amount) }}</p>
                        <p class="text-xs text-surface-500">Toplam Tutar</p>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(statistics.total_paid) }}</p>
                        <p class="text-xs text-surface-500">Ödenen Tutar</p>
                    </template>
                </Card>
            </div>

            <!-- Payments Table -->
            <Card>
                <template #content>
                    <DataTable :value="payments.data" stripedRows>
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
                        <Column header="">
                            <template #body="{ data }">
                                <Link :href="`/app/account/payments/${data.id}`">
                                    <Button icon="pi pi-eye" text size="small" aria-label="Detay" />
                                </Link>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
