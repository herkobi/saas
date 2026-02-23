<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, Home, RotateCcw } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/app';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    error?: string | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Ödeme Başarısız', href: '#' },
];
</script>

<template>
    <Head title="Ödeme Başarısız" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="mx-auto w-full max-w-lg">
                <Card>
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-2 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <AlertTriangle class="h-8 w-8 text-red-600 dark:text-red-400" />
                        </div>
                        <CardTitle class="text-xl">Ödeme Başarısız</CardTitle>
                        <CardDescription>
                            {{ error ?? 'Ödeme işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-3">
                        <Button as-child class="w-full">
                            <Link :href="dashboard().url">
                                <RotateCcw class="mr-2 h-4 w-4" />
                                Tekrar Dene
                            </Link>
                        </Button>
                        <Button variant="outline" as-child class="w-full">
                            <Link :href="dashboard().url">
                                <Home class="mr-2 h-4 w-4" />
                                Ana Sayfa
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
