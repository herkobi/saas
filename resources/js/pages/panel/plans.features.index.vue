<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';
import type { PaginatedData } from '@/types';
import { ref } from 'vue';

interface FeatureItem {
    id: string;
    name: string;
    slug: string;
    code: string;
    type: string;
    type_label?: string;
    unit: string | null;
    is_active: boolean;
    created_at: string;
}

const props = defineProps<{
    features: PaginatedData<FeatureItem>;
    filters: Record<string, any>;
}>();

const search = ref(props.filters?.search ?? '');

const applySearch = () => {
    router.get('/panel/plans/features', { search: search.value || undefined }, { preserveState: true });
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <PanelLayout title="Özellikler">
        <div class="flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Özellikler</h2>
                <Link href="/panel/plans/features/create">
                    <Button label="Yeni Özellik" icon="pi pi-plus" size="small" />
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

                    <DataTable :value="features.data" stripedRows>
                        <Column field="name" header="Özellik">
                            <template #body="{ data }">
                                <Link :href="`/panel/plans/features/${data.id}/edit`" class="text-sm font-medium text-primary-600 hover:underline">
                                    {{ data.name }}
                                </Link>
                            </template>
                        </Column>
                        <Column field="type" header="Tip">
                            <template #body="{ data }">
                                <Tag :value="data.type_label ?? data.type" severity="info" />
                            </template>
                        </Column>
                        <Column field="is_active" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="data.is_active ? 'Aktif' : 'Pasif'" :severity="data.is_active ? 'success' : 'secondary'" />
                            </template>
                        </Column>
                        <Column field="created_at" header="Tarih">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Link :href="`/panel/plans/features/${data.id}/edit`">
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
