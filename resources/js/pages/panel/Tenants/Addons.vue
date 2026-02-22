<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Calendar, Package, X } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/common/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import { useTenantTabs } from '@/composables/useTenantTabs';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index } from '@/routes/panel/tenants';
import { index as addonsIndex, extend, cancel } from '@/routes/panel/tenants/addons';
import type { BreadcrumbItem } from '@/types';
import type { TenantAddon } from '@/types/billing';
import type { Tenant } from '@/types/tenant';

type Props = {
    tenant: Tenant;
    addons: TenantAddon[];
    activeAddons: TenantAddon[];
};

const props = defineProps<Props>();
const tabs = useTenantTabs(props.tenant.id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: `/panel/tenants/${props.tenant.id}` },
    { title: 'Eklentiler', href: addonsIndex(props.tenant.id).url },
];

const showExtendDialog = ref(false);
const selectedAddonId = ref<string>('');

const extendForm = useForm({
    days: 30,
});

function openExtendDialog(addonId: string) {
    selectedAddonId.value = addonId;
    extendForm.reset();
    showExtendDialog.value = true;
}

function submitExtend() {
    extendForm.post(extend({ tenant: props.tenant.id, addon: selectedAddonId.value }).url, {
        onSuccess: () => { showExtendDialog.value = false; },
    });
}

function handleCancel(addonId: string) {
    if (confirm('Bu eklentiyi iptal etmek istediğinize emin misiniz?')) {
        router.post(cancel({ tenant: props.tenant.id, addon: addonId }).url, {}, { preserveScroll: true });
    }
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
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-lg font-semibold">{{ tenant.name }}</h1>
                <p class="text-sm text-muted-foreground">Eklenti yönetimi</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 overflow-x-auto border-b">
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="tab.href === addonsIndex(tenant.id).url
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted-foreground/30 hover:text-foreground'"
                >
                    {{ tab.title }}
                </Link>
            </div>

            <!-- Active Addons -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Aktif Eklentiler</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <Table v-if="activeAddons.length > 0">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Eklenti</TableHead>
                                <TableHead>Tip</TableHead>
                                <TableHead>Miktar</TableHead>
                                <TableHead>Fiyat</TableHead>
                                <TableHead>Bitiş Tarihi</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead class="text-right">İşlemler</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="ta in activeAddons" :key="ta.id">
                                <TableCell>
                                    <p class="font-medium">{{ ta.addon?.name ?? '—' }}</p>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ addonTypeLabels[ta.addon?.addon_type ?? ''] ?? ta.addon?.addon_type }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm">{{ ta.quantity }}</TableCell>
                                <TableCell class="text-sm">
                                    {{ formatCurrency(ta.custom_price ?? ta.addon?.price ?? 0) }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ ta.expires_at ? formatDate(ta.expires_at) : 'Süresiz' }}
                                </TableCell>
                                <TableCell>
                                    <Badge v-if="ta.is_active" variant="default">Aktif</Badge>
                                    <Badge v-else variant="secondary">Pasif</Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="sm" @click="openExtendDialog(ta.id)">
                                            <Calendar class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="handleCancel(ta.id)">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <Package class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">Aktif eklenti bulunmuyor</p>
                    </div>
                </CardContent>
            </Card>

            <!-- All Addons History -->
            <Card v-if="addons.length > activeAddons.length">
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Tüm Eklentiler</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Eklenti</TableHead>
                                <TableHead>Miktar</TableHead>
                                <TableHead>Başlangıç</TableHead>
                                <TableHead>Bitiş</TableHead>
                                <TableHead>Durum</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="ta in addons" :key="ta.id">
                                <TableCell class="font-medium">{{ ta.addon?.name ?? '—' }}</TableCell>
                                <TableCell class="text-sm">{{ ta.quantity }}</TableCell>
                                <TableCell class="text-sm text-muted-foreground">{{ formatDate(ta.started_at) }}</TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ ta.expires_at ? formatDate(ta.expires_at) : 'Süresiz' }}
                                </TableCell>
                                <TableCell>
                                    <Badge v-if="ta.is_active" variant="default">Aktif</Badge>
                                    <Badge v-else variant="secondary">Pasif</Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Extend Dialog -->
        <Dialog v-model:open="showExtendDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Süre Uzat</DialogTitle>
                    <DialogDescription>Eklentinin bitiş süresini uzatın.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitExtend" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="extend_days">Gün Sayısı</Label>
                        <Input id="extend_days" type="number" v-model.number="extendForm.days" min="1" max="365" />
                        <InputError :message="extendForm.errors.days" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showExtendDialog = false">İptal</Button>
                        <Button type="submit" :disabled="extendForm.processing">Uzat</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </PanelLayout>
</template>
