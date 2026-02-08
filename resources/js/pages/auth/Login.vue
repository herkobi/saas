<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Password from 'primevue/password';
import AuthLayout from '@/layouts/Auth.vue';
import { store } from '@/routes/login';

// Form yönetimi (Inertia useForm)
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Form gönderimi
const submit = () => {
    form.post(store.url(), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout title="Giriş Yap" subtitle="Sisteme erişmek için bilgilerinizi kullanın.">
        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <Message v-if="form.errors.email" severity="error" variant="simple" size="small">
                {{ form.errors.email }}
            </Message>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-300">E-posta</label>
                <InputText id="email" v-model="form.email" type="email" placeholder="ornek@alanadi.com" :invalid="!!form.errors.email" autofocus />
                <div v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</div>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-300">Şifre</label>
                    <a href="/forgot-password" class="text-xs text-primary-600 transition-colors hover:text-primary-500">Şifremi Unuttum</a>
                </div>
                <Password
                    id="password"
                    v-model="form.password"
                    :feedback="false"
                    toggleMask
                    placeholder="••••••••"
                    :invalid="!!form.errors.password"
                    fluid
                />
                <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox v-model="form.remember" inputId="remember" :binary="true" />
                <label for="remember" class="text-sm text-surface-600 dark:text-surface-400">Beni Hatırla</label>
            </div>

            <Button type="submit" label="Giriş Yap" icon="pi pi-sign-in" :loading="form.processing" class="mt-2" fluid />

            <div class="mt-4 text-center text-sm">
                <span class="text-surface-600 dark:text-surface-400">Hesabınız yok mu?</span>
                <a href="/register" class="ml-1 font-semibold text-primary-600 hover:text-primary-500">Ücretsiz Deneyin</a>
            </div>
        </form>
    </AuthLayout>
</template>
