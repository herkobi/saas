<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { index as generalIndex } from '@/routes/panel/settings/general';
import { index as companyIndex } from '@/routes/panel/settings/company';
import type { NavItem } from '@/types';

const tabs: NavItem[] = [
    { title: 'Genel', href: generalIndex() },
    { title: 'Firma Bilgileri', href: companyIndex() },
];

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- Tab Navigation -->
        <div class="flex gap-1 overflow-x-auto border-b">
            <Link
                v-for="tab in tabs"
                :key="toUrl(tab.href)"
                :href="tab.href"
                class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                :class="
                    isCurrentUrl(tab.href)
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted-foreground/30 hover:text-foreground'
                "
            >
                {{ tab.title }}
            </Link>
        </div>

        <slot />
    </div>
</template>
