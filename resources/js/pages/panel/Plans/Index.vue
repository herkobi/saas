<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Archive, ArchiveRestore, Package, Pencil, Plus } from 'lucide-vue-next';
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
import { formatDate } from '@/composables/useFormatting';
import PanelLayout from '@/layouts/PanelLayout.vue';
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

function toggleArchived() {
    router.get(index().url, isArchived ? {} : { archived: '1' }, {
        preserveState: true,
        replace: true,
    });
}

function handleArchive(plan: PlanListItem) {
    if (confirm('Bu planı arşivlemek istediğinize emin misiniz?')) {
        router.post(archive(plan.id).url, {}, { preserveScroll: true });
    }
}

function handleRestore(plan: PlanListItem) {
    router.post(restore(plan.id).url, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Planlar" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
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
                                <TableCell class="text-center">
                                    {{ plan.prices_count }}
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
                                            v-if="!isArchived"
                                            variant="ghost"
                                            size="sm"
                                            @click="handleArchive(plan)"
                                        >
                                            <Archive class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            v-else
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

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <Package class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">
                            {{ isArchived ? 'Arşivlenmiş plan bulunmuyor' : 'Henüz plan oluşturulmamış' }}
                        </p>
                        <Button v-if="!isArchived" variant="outline" size="sm" class="mt-4" as-child>
                            <Link :href="create().url">
                                <Plus class="mr-1.5 h-4 w-4" />
                                İlk Planı Oluştur
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="plans.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    {{ plans.from }}–{{ plans.to }} / {{ plans.total }} plan
                </p>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="!plans.links.prev" as-child>
                        <Link v-if="plans.links.prev" :href="plans.links.prev">Önceki</Link>
                        <span v-else>Önceki</span>
                    </Button>
                    <Button variant="outline" size="sm" :disabled="!plans.links.next" as-child>
                        <Link v-if="plans.links.next" :href="plans.links.next">Sonraki</Link>
                        <span v-else>Sonraki</span>
                    </Button>
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
