<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import AppLayout from '@/layouts/App.vue';

defineProps<{
    error?: string;
}>();
</script>

<template>
    <AppLayout title="Ödeme Başarısız">
        <div class="mx-auto flex max-w-md flex-col items-center gap-6 py-12 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <i class="pi pi-times text-4xl text-red-600 dark:text-red-400" />
            </div>
            <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Ödeme Başarısız</h2>
            <p class="text-sm text-surface-500 dark:text-surface-400">
                {{ error ?? 'Ödeme işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.' }}
            </p>
            <div class="flex gap-3">
                <Link href="/app/account/subscription">
                    <Button label="Aboneliğe Dön" icon="pi pi-arrow-left" outlined />
                </Link>
                <Link href="/app/dashboard">
                    <Button label="Panele Dön" icon="pi pi-home" />
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
