<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import AuthLayout from '@/layouts/Auth.vue';

const props = defineProps<{
    invitation: {
        id: string;
        email: string;
        role: string;
        tenant_name: string;
        expires_at: string;
    };
    token: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(`/invitation/accept/${props.token}`);
};
</script>

<template>
    <AuthLayout title="Daveti Kabul Et" subtitle="Bir işletmeye katılmaya davet edildiniz." :showFooter="false">
        <div class="flex flex-col gap-4">
            <Card class="!shadow-none !ring-0">
                <template #content>
                    <div class="flex flex-col gap-3 text-center">
                        <i class="pi pi-envelope text-3xl text-primary-600" />
                        <p class="text-sm text-surface-600 dark:text-surface-400">
                            <strong>{{ invitation.tenant_name }}</strong> işletmesine katılmaya davet edildiniz.
                        </p>
                        <p class="text-xs text-surface-400">
                            Davet e-postası: {{ invitation.email }}
                        </p>
                    </div>
                </template>
            </Card>

            <Button label="Daveti Kabul Et" icon="pi pi-check" :loading="form.processing" fluid @click="submit" />

            <div class="text-center">
                <a href="/login" class="text-sm text-surface-500 hover:text-primary-600">Giriş sayfasına dön</a>
            </div>
        </div>
    </AuthLayout>
</template>
