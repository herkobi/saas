<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import PanelLayout from '@/layouts/Panel.vue';

const props = defineProps<{
    tenant: any;
    users: any[];
    statistics: Record<string, any>;
}>();

const roleLabel = (role: string) => {
    return role === 'owner' ? 'Sahip' : 'Üye';
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <PanelLayout title="Hesap Kullanıcıları">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link :href="`/panel/tenants/${tenant.id}`">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <div>
                    <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Kullanıcılar</h2>
                    <p class="text-sm text-surface-500">{{ tenant.name }}</p>
                </div>
            </div>

            <Card>
                <template #content>
                    <DataTable :value="users" stripedRows>
                        <Column field="name" header="Ad Soyad">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium">{{ data.name }}</span>
                                    <span class="text-xs text-surface-400">{{ data.email }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="role" header="Rol">
                            <template #body="{ data }">
                                <Tag :value="roleLabel(data.role ?? data.pivot?.role)" :severity="(data.role ?? data.pivot?.role) === 'owner' ? 'warn' : 'info'" />
                            </template>
                        </Column>
                        <Column field="created_at" header="Katılım">
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
