<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import AppLayout from '@/layouts/App.vue';

const props = defineProps<{
    account: Record<string, any>;
    billingInfo: Record<string, any>;
}>();

const form = useForm({
    company_name: props.billingInfo.company_name ?? '',
    tax_office: props.billingInfo.tax_office ?? '',
    tax_number: props.billingInfo.tax_number ?? '',
    address: props.billingInfo.address ?? '',
    city: props.billingInfo.city ?? '',
    country: props.billingInfo.country ?? 'Türkiye',
    phone: props.billingInfo.phone ?? '',
});

const submit = () => {
    form.put('/app/account/billing');
};
</script>

<template>
    <AppLayout title="Fatura Bilgileri">
        <div class="mx-auto max-w-2xl">
            <h2 class="mb-1 text-xl font-semibold text-surface-900 dark:text-surface-0">Fatura Bilgileri</h2>
            <p class="mb-6 text-sm text-surface-500 dark:text-surface-400">Faturalarınızda görünecek bilgileri güncelleyin.</p>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="company_name" class="text-sm font-medium text-surface-700 dark:text-surface-300">Firma / Ad Soyad</label>
                    <InputText id="company_name" v-model="form.company_name" :invalid="!!form.errors.company_name" fluid />
                    <small v-if="form.errors.company_name" class="text-red-500">{{ form.errors.company_name }}</small>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label for="tax_office" class="text-sm font-medium text-surface-700 dark:text-surface-300">Vergi Dairesi</label>
                        <InputText id="tax_office" v-model="form.tax_office" :invalid="!!form.errors.tax_office" fluid />
                        <small v-if="form.errors.tax_office" class="text-red-500">{{ form.errors.tax_office }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="tax_number" class="text-sm font-medium text-surface-700 dark:text-surface-300">Vergi / TC No</label>
                        <InputText id="tax_number" v-model="form.tax_number" :invalid="!!form.errors.tax_number" fluid />
                        <small v-if="form.errors.tax_number" class="text-red-500">{{ form.errors.tax_number }}</small>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="address" class="text-sm font-medium text-surface-700 dark:text-surface-300">Adres</label>
                    <InputText id="address" v-model="form.address" :invalid="!!form.errors.address" fluid />
                    <small v-if="form.errors.address" class="text-red-500">{{ form.errors.address }}</small>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <label for="city" class="text-sm font-medium text-surface-700 dark:text-surface-300">Şehir</label>
                        <InputText id="city" v-model="form.city" :invalid="!!form.errors.city" fluid />
                        <small v-if="form.errors.city" class="text-red-500">{{ form.errors.city }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label for="phone" class="text-sm font-medium text-surface-700 dark:text-surface-300">Telefon</label>
                        <InputText id="phone" v-model="form.phone" :invalid="!!form.errors.phone" fluid />
                        <small v-if="form.errors.phone" class="text-red-500">{{ form.errors.phone }}</small>
                    </div>
                </div>

                <div class="flex justify-end">
                    <Button type="submit" label="Kaydet" icon="pi pi-check" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>
