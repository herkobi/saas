<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Save, SlidersHorizontal, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import InputError from '@/components/common/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import { useFeatureType } from '@/composables/useFeatureType';
import PanelLayout from '@/layouts/PanelLayout.vue';
import TenantLayout from '@/pages/panel/Tenants/layout/Layout.vue';
import { index, show as tenantShow } from '@/routes/panel/tenants';
import { index as featuresIndex, sync, destroy, clear } from '@/routes/panel/tenants/features';
import type { BreadcrumbItem } from '@/types';
import type { Tenant } from '@/types/tenant';
import type { EffectiveFeature } from '@/types/panel';

type FeatureOption = {
    id: string;
    name: string;
    slug: string;
    type: string;
};

type FeatureOverride = {
    id: string;
    feature_id: string;
    value: any;
    feature?: FeatureOption;
};

type Props = {
    tenant: Tenant;
    effectiveFeatures: EffectiveFeature[];
    planFeatures: any[];
    overrides: FeatureOverride[];
    allFeatures: FeatureOption[];
    statistics: {
        total_features: number;
        plan_features: number;
        overrides: number;
    };
};

const props = defineProps<Props>();
const { typeLabel, typeColor, sourceLabel, sourceColor } = useFeatureType();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: tenantShow(props.tenant.id).url },
    { title: 'Özellikler', href: featuresIndex(props.tenant.id).url },
];

const showAddDialog = ref(false);

const addForm = useForm({
    overrides: [{ feature_id: '', value: '' }] as { feature_id: string; value: string }[],
});

function addOverrideRow() {
    addForm.overrides.push({ feature_id: '', value: '' });
}

function removeOverrideRow(idx: number) {
    addForm.overrides.splice(idx, 1);
}

function submitSync() {
    const data = {
        overrides: addForm.overrides.filter(o => o.feature_id),
    };
    router.put(sync(props.tenant.id).url, data, {
        preserveScroll: true,
        onSuccess: () => { showAddDialog.value = false; addForm.reset(); },
    });
}

const showConfirm = ref(false);
const confirmDescription = ref('');
let pendingConfirmAction: (() => void) | null = null;

function requestConfirm(description: string, action: () => void) {
    confirmDescription.value = description;
    pendingConfirmAction = action;
    showConfirm.value = true;
}

function onConfirmed() {
    pendingConfirmAction?.();
    pendingConfirmAction = null;
}

function handleRemoveOverride(featureId: string) {
    requestConfirm('Bu override\'ı kaldırmak istediğinize emin misiniz?', () => {
        router.delete(destroy({ tenant: props.tenant.id, feature: featureId }).url, { preserveScroll: true });
    });
}

function handleClearAll() {
    requestConfirm('Tüm override\'ları kaldırmak istediğinize emin misiniz?', () => {
        router.post(clear(props.tenant.id).url, {}, { preserveScroll: true });
    });
}

function formatValue(value: any, type: string): string {
    if (value === null || value === undefined) return 'Sınırsız';
    if (type === 'feature') return value ? 'Evet' : 'Hayır';
    return String(value);
}
</script>

<template>
    <Head title="Özellikler" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <TenantLayout
            :tenant-id="tenant.id"
            :tenant-name="tenant.name"
            :tenant-code="tenant.code"
            :tenant-slug="tenant.slug"
        >
            <!-- Actions -->
            <div class="flex items-center gap-2">
                <Button size="sm" @click="showAddDialog = true">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Override Ekle
                </Button>
                <Button
                    v-if="overrides.length > 0"
                    variant="outline"
                    size="sm"
                    @click="handleClearAll"
                >
                    <Trash2 class="mr-1.5 h-4 w-4" />
                    Tümünü Temizle
                </Button>
            </div>

            <!-- Effective Features -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Efektif Özellikler</CardTitle>
                    <CardDescription>Plan, override ve eklentilerden birleştirilmiş özellik listesi</CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <Table v-if="effectiveFeatures.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Özellik</TableHead>
                                <TableHead>Tip</TableHead>
                                <TableHead>Değer</TableHead>
                                <TableHead>Kaynak</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="feat in effectiveFeatures" :key="feat.id">
                                <TableCell>
                                    <div>
                                        <p class="font-medium">{{ feat.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ feat.slug }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant="outline"
                                        :class="[typeColor(feat.type).bg, typeColor(feat.type).text]"
                                    >
                                        {{ typeLabel(feat.type) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm font-medium">
                                    {{ formatValue(feat.value, feat.type) }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        variant="outline"
                                        :class="[sourceColor(feat.source).bg, sourceColor(feat.source).text]"
                                    >
                                        {{ sourceLabel(feat.source) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button
                                        v-if="feat.source === 'override'"
                                        variant="ghost"
                                        size="sm"
                                        @click="handleRemoveOverride(feat.id)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <EmptyState v-else :icon="SlidersHorizontal" message="Özellik bulunamadı" />
                </CardContent>
            </Card>
        </TenantLayout>

        <!-- Add Override Dialog -->
        <Dialog v-model:open="showAddDialog">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Override Ekle</DialogTitle>
                    <DialogDescription>Bu müşteri için özellik değerlerini geçersiz kılın.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitSync" class="space-y-4">
                    <div v-for="(row, idx) in addForm.overrides" :key="idx" class="flex items-end gap-2">
                        <div class="flex-1 grid gap-2">
                            <Label v-if="idx === 0">Özellik</Label>
                            <Select v-model="row.feature_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Seçin" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="f in allFeatures"
                                        :key="f.id"
                                        :value="f.id"
                                    >
                                        {{ f.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="w-28 grid gap-2">
                            <Label v-if="idx === 0">Değer</Label>
                            <Input v-model="row.value" placeholder="Değer" />
                        </div>
                        <Button
                            v-if="addForm.overrides.length > 1"
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="removeOverrideRow(idx)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                    <Button type="button" variant="outline" size="sm" @click="addOverrideRow">
                        <Plus class="mr-1.5 h-4 w-4" />
                        Satır Ekle
                    </Button>
                    <InputError :message="addForm.errors.overrides" />
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAddDialog = false"><X class="mr-1.5 h-4 w-4" />İptal</Button>
                        <Button type="submit" :disabled="addForm.processing"><Save class="mr-1.5 h-4 w-4" />Kaydet</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
        <ConfirmDialog v-model="showConfirm" :description="confirmDescription" @confirm="onConfirmed" />
    </PanelLayout>
</template>
