<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import PanelLayout from '@/layouts/Panel.vue';
import { update, publish, unpublish } from '@/routes/panel/plans';

interface PlanPrice {
    id: string;
    interval: string;
    interval_label?: string;
    price: number;
    currency: string;
    is_active: boolean;
}

interface Feature {
    id: string;
    name: string;
    slug: string;
    type: string;
}

const props = defineProps<{
    plan: any;
    features: Feature[];
    prices: PlanPrice[];
    enabledFeatures: Record<string, any>;
    tenants: any[];
    intervalCases: any[];
    planTenantsCount: number;
    tenantList: any[];
}>();

const form = useForm({
    name: props.plan.name,
    slug: props.plan.slug,
    description: props.plan.description ?? '',
    trial_days: props.plan.trial_days,
    is_active: props.plan.is_active,
});

const submit = () => {
    form.put(update.url(props.plan.id));
};

const togglePublish = () => {
    if (props.plan.is_published) {
        useForm({}).post(unpublish.url(props.plan.id));
    } else {
        useForm({}).post(publish.url(props.plan.id));
    }
};

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};
</script>

<template>
    <PanelLayout title="Plan Düzenle">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/panel/plans">
                        <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                    </Link>
                    <div>
                        <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">{{ plan.name }}</h2>
                        <p class="text-sm text-surface-500">{{ planTenantsCount }} hesap bu planı kullanıyor</p>
                    </div>
                </div>
                <Button
                    :label="plan.is_published ? 'Yayından Kaldır' : 'Yayınla'"
                    :icon="plan.is_published ? 'pi pi-eye-slash' : 'pi pi-eye'"
                    :severity="plan.is_published ? 'warn' : 'success'"
                    outlined
                    size="small"
                    @click="togglePublish"
                />
            </div>

            <!-- Plan Details -->
            <Card>
                <template #title><span class="text-base font-semibold">Plan Bilgileri</span></template>
                <template #content>
                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="description" class="text-sm font-medium text-surface-700 dark:text-surface-300">Açıklama</label>
                            <Textarea id="description" v-model="form.description" rows="3" fluid />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label for="trial_days" class="text-sm font-medium text-surface-700 dark:text-surface-300">Deneme Süresi (Gün)</label>
                                <InputNumber id="trial_days" v-model="form.trial_days" :min="0" fluid />
                            </div>

                            <div class="flex items-center gap-3 pt-6">
                                <ToggleSwitch v-model="form.is_active" />
                                <span class="text-sm text-surface-700 dark:text-surface-300">Aktif</span>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" label="Kaydet" icon="pi pi-check" :loading="form.processing" />
                        </div>
                    </form>
                </template>
            </Card>

            <!-- Prices -->
            <Card v-if="prices.length > 0">
                <template #title><span class="text-base font-semibold">Fiyatlar</span></template>
                <template #content>
                    <DataTable :value="prices" stripedRows>
                        <Column field="interval" header="Periyot">
                            <template #body="{ data }">
                                <span class="text-sm font-medium">{{ data.interval_label ?? data.interval }}</span>
                            </template>
                        </Column>
                        <Column field="price" header="Fiyat">
                            <template #body="{ data }">
                                <span class="text-sm font-semibold">{{ formatCurrency(data.price, data.currency) }}</span>
                            </template>
                        </Column>
                        <Column field="is_active" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="data.is_active ? 'Aktif' : 'Pasif'" :severity="data.is_active ? 'success' : 'secondary'" />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <!-- Features -->
            <Card v-if="features.length > 0">
                <template #title><span class="text-base font-semibold">Özellikler</span></template>
                <template #content>
                    <DataTable :value="features" stripedRows>
                        <Column field="name" header="Özellik">
                            <template #body="{ data }">
                                <span class="text-sm font-medium">{{ data.name }}</span>
                            </template>
                        </Column>
                        <Column field="type" header="Tip">
                            <template #body="{ data }">
                                <Tag :value="data.type" severity="info" />
                            </template>
                        </Column>
                        <Column header="Değer">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ enabledFeatures[data.id]?.value ?? '-' }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <!-- Tenants Using Plan -->
            <Card v-if="tenantList.length > 0">
                <template #title><span class="text-base font-semibold">Bu Planı Kullanan Hesaplar</span></template>
                <template #content>
                    <DataTable :value="tenantList" stripedRows>
                        <Column field="name" header="Hesap">
                            <template #body="{ data }">
                                <Link :href="`/panel/tenants/${data.id}`" class="text-sm font-medium text-primary-600 hover:underline">
                                    {{ data.name }}
                                </Link>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
