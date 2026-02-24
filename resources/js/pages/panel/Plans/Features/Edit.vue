<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, X } from 'lucide-vue-next';
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
import { index, update } from '@/routes/panel/plans/features';
import type { BreadcrumbItem } from '@/types';

type FeaturePlan = {
    id: string;
    name: string;
};

type Feature = {
    id: string;
    name: string;
    code: string;
    description: string | null;
    type: string;
    unit: string | null;
    reset_period: string | null;
    is_active: boolean;
    plans?: FeaturePlan[];
};

type Props = {
    feature: Feature;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planlar', href: '/panel/plans' },
    { title: 'Özellikler', href: index().url },
    { title: props.feature.name, href: '#' },
];

const form = useForm({
    name: props.feature.name,
    code: props.feature.code,
    description: props.feature.description ?? '',
    type: props.feature.type,
    unit: props.feature.unit ?? '',
    reset_period: props.feature.reset_period ?? '',
    is_active: props.feature.is_active,
});

function submit() {
    form.put(update(props.feature.id).url);
}

const needsUnit = () => form.type === 'limit' || form.type === 'metered';
const needsResetPeriod = () => form.type === 'metered';
</script>

<template>
    <Head :title="feature.name" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="index().url">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold">{{ feature.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ feature.code }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Temel Bilgiler</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Özellik Adı</Label>
                                <Input id="name" v-model="form.name" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="code">Kod</Label>
                                <Input id="code" v-model="form.code" />
                                <InputError :message="form.errors.code" />
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
                            <CardTitle>Tip ve Ayarlar</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="type">Tip</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger id="type">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="limit">Sayısal Limit</SelectItem>
                                        <SelectItem value="feature">Özellik (Var/Yok)</SelectItem>
                                        <SelectItem value="metered">Sayaçlı (Sıfırlanan)</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.type" />
                            </div>

                            <div v-if="needsUnit()" class="grid gap-2">
                                <Label for="unit">Birim</Label>
                                <Input id="unit" v-model="form.unit" placeholder="Örn: kullanıcı, GB, adet" />
                                <InputError :message="form.errors.unit" />
                            </div>

                            <div v-if="needsResetPeriod()" class="grid gap-2">
                                <Label for="reset_period">Sıfırlama Periyodu</Label>
                                <Select v-model="form.reset_period">
                                    <SelectTrigger id="reset_period">
                                        <SelectValue placeholder="Seçin" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="daily">Günlük</SelectItem>
                                        <SelectItem value="weekly">Haftalık</SelectItem>
                                        <SelectItem value="monthly">Aylık</SelectItem>
                                        <SelectItem value="yearly">Yıllık</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.reset_period" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Aktif</Label>
                                    <p class="text-xs text-muted-foreground">Planlara atanabilir durumda</p>
                                </div>
                                <Switch v-model:checked="form.is_active" />
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="form.processing">
                            <Save class="mr-1.5 h-4 w-4" />
                            Güncelle
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="index().url">
                                <X class="mr-1.5 h-4 w-4" />
                                İptal
                            </Link>
                        </Button>
                    </div>
                </form>

                <!-- Sidebar: Plans using this feature -->
                <div>
                    <Card>
                        <CardHeader>
                            <CardTitle>Kullanan Planlar</CardTitle>
                            <CardDescription>Bu özelliği içeren planlar</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="feature.plans && feature.plans.length > 0" class="space-y-2">
                                <div
                                    v-for="plan in feature.plans"
                                    :key="plan.id"
                                    class="rounded-md border px-3 py-2 text-sm font-medium"
                                >
                                    {{ plan.name }}
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">Bu özellik henüz bir plana atanmamış</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
