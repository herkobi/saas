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
import { index, store } from '@/routes/panel/plans';
import type { BreadcrumbItem } from '@/types';

type TenantOption = {
    id: string;
    name: string;
};

type Props = {
    tenants: TenantOption[];
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Planlar', href: index().url },
    { title: 'Yeni Plan', href: '#' },
];

const form = useForm({
    name: '',
    slug: '',
    description: '',
    tenant_id: '',
    is_free: false,
    is_active: true,
    is_public: true,
    grace_period_days: 0,
    upgrade_proration_type: '',
    downgrade_proration_type: '',
});

function submit() {
    form.post(store().url);
}
</script>

<template>
    <Head title="Yeni Plan" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="sm" as-child>
                    <Link :href="index().url">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-lg font-semibold">Yeni Plan</h1>
                    <p class="text-sm text-muted-foreground">Yeni bir abonelik planı oluşturun</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Temel Bilgiler</CardTitle>
                            <CardDescription>Plan adı, slug ve açıklama bilgileri</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="name">Plan Adı</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Örn: Başlangıç, Profesyonel, Kurumsal"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="slug">Slug</Label>
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    placeholder="Boş bırakılırsa otomatik oluşturulur"
                                />
                                <InputError :message="form.errors.slug" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="description">Açıklama</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="Plan hakkında kısa bir açıklama"
                                    rows="3"
                                />
                                <InputError :message="form.errors.description" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Geçiş Ayarları</CardTitle>
                            <CardDescription>Plan yükseltme ve düşürme davranışları</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="grace_period_days">Ek Ödeme Süresi (gün)</Label>
                                <Input
                                    id="grace_period_days"
                                    type="number"
                                    v-model.number="form.grace_period_days"
                                    min="0"
                                    max="30"
                                />
                                <p class="text-xs text-muted-foreground">Abonelik sona erdikten sonra erişime izin verilen gün sayısı (0-30)</p>
                                <InputError :message="form.errors.grace_period_days" />
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
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Durum</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Aktif</Label>
                                    <p class="text-xs text-muted-foreground">Plan satın alınabilir durumda</p>
                                </div>
                                <Switch v-model:checked="form.is_active" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Herkese Açık</Label>
                                    <p class="text-xs text-muted-foreground">Fiyatlandırma sayfasında görünür</p>
                                </div>
                                <Switch v-model:checked="form.is_public" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <Label>Ücretsiz Plan</Label>
                                    <p class="text-xs text-muted-foreground">Ödeme gerektirmez</p>
                                </div>
                                <Switch v-model:checked="form.is_free" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Özel Atama</CardTitle>
                            <CardDescription>Belirli bir hesaba özel plan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-2">
                                <Label for="tenant_id">Hesap</Label>
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
                                <p class="text-xs text-muted-foreground">Boş bırakılırsa tüm hesaplar için geçerli olur</p>
                                <InputError :message="form.errors.tenant_id" />
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex gap-2">
                        <Button type="submit" class="flex-1" :disabled="form.processing">
                            <Plus class="mr-1.5 h-4 w-4" />
                            Plan Oluştur
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="index().url">
                                <X class="mr-1.5 h-4 w-4" />
                                İptal
                            </Link>
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </PanelLayout>
</template>
