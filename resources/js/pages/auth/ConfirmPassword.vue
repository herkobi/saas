<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Password from 'primevue/password';
import AuthLayout from '@/layouts/Auth.vue';
import { store } from '@/routes/password/confirm';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(store.url(), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout title="Şifre Onayı" subtitle="Devam etmeden önce şifrenizi onaylayın.">
        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <p class="text-sm text-surface-600 dark:text-surface-400">
                Bu güvenli bir alandır. Devam etmek için lütfen şifrenizi girin.
            </p>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-300">Şifre</label>
                <Password
                    id="password"
                    v-model="form.password"
                    :feedback="false"
                    toggleMask
                    placeholder="••••••••"
                    :invalid="!!form.errors.password"
                    autofocus
                    fluid
                />
                <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
            </div>

            <Button type="submit" label="Onayla" icon="pi pi-check" :loading="form.processing" class="mt-2" fluid />
        </form>
    </AuthLayout>
</template>
