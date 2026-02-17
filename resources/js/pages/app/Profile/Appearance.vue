<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/common/AppearanceTabs.vue';
import Heading from '@/components/common/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/pages/app/Profile/layout/Layout.vue';
import { show } from '@/routes/app/profile/appearance';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Görünüm ayarları',
        href: show().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Görünüm ayarları" />

        <h1 class="sr-only">Görünüm Ayarları</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Görünüm ayarları"
                    description="Hesabınızın görünüm ayarlarını güncelleyin"
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
