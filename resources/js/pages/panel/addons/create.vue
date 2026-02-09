<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { computed, watch } from 'vue';
import PanelLayout from '@/layouts/Panel.vue';

interface Feature {
    id: string;
    name: string;
    code: string;
    type: string;
}

const props = defineProps<{
    features: Feature[];
    allowedAddonTypesByFeatureType: Record<string, string[]>;
    systemCurrency: string;
}>();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    feature_id: null as string | null,
    addon_type: '',
    price: 0,
    quantity: 1,
    duration_days: null as number | null,
    is_recurring: false,
    is_active: true,
    is_public: true,
});

const selectedFeature = computed(() => props.features.find(f => f.id === form.feature_id));

const addonTypeOptions = computed(() => {
    if (!selectedFeature.value) return [];
    const types = props.allowedAddonTypesByFeatureType[selectedFeature.value.type] ?? [];
    return types.map(t => ({ label: t, value: t }));
});

watch(() => form.feature_id, () => {
    form.addon_type = '';
});

const submit = () => {
    form.post('/panel/plans/addons');
};
</script>

<template>
    <PanelLayout title="Yeni Eklenti">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/panel/plans/addons">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Yeni Eklenti Oluştur</h2>
            </div>

            <Card>
                <template #content>
                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="name" class="text-sm font-medium text-surface-700 dark:text-surface-300">Eklenti Adı</label>
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
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label for="feature_id" class="text-sm font-medium text-surface-700 dark:text-surface-300">Özellik</label>
                                <Select id="feature_id" v-model="form.feature_id" :options="features" optionLabel="name" optionValue="id" placeholder="Seçin" :invalid="!!form.errors.feature_id" fluid />
                                <small v-if="form.errors.feature_id" class="text-red-500">{{ form.errors.feature_id }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="addon_type" class="text-sm font-medium text-surface-700 dark:text-surface-300">Eklenti Tipi</label>
                                <Select id="addon_type" v-model="form.addon_type" :options="addonTypeOptions" optionLabel="label" optionValue="value" placeholder="Seçin" :disabled="!form.feature_id" :invalid="!!form.errors.addon_type" fluid />
                                <small v-if="form.errors.addon_type" class="text-red-500">{{ form.errors.addon_type }}</small>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label for="price" class="text-sm font-medium text-surface-700 dark:text-surface-300">Fiyat (kuruş)</label>
                                <InputNumber id="price" v-model="form.price" :min="0" :invalid="!!form.errors.price" fluid />
                                <small v-if="form.errors.price" class="text-red-500">{{ form.errors.price }}</small>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="quantity" class="text-sm font-medium text-surface-700 dark:text-surface-300">Miktar</label>
                                <InputNumber id="quantity" v-model="form.quantity" :min="1" fluid />
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <div class="flex items-center gap-3">
                                <ToggleSwitch v-model="form.is_recurring" />
                                <span class="text-sm text-surface-700 dark:text-surface-300">Tekrarlayan</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <ToggleSwitch v-model="form.is_active" />
                                <span class="text-sm text-surface-700 dark:text-surface-300">Aktif</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <ToggleSwitch v-model="form.is_public" />
                                <span class="text-sm text-surface-700 dark:text-surface-300">Herkese Açık</span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Link href="/panel/plans/addons">
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
