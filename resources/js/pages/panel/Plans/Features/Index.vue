<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, SlidersHorizontal, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate } from '@/composables/useFormatting';
import { useFeatureType } from '@/composables/useFeatureType';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, create, edit, destroy } from '@/routes/panel/plans/features';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { FeatureListItem } from '@/types/panel';
import { ref, watch } from 'vue';

type Props = {
    features: PaginatedData<FeatureListItem>;
    filters: {
        search?: string;
        type?: string;
        is_active?: string;
    };
};

const props = defineProps<Props>();

const { typeLabel, typeColor } = useFeatureType();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planlar', href: '/panel/plans' },
    { title: 'Özellikler', href: index().url },
];

const search = ref(props.filters.search ?? '');
const typeFilter = ref(props.filters.type ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters({ search: val || undefined });
    }, 300);
});

function applyFilters(override: Record<string, string | undefined> = {}) {
    const params: Record<string, string> = {};
    const s = override.search !== undefined ? override.search : search.value;
    const t = override.type !== undefined ? override.type : typeFilter.value;

    if (s) params.search = s;
    if (t) params.type = t;

    router.get(index().url, params, { preserveState: true, replace: true });
}

function filterByType(val: string) {
    typeFilter.value = val;
    applyFilters({ type: val || undefined });
}

function handleDelete(feature: FeatureListItem) {
    if (confirm('Bu özelliği silmek istediğinize emin misiniz?')) {
        router.delete(destroy(feature.id).url, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Özellikler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Özellikler</h1>
                    <p class="text-sm text-muted-foreground">Plan özelliklerini yönetin</p>
                </div>
                <Button size="sm" as-child>
                    <Link :href="create().url">
                        <Plus class="mr-1.5 h-4 w-4" />
                        Yeni Özellik
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <Input
                    v-model="search"
                    placeholder="Ara..."
                    class="w-64"
                />
                <Select :model-value="typeFilter" @update:model-value="filterByType">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Tüm Tipler" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Tüm Tipler</SelectItem>
                        <SelectItem value="limit">Limit</SelectItem>
                        <SelectItem value="feature">Özellik</SelectItem>
                        <SelectItem value="metered">Ölçümlü</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <Card>
                <CardContent class="p-0">
                    <Table v-if="features.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Özellik</TableHead>
                                <TableHead>Tip</TableHead>
                                <TableHead>Birim</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Tarih</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="feature in features.data" :key="feature.id">
                                <TableCell>
                                    <div>
                                        <p class="font-medium">{{ feature.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ feature.code }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant="outline"
                                        :class="[typeColor(feature.type).bg, typeColor(feature.type).text]"
                                    >
                                        {{ typeLabel(feature.type) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ feature.unit ?? '-' }}
                                </TableCell>
                                <TableCell>
                                    <Badge v-if="feature.is_active" variant="default">Aktif</Badge>
                                    <Badge v-else variant="secondary">Pasif</Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(feature.created_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="edit(feature.id).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="handleDelete(feature)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <SlidersHorizontal class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">Henüz özellik oluşturulmamış</p>
                        <Button variant="outline" size="sm" class="mt-4" as-child>
                            <Link :href="create().url">
                                <Plus class="mr-1.5 h-4 w-4" />
                                İlk Özelliği Oluştur
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="features.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    {{ features.from }}–{{ features.to }} / {{ features.total }} özellik
                </p>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="!features.links.prev" as-child>
                        <Link v-if="features.links.prev" :href="features.links.prev">Önceki</Link>
                        <span v-else>Önceki</span>
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!features.links.next" as-child>
                        <Link v-if="features.links.next" :href="features.links.next">Sonraki</Link>
                        <span v-else>Sonraki</span>
                    </Button>
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
