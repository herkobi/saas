<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import PanelLayout from '@/layouts/Panel.vue';
import { store } from '@/routes/panel/plans';

const props = defineProps<{
    tenants: any[];
}>();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    trial_days: 0,
    is_active: true,
});

const submit = () => {
    form.post(store.url());
};
</script>

<template>
    <PanelLayout title="Yeni Plan">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/panel/plans">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Yeni Plan Oluştur</h2>
            </div>

            <Card>
                <template #content>
                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="name" class="text-sm font-medium text-surface-700 dark:text-surface-300">Plan Adı</label>
                            <InputText id="name" v-model="form.name" :invalid="!!form.errors.name" fluid />
                            <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="slug" class="text-sm font-medium text-surface-700 dark:text-surface-300">Slug</label>
                            <InputText id="slug" v-model="form.slug" :invalid="!!form.errors.slug" fluid />
                            <small v-if="form.errors.slug" class="text-red-500">{{ form.errors.slug }}</small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium text-surface-700 dark:text-surface-300">Açıklama</label>
                            <Textarea id="description" v-model="form.description" rows="3" fluid />
                            <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="trial_days" class="text-sm font-medium text-surface-700 dark:text-surface-300">Deneme Süresi (Gün)</label>
                            <InputNumber id="trial_days" v-model="form.trial_days" :min="0" :invalid="!!form.errors.trial_days" fluid />
                            <small v-if="form.errors.trial_days" class="text-red-500">{{ form.errors.trial_days }}</small>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Link href="/panel/plans">
                                <Button label="İptal" severity="secondary" outlined />
                            </Link>
                            <Button type="submit" label="Oluştur" icon="pi pi-check" :loading="form.processing" />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
