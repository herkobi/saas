<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';
import type { TenantUser } from '@/types';

const props = defineProps<{
    user: TenantUser;
    pivotStatus: number;
    pivotStatusLabel: string;
    pivotStatusBadge: string;
    canChangeStatus: boolean;
    statusOptions: { value: number; label: string }[];
}>();

const removeForm = useForm({});

const statusForm = useForm({
    status: props.pivotStatus,
    reason: '',
});

const removeUser = () => {
    if (confirm('Bu kullanıcıyı kaldırmak istediğinize emin misiniz?')) {
        removeForm.delete(`/app/account/users/${props.user.id}`);
    }
};

const updateStatus = () => {
    statusForm.put(`/app/account/users/${props.user.id}/status`, {
        preserveScroll: true,
    });
};

const roleLabel = (role: string) => {
    return role === 'owner' ? 'Sahip' : 'Üye';
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
    <AppLayout title="Kullanıcı Detayı">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/app/account/users">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Kullanıcı Detayı</h2>
            </div>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Ad Soyad</span>
                            <span class="text-sm font-medium text-surface-900 dark:text-surface-0">{{ user.name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">E-posta</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ user.email }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Rol</span>
                            <Tag :value="roleLabel(user.role)" :severity="user.role === 'owner' ? 'warn' : 'info'" />
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Durum</span>
                            <Tag :value="pivotStatusLabel" :severity="pivotStatusBadge" />
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-surface-500">Katılım Tarihi</span>
                            <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatDate(user.created_at) }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <Card v-if="canChangeStatus">
                <template #title>
                    <span class="text-base font-semibold">Durum Değiştir</span>
                </template>
                <template #content>
                    <div class="flex flex-col gap-4">
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
                        <div v-if="statusForm.status !== pivotStatus" class="flex flex-col gap-2">
                            <label class="text-sm text-surface-500">Sebep (isteğe bağlı)</label>
                            <textarea
                                v-model="statusForm.reason"
                                rows="2"
                                class="w-full rounded-md border border-surface-300 dark:border-surface-600 bg-surface-0 dark:bg-surface-900 px-3 py-2 text-sm"
                                placeholder="Durum değişikliği sebebi..."
                            />
                        </div>
                        <div>
                            <Button
                                label="Durumu Güncelle"
                                icon="pi pi-check"
                                size="small"
                                :loading="statusForm.processing"
                                :disabled="statusForm.status === pivotStatus"
                                @click="updateStatus"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <div class="flex gap-2">
                <Link :href="`/app/account/users/${user.id}/activities`">
                    <Button label="Aktiviteler" icon="pi pi-history" outlined size="small" />
                </Link>
                <Button v-if="user.role !== 'owner'" label="Kullanıcıyı Kaldır" icon="pi pi-trash" severity="danger" outlined size="small" :loading="removeForm.processing" @click="removeUser" />
            </div>
        </div>
    </AppLayout>
</template>
