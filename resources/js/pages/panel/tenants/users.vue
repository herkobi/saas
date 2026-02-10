<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import PanelLayout from '@/layouts/Panel.vue';

const props = defineProps<{
    tenant: any;
    users: any[];
    statistics: Record<string, any>;
    statusOptions: { value: number; label: string }[];
}>();

const showStatusDialog = ref(false);
const selectedUser = ref<any>(null);

const statusForm = useForm({
    status: 1,
    reason: '',
});

const openStatusDialog = (user: any) => {
    selectedUser.value = user;
    statusForm.status = user.status;
    statusForm.reason = '';
    showStatusDialog.value = true;
};

const updateStatus = () => {
    if (!selectedUser.value) return;

    statusForm.put(`/panel/tenants/${props.tenant.id}/users/${selectedUser.value.id}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            showStatusDialog.value = false;
            selectedUser.value = null;
        },
    });
};

const roleLabel = (role: string) => {
    return role === 'owner' ? 'Sahip' : 'Üye';
};

const statusLabel = (status: number) => {
    if (status === 0) return 'Pasif';
    if (status === 2) return 'Taslak';
    return 'Aktif';
};

const statusBadge = (status: number): string => {
    if (status === 0) return 'secondary';
    if (status === 2) return 'warn';
    return 'success';
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
                        <Column header="Durum">
                            <template #body="{ data }">
                                <Tag :value="statusLabel(data.status)" :severity="statusBadge(data.status)" />
                            </template>
                        </Column>
                        <Column field="created_at" header="Katılım">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <Button icon="pi pi-pencil" text size="small" aria-label="Durum Değiştir" @click="openStatusDialog(data)" />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <Dialog v-model:visible="showStatusDialog" header="Kullanıcı Durumu Değiştir" :style="{ width: '28rem' }" modal>
            <div v-if="selectedUser" class="flex flex-col gap-4">
                <div class="text-sm text-surface-500">
                    <strong>{{ selectedUser.name }}</strong> ({{ selectedUser.email }})
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-surface-500">Yeni Durum</label>
                    <Select
                        v-model="statusForm.status"
                        :options="statusOptions"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                    />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-sm text-surface-500">Sebep (isteğe bağlı)</label>
                    <Textarea
                        v-model="statusForm.reason"
                        rows="2"
                        class="w-full"
                        placeholder="Durum değişikliği sebebi..."
                    />
                </div>
                <div class="flex justify-end gap-2">
                    <Button label="İptal" text size="small" @click="showStatusDialog = false" />
                    <Button
                        label="Güncelle"
                        icon="pi pi-check"
                        size="small"
                        :loading="statusForm.processing"
                        :disabled="selectedUser && statusForm.status === selectedUser.status"
                        @click="updateStatus"
                    />
                </div>
            </div>
        </Dialog>
    </PanelLayout>
</template>
