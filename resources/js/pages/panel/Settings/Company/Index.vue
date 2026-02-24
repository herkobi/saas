<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Save } from 'lucide-vue-next';
import InputError from '@/components/common/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import PanelLayout from '@/layouts/PanelLayout.vue';
import SettingsLayout from '@/pages/panel/Settings/layout/Layout.vue';
import { index as companyIndex, update as companyUpdate } from '@/routes/panel/settings/company';
import type { BreadcrumbItem } from '@/types';

type Props = {
    settings: Record<string, string | null>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ayarlar', href: companyIndex().url },
    { title: 'Firma Bilgileri', href: companyIndex().url },
];

const form = useForm({
    company_name: props.settings.company_name ?? '',
    company_address: props.settings.company_address ?? '',
    company_district: props.settings.company_district ?? '',
    company_city: props.settings.company_city ?? '',
    company_postcode: props.settings.company_postcode ?? '',
    tax_number: props.settings.tax_number ?? '',
    tax_office: props.settings.tax_office ?? '',
    mersis_number: props.settings.mersis_number ?? '',
    kep_email: props.settings.kep_email ?? '',
    invoice_prefix: props.settings.invoice_prefix ?? 'INV',
});

function submitForm() {
    form.put(companyUpdate().url);
}
</script>

<template>
    <Head title="Firma Bilgileri" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout>
            <form @submit.prevent="submitForm" class="space-y-6">
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Building2 class="h-4 w-4 text-muted-foreground" />
                            <CardTitle class="text-sm font-medium">Firma Bilgileri</CardTitle>
                        </div>
                        <CardDescription>Faturalama ve yasal bilgilerinizi girin</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="company_name">Firma Adı</Label>
                                <Input id="company_name" v-model="form.company_name" />
                                <InputError :message="form.errors.company_name" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="invoice_prefix">Fatura Ön Eki</Label>
                                <Input id="invoice_prefix" v-model="form.invoice_prefix" />
                                <InputError :message="form.errors.invoice_prefix" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="company_address">Adres</Label>
                            <Textarea id="company_address" v-model="form.company_address" rows="2" />
                            <InputError :message="form.errors.company_address" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="grid gap-2">
                                <Label for="company_district">İlçe</Label>
                                <Input id="company_district" v-model="form.company_district" />
                                <InputError :message="form.errors.company_district" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="company_city">İl</Label>
                                <Input id="company_city" v-model="form.company_city" />
                                <InputError :message="form.errors.company_city" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="company_postcode">Posta Kodu</Label>
                                <Input id="company_postcode" v-model="form.company_postcode" />
                                <InputError :message="form.errors.company_postcode" />
                            </div>
                        </div>

                        <Separator />

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="tax_number">Vergi Numarası</Label>
                                <Input id="tax_number" v-model="form.tax_number" />
                                <InputError :message="form.errors.tax_number" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="tax_office">Vergi Dairesi</Label>
                                <Input id="tax_office" v-model="form.tax_office" />
                                <InputError :message="form.errors.tax_office" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="mersis_number">Mersis Numarası</Label>
                                <Input id="mersis_number" v-model="form.mersis_number" placeholder="16 haneli" />
                                <InputError :message="form.errors.mersis_number" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="kep_email">KEP E-posta</Label>
                                <Input id="kep_email" type="email" v-model="form.kep_email" />
                                <InputError :message="form.errors.kep_email" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-1.5 h-4 w-4" />
                        Kaydet
                    </Button>
                </div>
            </form>
        </SettingsLayout>
    </PanelLayout>
</template>
