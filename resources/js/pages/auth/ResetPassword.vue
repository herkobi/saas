<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import AuthLayout from '@/layouts/Auth.vue';
import { update } from '@/routes/password';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(update.url(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout title="Şifre Sıfırla" subtitle="Yeni şifrenizi belirleyin.">
        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-surface-700 dark:text-surface-300">E-posta</label>
                <InputText id="email" v-model="form.email" type="email" :invalid="!!form.errors.email" disabled fluid />
                <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-300">Yeni Şifre</label>
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
                <label for="password_confirmation" class="text-sm font-medium text-surface-700 dark:text-surface-300">Yeni Şifre Tekrar</label>
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

            <Button type="submit" label="Şifreyi Sıfırla" icon="pi pi-lock" :loading="form.processing" class="mt-2" fluid />
        </form>
    </AuthLayout>
</template>
