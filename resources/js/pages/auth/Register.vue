<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import AuthLayout from '@/layouts/Auth.vue';
import { store } from '@/routes/register';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(store.url(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout title="Kayıt Ol" subtitle="Hemen ücretsiz hesabınızı oluşturun.">
        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-surface-700 dark:text-surface-300">Ad Soyad</label>
                <InputText id="name" v-model="form.name" placeholder="Adınız Soyadınız" :invalid="!!form.errors.name" autofocus fluid />
                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-300">E-posta</label>
                <InputText id="email" v-model="form.email" type="email" placeholder="ornek@alanadi.com" :invalid="!!form.errors.email" fluid />
                <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-300">Şifre</label>
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

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-sm font-medium text-surface-700 dark:text-surface-300">Şifre Tekrar</label>
                <Password
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :feedback="false"
                    toggleMask
                    placeholder="••••••••"
                    :invalid="!!form.errors.password_confirmation"
                    fluid
                />
                <small v-if="form.errors.password_confirmation" class="text-red-500">{{ form.errors.password_confirmation }}</small>
            </div>

            <Button type="submit" label="Kayıt Ol" icon="pi pi-user-plus" :loading="form.processing" class="mt-2" fluid />

            <div class="mt-4 text-center text-sm">
                <span class="text-surface-600 dark:text-surface-400">Zaten hesabınız var mı?</span>
                <a href="/login" class="ml-1 font-semibold text-primary-600 hover:text-primary-500">Giriş Yap</a>
            </div>
        </form>
    </AuthLayout>
</template>
