<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, SlidersHorizontal, Trash2 } from 'lucide-vue-next';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
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
import PlansLayout from '@/pages/panel/Plans/layout/Layout.vue';
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

const showConfirm = ref(false);
let pendingConfirmAction: (() => void) | null = null;

function requestConfirm(action: () => void) {
    pendingConfirmAction = action;
    showConfirm.value = true;
}

function onConfirmed() {
    pendingConfirmAction?.();
    pendingConfirmAction = null;
}

function handleDelete(feature: FeatureListItem) {
    requestConfirm(() => {
        router.delete(destroy(feature.id).url, { preserveScroll: true });
    });
}
</script>

<template>
    <Head title="Özellikler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <PlansLayout>
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
                                                <span class="hidden sm:inline">Düzenle</span>
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="handleDelete(feature)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            <span class="hidden sm:inline">Sil</span>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <EmptyState v-else :icon="SlidersHorizontal" message="Henüz özellik oluşturulmamış">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="create().url">
                                <Plus class="mr-1.5 h-4 w-4" />
                                İlk Özelliği Oluştur
                            </Link>
                        </Button>
                    </EmptyState>
                </CardContent>
            </Card>

            <SimplePagination :data="features" label="özellik" />
        </PlansLayout>
        <ConfirmDialog v-model="showConfirm" description="Bu özelliği silmek istediğinize emin misiniz?" @confirm="onConfirmed" />
    </PanelLayout>
</template>
