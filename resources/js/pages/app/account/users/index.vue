<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';
import type { PaginatedData, TenantUser } from '@/types';

defineProps<{
    users: PaginatedData<TenantUser>;
}>();

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const roleLabel = (role: string) => {
    return role === 'owner' ? 'Sahip' : 'Üye';
};

const statusLabel = (status: number | undefined) => {
    const s = status ?? 1;
    if (s === 0) return 'Pasif';
    if (s === 2) return 'Taslak';
    return 'Aktif';
};

const statusBadge = (status: number | undefined): string => {
    const s = status ?? 1;
    if (s === 0) return 'secondary';
    if (s === 2) return 'warn';
    return 'success';
};
</script>

<template>
    <AppLayout title="Kullanıcılar">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Kullanıcılar</h2>
                <Link href="/app/account/users/invitations">
                    <Button label="Davetiyeler" icon="pi pi-envelope" outlined size="small" />
                </Link>
            </div>

            <Card>
                <template #content>
                    <DataTable :value="users.data" stripedRows>
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
                                <Tag :value="roleLabel(data.role)" :severity="data.role === 'owner' ? 'warn' : 'info'" />
                            </template>
                        </Column>
                        <Column header="Durum">
                            <template #body="{ data }">
                                <Tag :value="statusLabel(data.pivot?.status)" :severity="statusBadge(data.pivot?.status)" />
                            </template>
                        </Column>
                        <Column field="created_at" header="Katılım">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Link :href="`/app/account/users/${data.id}`">
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
