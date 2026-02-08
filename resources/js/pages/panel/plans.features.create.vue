<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import PanelLayout from '@/layouts/Panel.vue';

const form = useForm({
    name: '',
    slug: '',
    code: '',
    description: '',
    type: 'limit',
    unit: '',
    is_active: true,
});

const featureTypes = [
    { label: 'Limit', value: 'limit' },
    { label: 'Özellik', value: 'feature' },
    { label: 'Metered', value: 'metered' },
];

const submit = () => {
    form.post('/panel/plans/features');
};
</script>

<template>
    <PanelLayout title="Yeni Özellik">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/panel/plans/features">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Yeni Özellik Oluştur</h2>
            </div>

            <Card>
                <template #content>
                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="name" class="text-sm font-medium text-surface-700 dark:text-surface-300">Özellik Adı</label>
                            <InputText id="name" v-model="form.name" :invalid="!!form.errors.name" fluid />
                            <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label for="slug" class="text-sm font-medium text-surface-700 dark:text-surface-300">Slug</label>
                                <InputText id="slug" v-model="form.slug" :invalid="!!form.errors.slug" fluid />
                                <small v-if="form.errors.slug" class="text-red-500">{{ form.errors.slug }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="code" class="text-sm font-medium text-surface-700 dark:text-surface-300">Kod</label>
                                <InputText id="code" v-model="form.code" :invalid="!!form.errors.code" fluid />
                                <small v-if="form.errors.code" class="text-red-500">{{ form.errors.code }}</small>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium text-surface-700 dark:text-surface-300">Açıklama</label>
                            <Textarea id="description" v-model="form.description" rows="3" fluid />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label for="type" class="text-sm font-medium text-surface-700 dark:text-surface-300">Tip</label>
                                <Select id="type" v-model="form.type" :options="featureTypes" optionLabel="label" optionValue="value" :invalid="!!form.errors.type" fluid />
                                <small v-if="form.errors.type" class="text-red-500">{{ form.errors.type }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="unit" class="text-sm font-medium text-surface-700 dark:text-surface-300">Birim</label>
                                <InputText id="unit" v-model="form.unit" placeholder="örn: adet, MB" fluid />
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <ToggleSwitch v-model="form.is_active" />
                            <span class="text-sm text-surface-700 dark:text-surface-300">Aktif</span>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Link href="/panel/plans/features">
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
