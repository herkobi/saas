<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import AuthLayout from '@/layouts/Auth.vue';
import { email } from '@/routes/password';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(email.url());
};
</script>

<template>
    <AuthLayout title="Şifremi Unuttum" subtitle="E-posta adresinizi girin, şifre sıfırlama bağlantısı gönderelim.">
        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <Message v-if="props.status" severity="success" variant="simple" size="small">
                {{ props.status }}
            </Message>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-300">E-posta</label>
                <InputText id="email" v-model="form.email" type="email" placeholder="ornek@alanadi.com" :invalid="!!form.errors.email" autofocus fluid />
                <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
            </div>

            <Button type="submit" label="Sıfırlama Bağlantısı Gönder" icon="pi pi-envelope" :loading="form.processing" class="mt-2" fluid />

            <div class="mt-4 text-center text-sm">
                <a href="/login" class="font-semibold text-primary-600 hover:text-primary-500">Giriş sayfasına dön</a>
            </div>
        </form>
    </AuthLayout>
</template>
