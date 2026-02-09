<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';
import { formatCurrency, formatDate } from '@/composables/useFormatting';

defineProps<{
    tenant: any;
    addons: any[];
    activeAddons: any[];
}>();

</script>

<template>
    <PanelLayout title="Hesap Eklentileri">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link :href="`/panel/tenants/${tenant.id}`">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <div>
                    <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Eklentiler</h2>
                    <p class="text-sm text-surface-500">{{ tenant.name }}</p>
                </div>
            </div>

            <!-- Active Addons -->
            <Card>
                <template #title><span class="text-base font-semibold">Aktif Eklentiler</span></template>
                <template #content>
                    <div v-if="activeAddons.length === 0" class="py-6 text-center">
                        <p class="text-sm text-surface-500">Aktif eklenti bulunmuyor.</p>
                    </div>
                    <DataTable v-else :value="activeAddons" stripedRows>
                        <Column field="name" header="Eklenti">
                            <template #body="{ data }">
                                <span class="text-sm font-medium">{{ data.name }}</span>
                            </template>
                        </Column>
                        <Column field="quantity" header="Miktar">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.pivot?.quantity ?? data.quantity ?? '-' }}</span>
                            </template>
                        </Column>
                        <Column field="is_active" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="(data.pivot?.is_active ?? data.is_active) ? 'Aktif' : 'Pasif'" :severity="(data.pivot?.is_active ?? data.is_active) ? 'success' : 'secondary'" />
                            </template>
                        </Column>
                        <Column field="expires_at" header="Son Geçerlilik">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ formatDate(data.pivot?.expires_at ?? data.expires_at) }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <!-- All Available Addons -->
            <Card v-if="addons.length > 0">
                <template #title><span class="text-base font-semibold">Tüm Eklentiler</span></template>
                <template #content>
                    <DataTable :value="addons" stripedRows>
                        <Column field="name" header="Eklenti">
                            <template #body="{ data }">
                                <span class="text-sm font-medium">{{ data.name }}</span>
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
                    </DataTable>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
