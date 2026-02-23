<script setup lang="ts">
import { type Component } from 'vue';

defineProps<{
    icon?: Component;
    message: string;
    description?: string;
    bordered?: boolean;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center py-12 text-center"
        :class="bordered && 'rounded-lg border border-dashed'"
    >
        <component :is="icon" v-if="icon" class="mb-3 h-10 w-10 text-muted-foreground/50" />
        <p class="text-sm font-medium text-muted-foreground">{{ message }}</p>
        <p v-if="description" class="mt-1 text-xs text-muted-foreground/70">{{ description }}</p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
