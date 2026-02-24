<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/app';
import type { BreadcrumbItem } from '@/types';

type Props = {
    tenant: { id: string; name: string; code: string };
    hasActiveSubscription: boolean;
    subscription: {
        status_label: string;
        plan: { name: string };
        price: { amount: number; currency: string; interval_label: string };
    } | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Başlangıç',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Başlangıç" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-2">
                <h1 class="text-3xl font-bold tracking-tight">Hoş Geldiniz</h1>
                <p class="text-muted-foreground">{{ props.tenant.name }} hesabınıza başarıyla giriş yaptınız.</p>
            </div>
        </div>
    </AppLayout>
</template>
