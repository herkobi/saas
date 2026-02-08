<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { watch } from 'vue';
import type { AppPageProps } from '@/types';

const toast = useToast();
const page = usePage<AppPageProps>();

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;

        if (flash.success) {
            toast.add({ severity: 'success', summary: 'İşlem Başarılı', detail: flash.success, life: 3000 });
        }
        if (flash.error) {
            toast.add({ severity: 'error', summary: 'Hata Oluştu', detail: flash.error, life: 5000 });
        }
        if (flash.warning) {
            toast.add({ severity: 'warn', summary: 'Dikkat', detail: flash.warning, life: 4000 });
        }
        if (flash.info) {
            toast.add({ severity: 'info', summary: 'Bilgi', detail: flash.info, life: 3000 });
        }
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
