<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { PaginatedData } from '@/types/common';

defineProps<{
    data: PaginatedData<any>;
    label: string;
}>();
</script>

<template>
    <div v-if="data.last_page > 1" class="flex items-center justify-between">
        <p class="text-sm text-muted-foreground">
            {{ data.from }}&ndash;{{ data.to }} / {{ data.total }} {{ label }}
        </p>
        <div class="flex gap-2">
            <Button variant="outline" size="sm" :disabled="!data.links.prev" as-child>
                <Link v-if="data.links.prev" :href="data.links.prev">Önceki</Link>
                <span v-else>Önceki</span>
            </Button>
            <Button variant="outline" size="sm" :disabled="!data.links.next" as-child>
                <Link v-if="data.links.next" :href="data.links.next">Sonraki</Link>
                <span v-else>Sonraki</span>
            </Button>
        </div>
    </div>
</template>
