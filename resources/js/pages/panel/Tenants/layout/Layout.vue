<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useTenantTabs } from '@/composables/useTenantTabs';
import { toUrl } from '@/lib/utils';

const props = defineProps<{
    tenantId: string;
    tenantName: string;
    tenantCode?: string;
    tenantSlug?: string;
}>();

const tabs = useTenantTabs(props.tenantId);
const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- Header -->
        <div>
            <h1 class="text-lg font-semibold">{{ tenantName }}</h1>
            <p v-if="tenantCode" class="text-sm text-muted-foreground">
                {{ tenantCode }}
                <template v-if="tenantSlug"> &middot; {{ tenantSlug }}</template>
            </p>
        </div>

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
