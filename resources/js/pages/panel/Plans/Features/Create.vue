<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, X } from 'lucide-vue-next';
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
import { index, store } from '@/routes/panel/plans/features';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planlar', href: '/panel/plans' },
    { title: 'Özellikler', href: index().url },
    { title: 'Yeni Özellik', href: '#' },
];

const form = useForm({
    name: '',
    code: '',
    description: '',
    type: 'feature',
    unit: '',
    reset_period: '',
    is_active: true,
});

function submit() {
    form.post(store().url);
}

const needsUnit = () => form.type === 'limit' || form.type === 'metered';
const needsResetPeriod = () => form.type === 'metered';
</script>

<template>
    <Head title="Yeni Özellik" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="index().url">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold">Yeni Özellik</h1>
                    <p class="text-sm text-muted-foreground">Plan özellikleri tanımlayın</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Temel Bilgiler</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="name">Özellik Adı</Label>
                            <Input id="name" v-model="form.name" placeholder="Örn: Kullanıcı Sayısı" />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="code">Kod</Label>
                            <Input id="code" v-model="form.code" placeholder="Örn: max_users" />
                            <p class="text-xs text-muted-foreground">Sistem içinde kullanılacak benzersiz tanımlayıcı</p>
                            <InputError :message="form.errors.code" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Açıklama</Label>
                            <Textarea id="description" v-model="form.description" rows="2" placeholder="Özellik açıklaması" />
                            <InputError :message="form.errors.description" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Tip ve Ayarlar</CardTitle>
                        <CardDescription>Özelliğin nasıl çalışacağını belirleyin</CardDescription>
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
                        <Plus class="mr-1.5 h-4 w-4" />
                        Özellik Oluştur
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="index().url">
                            <X class="mr-1.5 h-4 w-4" />
                            İptal
                        </Link>
                    </Button>
                </div>
            </form>
        </div>
    </PanelLayout>
</template>
