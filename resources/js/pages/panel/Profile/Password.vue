<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Password from 'primevue/password';
import PanelLayout from '@/layouts/Panel.vue';
import { update } from '@/routes/panel/profile/password';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(update.url(), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <PanelLayout title="Şifre Değiştir">
        <div class="mx-auto max-w-2xl">
            <h2 class="mb-1 text-xl font-semibold text-surface-900 dark:text-surface-0">Şifre Değiştir</h2>
            <p class="mb-6 text-sm text-surface-500 dark:text-surface-400">Güvenliğiniz için güçlü ve benzersiz bir şifre kullanın.</p>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="current_password" class="text-sm font-medium text-surface-700 dark:text-surface-300">Mevcut Şifre</label>
                    <Password id="current_password" v-model="form.current_password" :feedback="false" toggleMask :invalid="!!form.errors.current_password" fluid />
                    <small v-if="form.errors.current_password" class="text-red-500">{{ form.errors.current_password }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-300">Yeni Şifre</label>
                    <Password id="password" v-model="form.password" toggleMask :invalid="!!form.errors.password" fluid />
                    <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="password_confirmation" class="text-sm font-medium text-surface-700 dark:text-surface-300">Yeni Şifre (Tekrar)</label>
                    <Password id="password_confirmation" v-model="form.password_confirmation" :feedback="false" toggleMask fluid />
                </div>

                <div class="flex justify-end">
                    <Button type="submit" label="Şifreyi Güncelle" icon="pi pi-check" :loading="form.processing" />
                </div>
            </form>
        </div>
    </PanelLayout>
</template>
