<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { ref } from 'vue';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import PanelLayout from '@/layouts/Panel.vue';
import type { PaginatedData } from '@/types';

const props = defineProps<{
    payments: PaginatedData<any>;
    statistics: Record<string, any>;
    filters: Record<string, any>;
    statuses: any[];
}>();

const search = ref(props.filters?.search ?? '');

const applySearch = () => {
    router.get('/panel/payments', { search: search.value || undefined }, { preserveState: true });
};

</script>

<template>
    <PanelLayout title="Ödemeler">
        <div class="flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Ödemeler</h2>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
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
                            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(statistics?.total_revenue ?? 0) }}</p>
                            <p class="text-xs text-surface-500">Toplam Gelir</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-amber-600">{{ statistics?.pending_count ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Bekleyen</p>
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
                    <div class="mb-4">
                        <form @submit.prevent="applySearch" class="flex gap-2">
                            <InputText v-model="search" placeholder="Hesap adı..." fluid />
                            <Button type="submit" icon="pi pi-search" />
                        </form>
                    </div>

                    <DataTable :value="payments.data" stripedRows>
                        <Column field="tenant" header="Hesap">
                            <template #body="{ data }">
                                <Link v-if="data.tenant" :href="`/panel/tenants/${data.tenant.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                    {{ data.tenant.name }}
                                </Link>
                                <span v-else class="text-sm text-surface-400">-</span>
                            </template>
                        </Column>
                        <Column field="description" header="Açıklama">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.description ?? '-' }}</span>
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
                        <Column header="">
                            <template #body="{ data }">
                                <Link :href="`/panel/payments/${data.id}`">
                                    <Button icon="pi pi-eye" text size="small" aria-label="Detay" />
                                </Link>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
