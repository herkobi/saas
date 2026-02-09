<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';
import type { Invitation } from '@/types';

const props = defineProps<{
    invitations: Invitation[];
}>();

const inviteForm = useForm({
    email: '',
});

const submitInvite = () => {
    inviteForm.post('/app/account/users/invitations', {
        onSuccess: () => inviteForm.reset(),
    });
};

const revokeInvitation = (invitationId: string) => {
    useForm({}).delete(`/app/account/users/invitations/${invitationId}`);
};

const resendInvitation = (invitationId: string) => {
    useForm({}).post(`/app/account/users/invitations/${invitationId}/resend`);
};

const statusLabel = (status: string) => {
    const labels: Record<string, string> = {
        pending: 'Bekliyor',
        accepted: 'Kabul Edildi',
        expired: 'Süresi Doldu',
        revoked: 'İptal Edildi',
    };
    return labels[status] ?? status;
};

const statusSeverity = (status: string) => {
    const map: Record<string, string> = {
        pending: 'warn',
        accepted: 'success',
        expired: 'secondary',
        revoked: 'danger',
    };
    return map[status] ?? 'info';
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppLayout title="Davetiyeler">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Davetiyeler</h2>

            <!-- Invite Form -->
            <Card>
                <template #title><span class="text-base font-semibold">Yeni Davet Gönder</span></template>
                <template #content>
                    <form @submit.prevent="submitInvite" class="flex gap-3">
                        <div class="flex-1">
                            <InputText v-model="inviteForm.email" type="email" placeholder="ornek@alanadi.com" :invalid="!!inviteForm.errors.email" fluid />
                            <small v-if="inviteForm.errors.email" class="text-red-500">{{ inviteForm.errors.email }}</small>
                        </div>
                        <Button type="submit" label="Davet Gönder" icon="pi pi-send" :loading="inviteForm.processing" />
                    </form>
                </template>
            </Card>

            <!-- Invitations List -->
            <Card v-if="invitations.length > 0">
                <template #content>
                    <DataTable :value="invitations" stripedRows>
                        <Column field="email" header="E-posta">
                            <template #body="{ data }">
                                <span class="text-sm">{{ data.email }}</span>
                            </template>
                        </Column>
                        <Column field="status" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="statusLabel(data.status)" :severity="statusSeverity(data.status) as any" />
                            </template>
                        </Column>
                        <Column field="expires_at" header="Son Geçerlilik">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ formatDate(data.expires_at) }}</span>
                            </template>
                        </Column>
                        <Column header="">
                            <template #body="{ data }">
                                <div v-if="data.status === 'pending'" class="flex gap-1">
                                    <Button icon="pi pi-refresh" text size="small" severity="secondary" @click="resendInvitation(data.id)" aria-label="Tekrar Gönder" />
                                    <Button icon="pi pi-times" text size="small" severity="danger" @click="revokeInvitation(data.id)" aria-label="İptal Et" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
