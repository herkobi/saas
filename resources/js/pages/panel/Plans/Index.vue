<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Archive, ArchiveRestore, Package, Pencil, Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import SimplePagination from '@/components/common/SimplePagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
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
import { index, create, edit, archive, restore } from '@/routes/panel/plans';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import type { PlanListItem } from '@/types/panel';

type Props = {
    plans: PaginatedData<PlanListItem>;
    filters: {
        archived?: string;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planlar', href: index().url },
];

const isArchived = props.filters.archived === '1';

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

function toggleArchived() {
    router.get(index().url, isArchived ? {} : { archived: '1' }, {
        preserveState: true,
        replace: true,
    });
}

function handleArchive(plan: PlanListItem) {
    requestConfirm(() => {
        router.post(archive(plan.id).url, {}, { preserveScroll: true });
    });
}

function handleRestore(plan: PlanListItem) {
    router.post(restore(plan.id).url, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Planlar" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <PlansLayout>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold">Planlar</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ isArchived ? 'Arşivlenmiş planlar' : 'Tüm abonelik planlarını yönetin' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="toggleArchived">
                        <Archive v-if="!isArchived" class="mr-1.5 h-4 w-4" />
                        <Package v-else class="mr-1.5 h-4 w-4" />
                        {{ isArchived ? 'Aktif Planlar' : 'Arşiv' }}
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="create().url">
                            <Plus class="mr-1.5 h-4 w-4" />
                            Yeni Plan
                        </Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardContent class="p-0">
                    <Table v-if="plans.data.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Plan</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead class="text-center">Fiyatlar</TableHead>
                                <TableHead class="text-center">Özellikler</TableHead>
                                <TableHead>Tarih</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="plan in plans.data" :key="plan.id">
                                <TableCell>
                                    <div>
                                        <p class="font-medium">{{ plan.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ plan.slug }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-if="plan.is_active" variant="default">Aktif</Badge>
                                        <Badge v-else variant="secondary">Pasif</Badge>
                                        <Badge v-if="plan.is_free" variant="outline">Ücretsiz</Badge>
                                        <Badge v-if="!plan.is_public" variant="outline">Özel</Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="plan.prices && plan.prices.length > 0" class="flex flex-col gap-0.5">
                                        <span
                                            v-for="price in plan.prices"
                                            :key="price.id"
                                            class="text-xs"
                                        >
                                            {{ formatCurrency(price.amount) }}<span class="text-muted-foreground">/{{ price.interval === 'month' ? 'ay' : price.interval === 'year' ? 'yıl' : 'gün' }}</span>
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell class="text-center">
                                    {{ plan.features_count }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(plan.created_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="edit(plan.id).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="isArchived"
                                            variant="ghost"
                                            size="sm"
                                            @click="handleRestore(plan)"
                                        >
                                            <ArchiveRestore class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <EmptyState
                        v-else
                        :icon="Package"
                        :message="isArchived ? 'Arşivlenmiş plan bulunmuyor' : 'Henüz plan oluşturulmamış'"
                    >
                        <Button v-if="!isArchived" variant="outline" size="sm" as-child>
                            <Link :href="create().url">
                                <Plus class="mr-1.5 h-4 w-4" />
                                İlk Planı Oluştur
                            </Link>
                        </Button>
                    </EmptyState>
                </CardContent>
            </Card>

            <SimplePagination :data="plans" label="plan" />
        </PlansLayout>
        <ConfirmDialog v-model="showConfirm" description="Bu planı arşivlemek istediğinize emin misiniz?" @confirm="onConfirmed" />
    </PanelLayout>
</template>
