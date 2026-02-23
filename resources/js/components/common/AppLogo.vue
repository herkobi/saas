<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/common/AppLogoIcon.vue';

const page = usePage();
const siteName = computed(() => page.props.site?.name ?? 'Herkobi');
const siteLogo = computed(() => page.props.site?.logo ?? null);
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
    >
        <img
            v-if="siteLogo"
            :src="siteLogo"
            :alt="siteName"
            class="size-5 object-contain"
        />
        <AppLogoIcon v-else class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{
            siteName
        }}</span>
    </div>
</template>
