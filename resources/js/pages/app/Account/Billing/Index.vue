<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Receipt } from 'lucide-vue-next';
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
import InputError from '@/components/common/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as billingIndex, update } from '@/routes/app/account/billing';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    account: Record<string, string | null>;
    billingInfo: Record<string, string | null>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Fatura Bilgileri', href: billingIndex().url },
];

const form = useForm({
    company_name: props.billingInfo.company_name ?? props.account.company_name ?? '',
    tax_office: props.billingInfo.tax_office ?? props.account.tax_office ?? '',
    tax_number: props.billingInfo.tax_number ?? props.account.tax_number ?? '',
    address: props.billingInfo.address ?? props.account.address ?? '',
    city: props.billingInfo.city ?? props.account.city ?? '',
    country: props.billingInfo.country ?? props.account.country ?? '',
    postal_code: props.billingInfo.postal_code ?? '',
    phone: props.billingInfo.phone ?? props.account.phone ?? '',
    billing_email: props.billingInfo.billing_email ?? '',
});

function submit() {
    form.put(update().url);
}
</script>

<template>
    <Head title="Fatura Bilgileri" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <div class="w-full max-w-2xl">
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Receipt class="h-5 w-5 text-primary" />
                            <div>
                                <CardTitle>Fatura Bilgileri</CardTitle>
                                <CardDescription>
                                    Faturalarınızda kullanılacak bilgileri güncelleyebilirsiniz.
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Company Info -->
                            <div class="space-y-4">
                                <h3 class="flex items-center gap-2 text-sm font-semibold">
                                    <Building2 class="h-4 w-4" />
                                    Firma Bilgileri
                                </h3>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2 sm:col-span-2">
                                        <Label for="company_name">Firma Adı</Label>
                                        <Input id="company_name" v-model="form.company_name" :disabled="form.processing" />
                                        <InputError :message="form.errors.company_name" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="tax_office">Vergi Dairesi</Label>
                                        <Input id="tax_office" v-model="form.tax_office" :disabled="form.processing" />
                                        <InputError :message="form.errors.tax_office" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="tax_number">Vergi Numarası</Label>
                                        <Input id="tax_number" v-model="form.tax_number" :disabled="form.processing" />
                                        <InputError :message="form.errors.tax_number" />
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="space-y-4">
                                <h3 class="text-sm font-semibold">Adres Bilgileri</h3>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2 sm:col-span-2">
                                        <Label for="address">Adres</Label>
                                        <Input id="address" v-model="form.address" :disabled="form.processing" />
                                        <InputError :message="form.errors.address" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="city">Şehir</Label>
                                        <Input id="city" v-model="form.city" :disabled="form.processing" />
                                        <InputError :message="form.errors.city" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="country">Ülke</Label>
                                        <Input id="country" v-model="form.country" :disabled="form.processing" />
                                        <InputError :message="form.errors.country" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="postal_code">Posta Kodu</Label>
                                        <Input id="postal_code" v-model="form.postal_code" :disabled="form.processing" />
                                        <InputError :message="form.errors.postal_code" />
                                    </div>
                                </div>
                            </div>

                            <!-- Contact -->
                            <div class="space-y-4">
                                <h3 class="text-sm font-semibold">İletişim</h3>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="phone">Telefon</Label>
                                        <Input id="phone" v-model="form.phone" :disabled="form.processing" />
                                        <InputError :message="form.errors.phone" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="billing_email">Fatura E-posta</Label>
                                        <Input id="billing_email" v-model="form.billing_email" type="email" :disabled="form.processing" />
                                        <InputError :message="form.errors.billing_email" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Kaydediliyor...' : 'Kaydet' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
        </AccountLayout>
    </AppLayout>
</template>
