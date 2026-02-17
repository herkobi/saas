<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/common/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, update } from '@/routes/panel/plans/addons';
import type { BreadcrumbItem } from '@/types';

type FeatureOption = {
    id: string;
    name: string;
    code: string;
    type: string;
};

type Addon = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    feature_id: string;
    addon_type: string;
    value: string | null;
    price: number;
    currency: string;
    is_recurring: boolean;
    interval: string | null;
    interval_count: number | null;
    is_active: boolean;
    is_public: boolean;
};

type Props = {
    addon: Addon;
    features: FeatureOption[];
    allowedAddonTypesByFeatureType: Record<string, string[]>;
    systemCurrency: string;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planlar', href: '/panel/plans' },
    { title: 'Eklentiler', href: index().url },
    { title: props.addon.name, href: '#' },
];

const form = useForm({
    name: props.addon.name,
    slug: props.addon.slug,
    description: props.addon.description ?? '',
    feature_id: props.addon.feature_id,
    addon_type: props.addon.addon_type,
    value: props.addon.value ?? '',
    price: props.addon.price,
    currency: props.addon.currency,
    is_recurring: props.addon.is_recurring,
    interval: props.addon.interval ?? 'month',
    interval_count: props.addon.interval_count ?? 1,
    is_active: props.addon.is_active,
    is_public: props.addon.is_public,
});

const selectedFeature = computed(() =>
    props.features.find((f) => f.id === form.feature_id),
);

const allowedAddonTypes = computed(() => {
    if (!selectedFeature.value) return [];
    return props.allowedAddonTypesByFeatureType[selectedFeature.value.type] ?? [];
});

const addonTypeLabels: Record<string, string> = {
    increment: 'Artırım',
    unlimited: 'Sınırsız',
    boolean: 'Açma/Kapama',
};

function submit() {
    form.put(update(props.addon.id).url);
}
</script>

<template>
    <Head :title="addon.name" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="index().url">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold">{{ addon.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ addon.slug }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Temel Bilgiler</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="name">Eklenti Adı</Label>
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
                            <Textarea id="description" v-model="form.description" rows="2" />
                            <InputError :message="form.errors.description" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Özellik ve Tip</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="feature_id">Özellik</Label>
                            <Select v-model="form.feature_id">
                                <SelectTrigger id="feature_id">
                                    <SelectValue placeholder="Özellik seçin" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="f in features" :key="f.id" :value="f.id">
                                        {{ f.name }} ({{ f.code }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.feature_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="addon_type">Eklenti Tipi</Label>
                            <Select v-model="form.addon_type" :disabled="!form.feature_id">
                                <SelectTrigger id="addon_type">
                                    <SelectValue placeholder="Tip seçin" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="type in allowedAddonTypes" :key="type" :value="type">
                                        {{ addonTypeLabels[type] ?? type }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.addon_type" />
                        </div>

                        <div v-if="form.addon_type === 'increment'" class="grid gap-2">
                            <Label for="value">Artırım Değeri</Label>
                            <Input id="value" v-model="form.value" />
                            <InputError :message="form.errors.value" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Fiyatlandırma</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="price">Fiyat (kuruş)</Label>
                                <Input id="price" type="number" v-model.number="form.price" min="0" />
                                <p class="text-xs text-muted-foreground">100 = 1,00 {{ systemCurrency }}</p>
                                <InputError :message="form.errors.price" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <Label>Tekrarlayan Ödeme</Label>
                                <p class="text-xs text-muted-foreground">Periyodik olarak yenilenir</p>
                            </div>
                            <Switch v-model:checked="form.is_recurring" />
                        </div>

                        <div v-if="form.is_recurring" class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="interval">Periyot</Label>
                                <Select v-model="form.interval">
                                    <SelectTrigger id="interval">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="month">Aylık</SelectItem>
                                        <SelectItem value="year">Yıllık</SelectItem>
                                        <SelectItem value="day">Günlük</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.interval" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="interval_count">Periyot Sayısı</Label>
                                <Input id="interval_count" type="number" v-model.number="form.interval_count" min="1" />
                                <InputError :message="form.errors.interval_count" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Durum</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <Label>Aktif</Label>
                                <p class="text-xs text-muted-foreground">Satın alınabilir</p>
                            </div>
                            <Switch v-model:checked="form.is_active" />
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <Label>Herkese Açık</Label>
                                <p class="text-xs text-muted-foreground">Listede görünür</p>
                            </div>
                            <Switch v-model:checked="form.is_public" />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">Güncelle</Button>
                    <Button variant="outline" as-child>
                        <Link :href="index().url">İptal</Link>
                    </Button>
                </div>
            </form>
        </div>
    </PanelLayout>
</template>
