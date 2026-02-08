<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import AppLayout from '@/layouts/App.vue';
import { update } from '@/routes/app/profile';
import type { AppPageProps, User } from '@/types';

const props = defineProps<{
    user: User;
    mustVerifyEmail: boolean;
    status?: string;
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
});

const submit = () => {
    form.patch(update.url());
};
</script>

<template>
    <AppLayout title="Profil Düzenle">
        <div class="mx-auto max-w-2xl">
            <h2 class="mb-1 text-xl font-semibold text-surface-900 dark:text-surface-0">Profil Bilgileri</h2>
            <p class="mb-6 text-sm text-surface-500 dark:text-surface-400">Hesabınıza ait ad ve e-posta bilgilerinizi güncelleyin.</p>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <Message v-if="mustVerifyEmail && !props.user.email_verified_at" severity="warn" variant="simple" size="small">
                    E-posta adresiniz doğrulanmamış. Lütfen gelen kutunuzu kontrol edin.
                </Message>

                <Message v-if="props.status === 'verification-link-sent'" severity="success" variant="simple" size="small">
                    Yeni bir doğrulama bağlantısı e-posta adresinize gönderildi.
                </Message>

                <div class="flex flex-col gap-2">
                    <label for="name" class="text-sm font-medium text-surface-700 dark:text-surface-300">Ad Soyad</label>
                    <InputText id="name" v-model="form.name" :invalid="!!form.errors.name" fluid />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-300">E-posta</label>
                    <InputText id="email" v-model="form.email" type="email" :invalid="!!form.errors.email" fluid />
                    <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                </div>

                <div class="flex justify-end">
                    <Button type="submit" label="Kaydet" icon="pi pi-check" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>
