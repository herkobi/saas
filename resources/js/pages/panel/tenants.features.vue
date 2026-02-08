<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';

interface EffectiveFeature {
    id: string;
    name: string;
    slug: string;
    type: string;
    value: any;
    source: string;
}

const props = defineProps<{
    tenant: any;
    effectiveFeatures: EffectiveFeature[];
    planFeatures: any[];
    overrides: any[];
    allFeatures: any[];
    statistics: Record<string, any>;
}>();

const removeOverride = (featureId: string) => {
    if (confirm('Bu özellik için özel değeri kaldırmak istediğinize emin misiniz?')) {
        useForm({}).delete(`/panel/tenants/${props.tenant.id}/features/${featureId}`);
    }
};

const clearAll = () => {
    if (confirm('Tüm özel özellik değerlerini kaldırmak istediğinize emin misiniz?')) {
        useForm({}).post(`/panel/tenants/${props.tenant.id}/features/clear`);
    }
};

const sourceLabel = (source: string) => {
    const labels: Record<string, string> = {
        plan: 'Plan',
        override: 'Özel Değer',
        addon: 'Eklenti',
        default: 'Varsayılan',
    };
    return labels[source] ?? source;
};

const sourceSeverity = (source: string) => {
    const map: Record<string, string> = {
        plan: 'info',
        override: 'warn',
        addon: 'success',
        default: 'secondary',
    };
    return map[source] ?? 'info';
};
</script>

<template>
    <PanelLayout title="Hesap Özellikleri">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="`/panel/tenants/${tenant.id}`">
                        <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Özellikler</h2>
                        <p class="text-sm text-surface-500">{{ tenant.name }}</p>
                    </div>
                </div>
                <Button v-if="overrides.length > 0" label="Tüm Özel Değerleri Kaldır" icon="pi pi-trash" severity="danger" outlined size="small" @click="clearAll" />
            </div>

            <Card>
                <template #title><span class="text-base font-semibold">Etkin Özellikler</span></template>
                <template #content>
                    <DataTable :value="effectiveFeatures" stripedRows>
                        <Column field="name" header="Özellik">
                            <template #body="{ data }">
                                <span class="text-sm font-medium">{{ data.name }}</span>
                            </template>
                        </Column>
                        <Column field="type" header="Tip">
                            <template #body="{ data }">
                                <Tag :value="data.type" severity="info" />
                            </template>
                        </Column>
                        <Column field="value" header="Değer">
                            <template #body="{ data }">
                                <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">{{ data.value ?? '-' }}</span>
                            </template>
                        </Column>
                        <Column field="source" header="Kaynak">
                            <template #body="{ data }">
                                <Tag :value="sourceLabel(data.source)" :severity="sourceSeverity(data.source) as any" />
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Button v-if="data.source === 'override'" icon="pi pi-times" text size="small" severity="danger" @click="removeOverride(data.id)" aria-label="Özel Değeri Kaldır" />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
