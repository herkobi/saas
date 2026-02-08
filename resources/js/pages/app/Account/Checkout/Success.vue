<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import AppLayout from '@/layouts/App.vue';
</script>

<template>
    <AppLayout title="Ödeme Başarılı">
        <div class="mx-auto flex max-w-md flex-col items-center gap-6 py-12 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                <i class="pi pi-check text-4xl text-green-600 dark:text-green-400" />
            </div>
            <h2 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Ödeme Başarılı!</h2>
            <p class="text-sm text-surface-500 dark:text-surface-400">
                Ödemeniz başarıyla tamamlandı. Aboneliğiniz aktif edildi.
            </p>
            <Link href="/app/dashboard">
                <Button label="Panele Dön" icon="pi pi-home" />
            </Link>
        </div>
    </AppLayout>
</template>
