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

interface TenantItem {
    id: string;
    name: string;
    owner_name: string | null;
    owner_email: string | null;
    subscription_status: string | null;
    subscription_status_label: string | null;
    subscription_status_badge: string | null;
    plan_name: string | null;
    created_at: string;
}

interface PlanOption {
    id: string;
    name: string;
    slug: string;
}

const props = defineProps<{
    tenants: PaginatedData<TenantItem>;
    plans: PlanOption[];
    filters: Record<string, any>;
}>();

const search = ref(props.filters?.search ?? '');
const planFilter = ref(props.filters?.plan_id ?? null);

const applyFilters = () => {
    router.get('/panel/tenants', {
        search: search.value || undefined,
        plan_id: planFilter.value || undefined,
    }, { preserveState: true });
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <PanelLayout title="Hesaplar">
        <div class="flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Hesaplar</h2>

            <Card>
                <template #content>
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
                        <form @submit.prevent="applyFilters" class="flex flex-1 gap-2">
                            <InputText v-model="search" placeholder="Hesap adı veya e-posta..." fluid />
                            <Select v-model="planFilter" :options="plans" optionLabel="name" optionValue="id" placeholder="Plan" showClear class="w-48" />
                            <Button type="submit" icon="pi pi-search" />
                        </form>
                    </div>

                    <DataTable :value="tenants.data" stripedRows>
                        <Column field="name" header="Hesap">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <Link :href="`/panel/tenants/${data.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                        {{ data.name }}
                                    </Link>
                                    <span v-if="data.owner_email" class="text-xs text-surface-400">{{ data.owner_email }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="plan_name" header="Plan">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.plan_name ?? '-' }}</span>
                            </template>
                        </Column>
                        <Column field="subscription_status" header="Abonelik">
                            <template #body="{ data }">
                                <Tag v-if="data.subscription_status_label" :value="data.subscription_status_label" :severity="(data.subscription_status_badge as any) ?? 'secondary'" />
                                <span v-else class="text-sm text-surface-400">-</span>
                            </template>
                        </Column>
                        <Column field="created_at" header="Kayıt Tarihi">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Link :href="`/panel/tenants/${data.id}`">
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
