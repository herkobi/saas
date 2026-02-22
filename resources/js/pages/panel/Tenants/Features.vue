<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, SlidersHorizontal, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
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
import { useTenantTabs } from '@/composables/useTenantTabs';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index } from '@/routes/panel/tenants';
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
const tabs = useTenantTabs(props.tenant.id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: `/panel/tenants/${props.tenant.id}` },
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

function handleRemoveOverride(featureId: string) {
    if (confirm('Bu override\'ı kaldırmak istediğinize emin misiniz?')) {
        router.delete(destroy({ tenant: props.tenant.id, feature: featureId }).url, { preserveScroll: true });
    }
}

function handleClearAll() {
    if (confirm('Tüm override\'ları kaldırmak istediğinize emin misiniz?')) {
        router.post(clear(props.tenant.id).url, {}, { preserveScroll: true });
    }
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
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-lg font-semibold">{{ tenant.name }}</h1>
                <p class="text-sm text-muted-foreground">Özellik yönetimi ve override'lar</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 overflow-x-auto border-b">
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="tab.href === featuresIndex(tenant.id).url
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted-foreground/30 hover:text-foreground'"
                >
                    {{ tab.title }}
                </Link>
            </div>

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

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <SlidersHorizontal class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">Özellik bulunamadı</p>
                    </div>
                </CardContent>
            </Card>
        </div>

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
                        <Button type="button" variant="outline" @click="showAddDialog = false">İptal</Button>
                        <Button type="submit" :disabled="addForm.processing">Kaydet</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </PanelLayout>
</template>
