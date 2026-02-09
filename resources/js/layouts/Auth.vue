<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Toast from 'primevue/toast';
import { computed } from 'vue';
import ToastListener from '@/components/ToastListener.vue';
import type { AppPageProps } from '@/types';

const props = withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
        showFooter?: boolean;
    }>(),
    {
        title: 'Giriş Yap',
        subtitle: 'Hesabına erişmek için bilgilerini gir.',
        showFooter: true,
    },
);

const page = usePage<AppPageProps>();
const appName = computed(() => {
    return page.props.site?.name ?? import.meta.env.VITE_APP_NAME ?? 'Herkobi';
});
</script>

<template>
    <Head :title="props.title" />

    <Toast position="top-right" />
    <ToastListener />

    <div class="flex min-h-screen w-full items-center justify-center bg-surface-50 dark:bg-surface-950 p-4 transition-colors duration-500">
        <div class="w-full max-w-[420px]">

            <div class="mb-8 text-center">
                <div class="text-3xl font-extrabold tracking-tight text-surface-900 dark:text-surface-0">
                    {{ appName }}
                </div>
                <div class="mt-2 text-sm text-surface-500 dark:text-surface-400">
                    {{ props.subtitle }}
                </div>
            </div>

            <Card class="border-0 shadow-sm ring-1 ring-surface-200 dark:ring-surface-800">
                <template #content>
                    <div class="p-2">
                        <slot />
                    </div>
                </template>
            </Card>

            <div v-if="props.showFooter" class="mt-8 text-center text-xs text-surface-400">
                &copy; {{ new Date().getFullYear() }} {{ appName }}.
            </div>
        </div>
    </div>
</template>
