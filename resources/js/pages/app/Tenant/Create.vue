<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import AppLayout from '@/layouts/App.vue';

const form = useForm({
    name: '',
});

const submit = () => {
    form.post('/app/tenant');
};
</script>

<template>
    <AppLayout title="Yeni Tenant Oluştur">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">
                Yeni Tenant Oluştur
            </h2>

            <Card>
                <template #content>
                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="name" class="text-sm font-medium text-surface-700 dark:text-surface-300">
                                Tenant Adı
                            </label>
                            <InputText
                                id="name"
                                v-model="form.name"
                                placeholder="Tenant adını girin"
                                :invalid="!!form.errors.name"
                                fluid
                            />
                            <small v-if="form.errors.name" class="text-red-500">
                                {{ form.errors.name }}
                            </small>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <Button
                                type="button"
                                label="Vazgeç"
                                severity="secondary"
                                text
                                @click="$inertia.visit('/app/dashboard')"
                            />
                            <Button
                                type="submit"
                                label="Oluştur"
                                icon="pi pi-plus"
                                :loading="form.processing"
                            />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
