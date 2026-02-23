<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { index as plansIndex } from '@/routes/panel/plans';
import { index as featuresIndex } from '@/routes/panel/plans/features';
import { index as addonsIndex } from '@/routes/panel/plans/addons';
import type { NavItem } from '@/types';

const tabs: NavItem[] = [
    { title: 'Planlar', href: plansIndex() },
    { title: 'Özellikler', href: featuresIndex() },
    { title: 'Eklentiler', href: addonsIndex() },
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
