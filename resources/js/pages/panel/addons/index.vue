<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { ref } from 'vue';
import { formatCurrency } from '@/composables/useFormatting';
import PanelLayout from '@/layouts/Panel.vue';
import type { PaginatedData } from '@/types';

interface AddonItem {
    id: string;
    name: string;
    slug: string;
    price: number;
    currency: string;
    addon_type: string;
    addon_type_label?: string;
    is_recurring: boolean;
    is_active: boolean;
    is_public: boolean;
    feature?: { id: string; name: string };
    created_at: string;
}

const props = defineProps<{
    addons: PaginatedData<AddonItem>;
    features: any[];
    filters: Record<string, any>;
}>();

const search = ref(props.filters?.search ?? '');

const applySearch = () => {
    router.get('/panel/plans/addons', { search: search.value || undefined }, { preserveState: true });
};

</script>

<template>
    <PanelLayout title="Eklentiler">
        <div class="flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Eklentiler</h2>
                <Link href="/panel/plans/addons/create">
                    <Button label="Yeni Eklenti" icon="pi pi-plus" size="small" />
                </Link>
            </div>

            <Card>
                <template #content>
                    <div class="mb-4">
                        <form @submit.prevent="applySearch" class="flex gap-2">
                            <InputText v-model="search" placeholder="Ara..." fluid />
                            <Button type="submit" icon="pi pi-search" />
                        </form>
                    </div>

                    <DataTable :value="addons.data" stripedRows>
                        <Column field="name" header="Eklenti">
                            <template #body="{ data }">
                                <Link :href="`/panel/plans/addons/${data.id}/edit`" class="text-sm font-medium text-primary-600 hover:underline">
                                    {{ data.name }}
                                </Link>
                            </template>
                        </Column>
                        <Column field="feature" header="Özellik">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.feature?.name ?? '-' }}</span>
                            </template>
                        </Column>
                        <Column field="price" header="Fiyat">
                            <template #body="{ data }">
                                <span class="text-sm font-semibold">{{ formatCurrency(data.price, data.currency) }}</span>
                            </template>
                        </Column>
                        <Column field="addon_type" header="Tip">
                            <template #body="{ data }">
                                <Tag :value="data.addon_type_label ?? data.addon_type" severity="info" />
                            </template>
                        </Column>
                        <Column field="is_active" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="data.is_active ? 'Aktif' : 'Pasif'" :severity="data.is_active ? 'success' : 'secondary'" />
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Link :href="`/panel/plans/addons/${data.id}/edit`">
                                    <Button icon="pi pi-pencil" text size="small" aria-label="Düzenle" />
                                </Link>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
