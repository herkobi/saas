<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Message from 'primevue/message';
import AuthLayout from '@/layouts/Auth.vue';
import { send } from '@/routes/verification';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(send.url());
};
</script>

<template>
    <AuthLayout title="E-posta Doğrulama" subtitle="Devam etmeden önce e-posta adresinizi doğrulamanız gerekiyor.">
        <div class="flex flex-col gap-4">
            <p class="text-sm text-surface-600 dark:text-surface-400">
                Kayıt olurken belirttiğiniz e-posta adresine bir doğrulama bağlantısı gönderdik. Eğer e-postayı almadıysanız, tekrar gönderebilirsiniz.
            </p>

            <Message v-if="props.status === 'verification-link-sent'" severity="success" variant="simple" size="small">
                Yeni bir doğrulama bağlantısı e-posta adresinize gönderildi.
            </Message>

            <form @submit.prevent="submit">
                <Button type="submit" label="Doğrulama E-postasını Tekrar Gönder" icon="pi pi-refresh" :loading="form.processing" fluid />
            </form>

            <div class="mt-2 text-center text-sm">
                <a href="/login" class="font-semibold text-primary-600 hover:text-primary-500">Çıkış Yap</a>
            </div>
        </div>
    </AuthLayout>
</template>
