<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Package, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import PanelLayout from '@/layouts/PanelLayout.vue';
import PlansLayout from '@/pages/panel/Plans/layout/Layout.vue';
import { index, create, edit, destroy } from '@/routes/panel/plans/addons';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { AddonListItem } from '@/types/panel';

type FeatureOption = {
    id: string;
    name: string;
};

type Props = {
    addons: PaginatedData<AddonListItem>;
    features: FeatureOption[];
    filters: {
        search?: string;
        feature_id?: string;
        addon_type?: string;
        is_active?: string;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planlar', href: '/panel/plans' },
    { title: 'Eklentiler', href: index().url },
];

const search = ref(props.filters.search ?? '');

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
    if (s) params.search = s;

    router.get(index().url, params, { preserveState: true, replace: true });
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

function handleDelete(addon: AddonListItem) {
    requestConfirm(() => {
        router.delete(destroy(addon.id).url, { preserveScroll: true });
    });
}

const addonTypeLabels: Record<string, string> = {
    increment: 'Artırım',
    unlimited: 'Sınırsız',
    boolean: 'Açma/Kapama',
};
</script>

<template>
    <Head title="Eklentiler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <PlansLayout>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Eklentiler</h1>
                    <p class="text-sm text-muted-foreground">Plan eklentilerini yönetin</p>
                </div>
                <Button size="sm" as-child>
                    <Link :href="create().url">
                        <Plus class="mr-1.5 h-4 w-4" />
                        Yeni Eklenti
                    </Link>
                </Button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <Input v-model="search" placeholder="Ara..." class="w-64" />
            </div>

            <Card>
                <CardContent class="p-0">
                    <Table v-if="addons.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Eklenti</TableHead>
                                <TableHead>Özellik</TableHead>
                                <TableHead>Tip</TableHead>
                                <TableHead>Fiyat</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Tarih</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="addon in addons.data" :key="addon.id">
                                <TableCell>
                                    <div>
                                        <p class="font-medium">{{ addon.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ addon.slug }}</p>
                                    </div>
                                </TableCell>
                                <TableCell class="text-sm">
                                    {{ addon.feature?.name ?? '-' }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ addonTypeLabels[addon.addon_type] ?? addon.addon_type }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ formatCurrency(addon.price, addon.currency) }}
                                    <span v-if="addon.is_recurring" class="text-xs text-muted-foreground"> / tekrar</span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-if="addon.is_active" variant="default">Aktif</Badge>
                                        <Badge v-else variant="secondary">Pasif</Badge>
                                        <Badge v-if="!addon.is_public" variant="outline">Özel</Badge>
                                    </div>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(addon.created_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="edit(addon.id).url">
                                                <Pencil class="h-4 w-4" />
                                                <span class="hidden sm:inline">Düzenle</span>
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="handleDelete(addon)">
                                            <Trash2 class="h-4 w-4" />
                                            <span class="hidden sm:inline">Sil</span>
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <EmptyState v-else :icon="Package" message="Henüz eklenti oluşturulmamış">
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="create().url">
                                <Plus class="mr-1.5 h-4 w-4" />
                                İlk Eklentiyi Oluştur
                            </Link>
                        </Button>
                    </EmptyState>
                </CardContent>
            </Card>

            <SimplePagination :data="addons" label="eklenti" />
        </PlansLayout>
        <ConfirmDialog v-model="showConfirm" description="Bu eklentiyi silmek istediğinize emin misiniz?" @confirm="onConfirmed" />
    </PanelLayout>
</template>
