<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Loader2, Shield } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatCurrency } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/app';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    checkout: {
        id: string;
        merchant_oid: string;
        final_amount: number;
        currency: string;
    };
    planPrice: {
        plan: {
            name: string;
        };
    };
    iframeUrl: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Ödeme İşlemi', href: '#' },
];

const iframeLoaded = ref(false);

function onIframeLoad() {
    iframeLoaded.value = true;
}
</script>

<template>
    <Head title="Ödeme İşlemi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="mx-auto w-full max-w-2xl">
                <!-- Info -->
                <Card class="mb-4">
                    <CardHeader class="pb-3">
                        <div class="flex items-center gap-2">
                            <Shield class="h-5 w-5 text-primary" />
                            <div>
                                <CardTitle class="text-base">{{ planPrice.plan.name }}</CardTitle>
                                <CardDescription>
                                    {{ formatCurrency(checkout.final_amount, checkout.currency) }} ödeme işlemi
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Iframe Container -->
                <Card>
                    <CardContent class="p-0">
                        <!-- Loading -->
                        <div v-if="!iframeLoaded" class="flex flex-col items-center justify-center py-24">
                            <Loader2 class="h-8 w-8 animate-spin text-primary" />
                            <p class="mt-3 text-sm text-muted-foreground">Ödeme formu yükleniyor...</p>
                        </div>

                        <!-- PayTR iFrame -->
                        <iframe
                            :src="iframeUrl"
                            frameborder="0"
                            class="w-full"
                            :class="iframeLoaded ? 'min-h-[500px]' : 'h-0'"
                            @load="onIframeLoad"
                        />
                    </CardContent>
                </Card>

                <p class="mt-3 text-center text-xs text-muted-foreground">
                    Ödeme işlemi PayTR güvenli ödeme altyapısı üzerinden gerçekleştirilmektedir.
                    Kart bilgileriniz bizimle paylaşılmaz.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
