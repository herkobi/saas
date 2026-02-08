<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';
import type { PaginatedData } from '@/types';

interface PlanItem {
    id: string;
    name: string;
    slug: string;
    is_active: boolean;
    is_published: boolean;
    trial_days: number;
    prices_count: number;
    features_count: number;
    created_at: string;
    deleted_at: string | null;
}

const props = defineProps<{
    plans: PaginatedData<PlanItem>;
    filters: Record<string, any>;
}>();

const showArchived = props.filters?.archived === '1';

const toggleArchived = () => {
    router.get('/panel/plans', { archived: showArchived ? undefined : '1' }, { preserveState: true });
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <PanelLayout title="Planlar">
        <div class="flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Planlar</h2>
                <div class="flex gap-2">
                    <Button :label="showArchived ? 'Aktif Planlar' : 'Arşivlenmiş'" :icon="showArchived ? 'pi pi-list' : 'pi pi-inbox'" outlined size="small" @click="toggleArchived" />
                    <Link href="/panel/plans/create">
                        <Button label="Yeni Plan" icon="pi pi-plus" size="small" />
                    </Link>
                </div>
            </div>

            <Card>
                <template #content>
                    <DataTable :value="plans.data" stripedRows>
                        <Column field="name" header="Plan Adı">
                            <template #body="{ data }">
                                <Link :href="`/panel/plans/${data.id}/edit`" class="text-sm font-medium text-primary-600 hover:underline">
                                    {{ data.name }}
                                </Link>
                            </template>
                        </Column>
                        <Column field="is_active" header="Durum">
                            <template #body="{ data }">
                                <Tag v-if="data.deleted_at" value="Arşiv" severity="secondary" />
                                <Tag v-else-if="data.is_published" value="Yayında" severity="success" />
                                <Tag v-else-if="data.is_active" value="Aktif" severity="info" />
                                <Tag v-else value="Pasif" severity="warn" />
                            </template>
                        </Column>
                        <Column field="trial_days" header="Deneme">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.trial_days > 0 ? `${data.trial_days} gün` : '-' }}</span>
                            </template>
                        </Column>
                        <Column field="created_at" header="Oluşturulma">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Link :href="`/panel/plans/${data.id}/edit`">
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
