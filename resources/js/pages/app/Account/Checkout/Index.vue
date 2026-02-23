<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CreditCard,
    Shield,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
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
import InputError from '@/components/common/InputError.vue';
import { formatCurrency } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/app';
import { initiate } from '@/routes/app/account/checkout';
import type { BreadcrumbItem } from '@/types';

type CheckoutPlanPrice = {
    id: string;
    price: number;
    currency: string;
    interval: string;
    interval_count: number;
    plan: {
        id: string;
        name: string;
        description: string | null;
    };
};

type CheckoutAmounts = {
    base_amount: number;
    proration_credit: number;
    tax_amount: number;
    final_amount: number;
    currency: string;
};

const props = defineProps<{
    planPrice: CheckoutPlanPrice;
    type: string;
    amounts: CheckoutAmounts;
    billingInfo: Record<string, string | null>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Ödeme', href: '#' },
];

const intervalLabels: Record<string, string> = {
    month: 'Aylık',
    year: 'Yıllık',
    day: 'Günlük',
};

const typeLabels: Record<string, string> = {
    new: 'Yeni Abonelik',
    renew: 'Yenileme',
    upgrade: 'Plan Yükseltme',
    downgrade: 'Plan Düşürme',
    addon: 'Eklenti',
    addon_renew: 'Eklenti Yenileme',
};

const form = useForm({
    plan_price_id: props.planPrice.id,
    type: props.type,
    billing_info: {
        company_name: props.billingInfo.company_name ?? '',
        tax_office: props.billingInfo.tax_office ?? '',
        tax_number: props.billingInfo.tax_number ?? '',
        address: props.billingInfo.address ?? '',
        city: props.billingInfo.city ?? '',
        country: props.billingInfo.country ?? '',
        phone: props.billingInfo.phone ?? '',
    },
});

function submit() {
    form.post(initiate().url);
}
</script>

<template>
    <Head title="Ödeme" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Back -->
            <div>
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="dashboard().url">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Geri Dön
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Billing Form -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CreditCard class="h-5 w-5 text-primary" />
                                Fatura Bilgileri
                            </CardTitle>
                            <CardDescription>
                                Ödeme için fatura bilgilerinizi kontrol edin.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submit" class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2 sm:col-span-2">
                                        <Label for="company_name">Firma Adı</Label>
                                        <Input id="company_name" v-model="form.billing_info.company_name" :disabled="form.processing" />
                                        <InputError :message="form.errors['billing_info.company_name']" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="tax_office">Vergi Dairesi</Label>
                                        <Input id="tax_office" v-model="form.billing_info.tax_office" :disabled="form.processing" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="tax_number">Vergi Numarası</Label>
                                        <Input id="tax_number" v-model="form.billing_info.tax_number" :disabled="form.processing" />
                                    </div>
                                    <div class="space-y-2 sm:col-span-2">
                                        <Label for="address">Adres</Label>
                                        <Input id="address" v-model="form.billing_info.address" :disabled="form.processing" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="city">Şehir</Label>
                                        <Input id="city" v-model="form.billing_info.city" :disabled="form.processing" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="country">Ülke</Label>
                                        <Input id="country" v-model="form.billing_info.country" :disabled="form.processing" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="phone">Telefon</Label>
                                        <Input id="phone" v-model="form.billing_info.phone" :disabled="form.processing" />
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4">
                                    <Button type="submit" :disabled="form.processing" size="lg">
                                        <Shield class="mr-2 h-4 w-4" />
                                        {{ form.processing ? 'Yönlendiriliyor...' : 'Ödemeye Geç' }}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Order Summary -->
                <div>
                    <Card class="sticky top-6">
                        <CardHeader>
                            <CardTitle class="text-base">Sipariş Özeti</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="font-semibold">{{ planPrice.plan.name }}</p>
                                <p v-if="planPrice.plan.description" class="text-sm text-muted-foreground">
                                    {{ planPrice.plan.description }}
                                </p>
                                <Badge variant="outline" class="mt-2">
                                    {{ typeLabels[type] ?? type }}
                                </Badge>
                            </div>

                            <Separator />

                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Plan ({{ intervalLabels[planPrice.interval] ?? planPrice.interval }})</span>
                                    <span>{{ formatCurrency(amounts.base_amount, amounts.currency) }}</span>
                                </div>
                                <div v-if="amounts.proration_credit > 0" class="flex justify-between text-green-600 dark:text-green-400">
                                    <span>Kalan Süre Kredisi</span>
                                    <span>-{{ formatCurrency(amounts.proration_credit, amounts.currency) }}</span>
                                </div>
                                <div v-if="amounts.tax_amount > 0" class="flex justify-between">
                                    <span class="text-muted-foreground">KDV (%20)</span>
                                    <span>{{ formatCurrency(amounts.tax_amount, amounts.currency) }}</span>
                                </div>
                            </div>

                            <Separator />

                            <div class="flex justify-between text-lg font-bold">
                                <span>Toplam</span>
                                <span>{{ formatCurrency(amounts.final_amount, amounts.currency) }}</span>
                            </div>

                            <p class="text-xs text-muted-foreground">
                                Ödeme, güvenli PayTR altyapısı üzerinden gerçekleştirilecektir.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
