<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    Eye,
    EyeOff,
    Plus,
    Trash2,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
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
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
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
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { formatCurrency } from '@/composables/useFormatting';
import { useFeatureType } from '@/composables/useFeatureType';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, update, publish, unpublish } from '@/routes/panel/plans';
import { store as storePrice, update as updatePrice, destroy as destroyPrice } from '@/routes/panel/plans/prices';
import { sync as syncFeatures } from '@/routes/panel/plans/plan-features';
import type { BreadcrumbItem } from '@/types';

type PlanInterval = {
    value: string;
    label?: string;
};

type PlanPrice = {
    id: string;
    price: number;
    currency: string;
    interval: string;
    interval_count: number;
    trial_days: number | null;
};

type Feature = {
    id: string;
    name: string;
    code: string;
    type: string;
    unit: string | null;
};

type EnabledFeature = {
    feature_id: string;
    value: string | null;
};

type TenantOption = {
    id: string;
    name: string;
};

type TenantDisplay = {
    id: string;
    name: string;
    subscription_status?: string;
};

type Plan = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    tenant_id: string | null;
    is_free: boolean;
    is_active: boolean;
    is_public: boolean;
    grace_period_days: number;
    upgrade_proration_type: string | null;
    downgrade_proration_type: string | null;
};

type Props = {
    plan: Plan;
    features: Feature[];
    prices: PlanPrice[];
    enabledFeatures: EnabledFeature[];
    tenants: TenantOption[];
    intervalCases: PlanInterval[];
    planTenantsCount: number;
    tenantList: TenantDisplay[];
};

const props = defineProps<Props>();

const { typeLabel, typeColor } = useFeatureType();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planlar', href: index().url },
    { title: props.plan.name, href: '#' },
];

// Plan update form
const form = useForm({
    name: props.plan.name,
    slug: props.plan.slug,
    description: props.plan.description ?? '',
    tenant_id: props.plan.tenant_id ?? '',
    is_free: props.plan.is_free,
    is_active: props.plan.is_active,
    is_public: props.plan.is_public,
    grace_period_days: props.plan.grace_period_days,
    upgrade_proration_type: props.plan.upgrade_proration_type ?? '',
    downgrade_proration_type: props.plan.downgrade_proration_type ?? '',
});

function submitPlan() {
    form.put(update(props.plan.id).url);
}

// Price form
const priceDialogOpen = ref(false);
const priceForm = useForm({
    price: 0,
    currency: 'TRY',
    interval: 'month',
    interval_count: 1,
    trial_days: 0,
});

function submitPrice() {
    priceForm.post(storePrice(props.plan.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            priceDialogOpen.value = false;
            priceForm.reset();
        },
    });
}

function handleDeletePrice(price: PlanPrice) {
    requestConfirm(() => {
        router.delete(destroyPrice({ plan: props.plan.id, price: price.id }).url, {
            preserveScroll: true,
        });
    });
}

// Features sync
const featureStates = ref<Record<string, { enabled: boolean; value: string }>>(
    Object.fromEntries(
        props.features.map((f) => {
            const enabled = props.enabledFeatures.find((ef) => ef.feature_id === f.id);
            return [f.id, { enabled: !!enabled, value: enabled?.value ?? '' }];
        }),
    ),
);

function submitFeatures() {
    const features = Object.entries(featureStates.value)
        .filter(([, state]) => state.enabled)
        .map(([featureId, state]) => ({
            feature_id: featureId,
            value: state.value || null,
        }));

    router.put(syncFeatures(props.plan.id).url, { features } as any, {
        preserveScroll: true,
    });
}

// Publish/Unpublish
function handlePublish() {
    router.post(publish(props.plan.id).url, {}, { preserveScroll: true });
}

