<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';
import type { PaginatedData } from '@/types';
import { ref } from 'vue';

const props = defineProps<{
    subscriptions: PaginatedData<any>;
    statistics: Record<string, any>;
    filters: Record<string, any>;
    plans: any[];
    statuses: any[];
}>();

const search = ref(props.filters?.search ?? '');

const applySearch = () => {
    router.get('/panel/subscriptions', { search: search.value || undefined }, { preserveState: true });
};

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <PanelLayout title="Abonelikler">
        <div class="flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Abonelikler</h2>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ statistics?.total ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Toplam</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ statistics?.active ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Aktif</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-amber-600">{{ statistics?.trial ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Deneme</p>
                        </div>
                    </template>
                </Card>
                <Card>
                    <template #content>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-red-600">{{ statistics?.expired ?? 0 }}</p>
                            <p class="text-xs text-surface-500">Süresi Dolmuş</p>
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

                    <DataTable :value="subscriptions.data" stripedRows>
                        <Column field="tenant" header="Hesap">
                            <template #body="{ data }">
                                <Link :href="`/panel/tenants/${data.tenant?.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                    {{ data.tenant?.name ?? '-' }}
                                </Link>
                            </template>
                        </Column>
                        <Column field="plan_name" header="Plan">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.plan_name ?? '-' }}</span>
                            </template>
                        </Column>
                        <Column field="status_label" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="data.status_label" :severity="(data.status_badge as any) ?? 'info'" />
                            </template>
                        </Column>
                        <Column field="ends_at" header="Bitiş">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.ends_at ? formatDate(data.ends_at) : '-' }}</span>
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Link :href="`/panel/subscriptions/${data.id}`">
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