function handleUnpublish() {
    router.post(unpublish(props.plan.id).url, {}, { preserveScroll: true });
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

const intervalLabels: Record<string, string> = {
    month: 'Aylık',
    year: 'Yıllık',
    day: 'Günlük',
};
</script>

<template>
    <Head :title="plan.name" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="index().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-semibold">{{ plan.name }}</h1>
                            <Badge v-if="plan.is_active" variant="default">Aktif</Badge>
                            <Badge v-else variant="secondary">Pasif</Badge>
                            <Badge v-if="plan.is_public" variant="outline">
                                <Eye class="mr-1 h-3 w-3" /> Herkese Açık
                            </Badge>
                            <Badge v-else variant="outline">
                                <EyeOff class="mr-1 h-3 w-3" /> Özel
                            </Badge>
                        </div>
                        <p class="text-sm text-muted-foreground">{{ plan.slug }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="!plan.is_public"
                        size="sm"
                        @click="handlePublish"
                    >
                        <Eye class="mr-1.5 h-4 w-4" /> Yayınla
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        size="sm"
                        @click="handleUnpublish"
                    >
                        <EyeOff class="mr-1.5 h-4 w-4" /> Yayından Kaldır
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left: Plan details + transition settings -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Plan Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Temel Bilgiler</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submitPlan" class="space-y-4">
                                <div class="grid gap-2">
                                    <Label for="name">Plan Adı</Label>
                                    <Input id="name" v-model="form.name" />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="slug">Slug</Label>
                                    <Input id="slug" v-model="form.slug" />
                                    <InputError :message="form.errors.slug" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="description">Açıklama</Label>
                                    <Textarea id="description" v-model="form.description" rows="3" />
                                    <InputError :message="form.errors.description" />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="grace_period_days">Ek Ödeme Süresi (gün)</Label>
                                        <Input
                                            id="grace_period_days"
                                            type="number"
                                            v-model.number="form.grace_period_days"
                                            min="0"
                                            max="30"
                                        />
                                        <InputError :message="form.errors.grace_period_days" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="tenant_id">Özel Hesap</Label>
                                        <Select v-model="form.tenant_id">
                                            <SelectTrigger id="tenant_id">
                                                <SelectValue placeholder="Tüm hesaplar" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                                                    {{ tenant.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="form.errors.tenant_id" />
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="grid gap-2">
                                        <Label for="upgrade_proration_type">Yükseltme Geçiş Tipi</Label>
                                        <Select v-model="form.upgrade_proration_type">
                                            <SelectTrigger id="upgrade_proration_type">
                                                <SelectValue placeholder="Varsayılan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="immediate">Anında Geçiş</SelectItem>
                                                <SelectItem value="end_of_period">Dönem Sonunda Geçiş</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="form.errors.upgrade_proration_type" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="downgrade_proration_type">Düşürme Geçiş Tipi</Label>
                                        <Select v-model="form.downgrade_proration_type">
                                            <SelectTrigger id="downgrade_proration_type">
                                                <SelectValue placeholder="Varsayılan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="immediate">Anında Geçiş</SelectItem>
                                                <SelectItem value="end_of_period">Dönem Sonunda Geçiş</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError :message="form.errors.downgrade_proration_type" />
                                    </div>
                                </div>

                                <Separator />

                                <div class="flex flex-wrap gap-6">
                                    <div class="flex items-center gap-2">
                                        <Switch v-model:checked="form.is_active" />
                                        <Label>Aktif</Label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Switch v-model:checked="form.is_public" />
                                        <Label>Herkese Açık</Label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Switch v-model:checked="form.is_free" />
                                        <Label>Ücretsiz</Label>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <Button type="submit" :disabled="form.processing">Planı Güncelle</Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>

                    <!-- Prices -->
                    <Card>
                        <CardHeader class="flex-row items-center justify-between space-y-0">
                            <div>
                                <CardTitle>Fiyatlar</CardTitle>
                                <CardDescription>Plan fiyatlandırma seçenekleri</CardDescription>
                            </div>
                            <Dialog v-model:open="priceDialogOpen">
                                <DialogTrigger as-child>
                                    <Button size="sm">
                                        <Plus class="mr-1.5 h-4 w-4" /> Fiyat Ekle
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Yeni Fiyat</DialogTitle>
                                        <DialogDescription>Bu plana yeni bir fiyat seçeneği ekleyin</DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="submitPrice" class="space-y-4">
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="grid gap-2">
                                                <Label for="price">Fiyat (kuruş)</Label>
                                                <Input
                                                    id="price"
                                                    type="number"
                                                    v-model.number="priceForm.price"
                                                    min="0"
                                                />
                                                <p class="text-xs text-muted-foreground">100 = 1,00 TL</p>
                                                <InputError :message="priceForm.errors.price" />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label for="interval">Periyot</Label>
                                                <Select v-model="priceForm.interval">
                                                    <SelectTrigger id="interval">
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem v-for="ic in intervalCases" :key="ic.value" :value="ic.value">
                                                            {{ intervalLabels[ic.value] ?? ic.value }}
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                <InputError :message="priceForm.errors.interval" />
                                            </div>
                                        </div>

                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="grid gap-2">
                                                <Label for="interval_count">Periyot Sayısı</Label>
                                                <Input
                                                    id="interval_count"
                                                    type="number"
                                                    v-model.number="priceForm.interval_count"
                                                    min="1"
                                                    max="12"
                                                />
                                                <InputError :message="priceForm.errors.interval_count" />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label for="trial_days">Deneme Süresi (gün)</Label>
                                                <Input
                                                    id="trial_days"
                                                    type="number"
                                                    v-model.number="priceForm.trial_days"
                                                    min="0"
                                                    max="365"
                                                />
                                                <InputError :message="priceForm.errors.trial_days" />
                                            </div>
                                        </div>

                                        <DialogFooter>
                                            <DialogClose as-child>
                                                <Button variant="outline" type="button">İptal</Button>
                                            </DialogClose>
                                            <Button type="submit" :disabled="priceForm.processing">Ekle</Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </CardHeader>
                        <CardContent class="p-0">
                            <Table v-if="prices.length > 0">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Fiyat</TableHead>
                                        <TableHead>Periyot</TableHead>
                                        <TableHead>Deneme</TableHead>
                                        <TableHead class="text-right">İşlem</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="price in prices" :key="price.id">
                                        <TableCell class="font-medium">
                                            {{ formatCurrency(price.price, price.currency) }}
                                        </TableCell>
                                        <TableCell>
                                            {{ price.interval_count > 1 ? `${price.interval_count} ` : '' }}{{ intervalLabels[price.interval] ?? price.interval }}
                                        </TableCell>
                                        <TableCell>
                                            {{ price.trial_days ? `${price.trial_days} gün` : '-' }}
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                @click="handleDeletePrice(price)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                                <p class="text-sm text-muted-foreground">Henüz fiyat eklenmemiş</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Features -->
                    <Card>
                        <CardHeader class="flex-row items-center justify-between space-y-0">
                            <div>
                                <CardTitle>Özellikler</CardTitle>
                                <CardDescription>Bu plana dahil olan özellikleri seçin</CardDescription>
                            </div>
                            <Button size="sm" @click="submitFeatures">
                                <Check class="mr-1.5 h-4 w-4" /> Kaydet
                            </Button>
                        </CardHeader>
                        <CardContent class="p-0">
                            <Table v-if="features.length > 0">
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-10"></TableHead>
                                        <TableHead>Özellik</TableHead>
                                        <TableHead>Tip</TableHead>
                                        <TableHead>Değer</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="feature in features" :key="feature.id">
                                        <TableCell>
                                            <Checkbox
                                                :checked="featureStates[feature.id]?.enabled"
                                                @update:checked="(val: boolean) => featureStates[feature.id].enabled = val"
                                            />
                                        </TableCell>
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
                                        <TableCell>
                                            <Input
                                                v-if="featureStates[feature.id]?.enabled && feature.type !== 'feature'"
                                                v-model="featureStates[feature.id].value"
                                                class="h-8 w-32"
                                                :placeholder="feature.unit ?? 'Değer'"
                                            />
                                            <span v-else-if="featureStates[feature.id]?.enabled" class="text-sm text-muted-foreground">
                                                Açık
                                            </span>
                                            <span v-else class="text-sm text-muted-foreground">-</span>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                            <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                                <p class="text-sm text-muted-foreground">Sistemde tanımlı özellik yok</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right sidebar -->
                <div class="space-y-6">
                    <!-- Plan using tenants -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Users class="h-4 w-4" />
                                Kullanan Hesaplar
                            </CardTitle>
                            <CardDescription>Bu planı kullanan {{ planTenantsCount }} hesap var</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="tenantList.length > 0" class="space-y-2">
                                <div
                                    v-for="tenant in tenantList"
                                    :key="tenant.id"
                                    class="flex items-center justify-between rounded-md border px-3 py-2 text-sm"
                                >
                                    <span class="font-medium">{{ tenant.name }}</span>
                                    <Badge v-if="tenant.subscription_status" variant="outline" class="text-xs">
                                        {{ tenant.subscription_status }}
                                    </Badge>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">Bu planı kullanan hesap yok</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
        <ConfirmDialog v-model="showConfirm" description="Bu fiyatı silmek istediğinize emin misiniz?" @confirm="onConfirmed" />
    </PanelLayout>
</template>
