<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Ban,
    CheckCircle,
    Package,
    Plus,
    ShoppingCart,
    XCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';
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
import { Separator } from '@/components/ui/separator';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as addonsIndex, purchase, cancel } from '@/routes/app/account/addons';
import type { BreadcrumbItem } from '@/types';
import type { Addon } from '@/types/billing';

type CurrentAddon = Addon & {
    pivot: {
        quantity: number;
        is_active: boolean;
        started_at: string;
        expires_at: string | null;
    };
    feature: { name: string } | null;
};

const props = defineProps<{
    availableAddons: Addon[];
    currentAddons: CurrentAddon[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Eklentiler', href: addonsIndex().url },
];

const addonTypeLabels: Record<string, string> = {
    increment: 'Artırım',
    unlimited: 'Sınırsız',
    boolean: 'Özellik',
};

// Purchase
const showPurchaseDialog = ref(false);
const selectedAddon = ref<Addon | null>(null);
const quantity = ref(1);
const purchasing = ref(false);

function openPurchaseDialog(addon: Addon) {
    selectedAddon.value = addon;
    quantity.value = 1;
    showPurchaseDialog.value = true;
}

function confirmPurchase() {
    if (!selectedAddon.value) return;
    purchasing.value = true;
    router.post(purchase().url, {
        addon_id: selectedAddon.value.id,
        quantity: quantity.value,
    }, {
        onFinish: () => {
            purchasing.value = false;
            showPurchaseDialog.value = false;
        },
    });
}

// Cancel
const showCancelDialog = ref(false);
const cancelAddonId = ref<string | null>(null);
const canceling = ref(false);

function openCancelDialog(addonId: string) {
    cancelAddonId.value = addonId;
    showCancelDialog.value = true;
}

function confirmCancel() {
    if (!cancelAddonId.value) return;
    canceling.value = true;
    router.post(cancel(cancelAddonId.value).url, {}, {
        onFinish: () => {
            canceling.value = false;
            showCancelDialog.value = false;
        },
    });
}
</script>

<template>
    <Head title="Eklentiler" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <!-- Active Addons -->
            <div v-if="currentAddons.length > 0">
                <h2 class="mb-3 text-lg font-semibold">Aktif Eklentiler</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="addon in currentAddons" :key="addon.id">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base">{{ addon.name }}</CardTitle>
                                <Badge variant="default">
                                    <CheckCircle class="mr-1 h-3 w-3" />
                                    Aktif
                                </Badge>
                            </div>
                            <CardDescription v-if="addon.description">{{ addon.description }}</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="text-sm space-y-1">
                                <div v-if="addon.feature" class="flex justify-between">
                                    <span class="text-muted-foreground">Özellik</span>
                                    <span class="font-medium">{{ addon.feature.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Tür</span>
                                    <Badge variant="outline" class="text-xs">{{ addonTypeLabels[addon.addon_type] ?? addon.addon_type }}</Badge>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Miktar</span>
                                    <span class="font-medium">{{ addon.pivot.quantity }}</span>
                                </div>
                                <div v-if="addon.pivot.expires_at" class="flex justify-between">
                                    <span class="text-muted-foreground">Bitiş</span>
                                    <span>{{ formatDate(addon.pivot.expires_at) }}</span>
                                </div>
                            </div>
                            <Separator />
                            <Button
                                variant="ghost"
                                size="sm"
                                class="w-full text-destructive hover:text-destructive"
                                @click="openCancelDialog(addon.id)"
                            >
                                <XCircle class="mr-2 h-4 w-4" />
                                İptal Et
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Available Addons -->
            <div>
                <h2 class="mb-3 text-lg font-semibold">Mevcut Eklentiler</h2>
                <div v-if="availableAddons.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="addon in availableAddons" :key="addon.id">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-base">{{ addon.name }}</CardTitle>
                                <Badge variant="outline" class="text-xs">{{ addonTypeLabels[addon.addon_type] ?? addon.addon_type }}</Badge>
                            </div>
                            <CardDescription v-if="addon.description">{{ addon.description }}</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="text-sm space-y-1">
                                <div v-if="addon.feature" class="flex justify-between">
                                    <span class="text-muted-foreground">Özellik</span>
                                    <span class="font-medium">{{ addon.feature.name }}</span>
                                </div>
                                <div v-if="addon.value" class="flex justify-between">
                                    <span class="text-muted-foreground">Değer</span>
                                    <span class="font-medium">{{ addon.value }}</span>
                                </div>
                                <div v-if="addon.is_recurring" class="flex justify-between">
                                    <span class="text-muted-foreground">Periyot</span>
                                    <span class="font-medium">{{ addon.interval === 'month' ? 'Aylık' : addon.interval === 'year' ? 'Yıllık' : 'Günlük' }}</span>
                                </div>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold">{{ formatCurrency(addon.price, addon.currency) }}</span>
                                <Button size="sm" @click="openPurchaseDialog(addon)">
                                    <ShoppingCart class="mr-2 h-4 w-4" />
                                    Satın Al
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <Card v-else>
                    <CardContent class="flex flex-col items-center gap-2 py-12 text-center">
                        <Package class="h-10 w-10 text-muted-foreground" />
                        <p class="text-muted-foreground">Şu anda satın alınabilecek eklenti bulunmuyor.</p>
                    </CardContent>
                </Card>
            </div>
        </div>

        </AccountLayout>

        <!-- Purchase Dialog -->
        <Dialog v-model:open="showPurchaseDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Eklenti Satın Al</DialogTitle>
                    <DialogDescription v-if="selectedAddon">
                        <strong>{{ selectedAddon.name }}</strong> eklentisini satın almak üzeresiniz.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="selectedAddon" class="space-y-4 py-2">
                    <div v-if="selectedAddon.addon_type === 'increment'" class="space-y-2">
                        <Label for="quantity">Miktar</Label>
                        <Input id="quantity" v-model.number="quantity" type="number" min="1" />
                    </div>
                    <div class="rounded-lg border p-3 text-sm space-y-1">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Birim Fiyat</span>
                            <span>{{ formatCurrency(selectedAddon.price, selectedAddon.currency) }}</span>
                        </div>
                        <div v-if="selectedAddon.addon_type === 'increment'" class="flex justify-between">
                            <span class="text-muted-foreground">Miktar</span>
                            <span>{{ quantity }}</span>
                        </div>
                        <Separator />
                        <div class="flex justify-between font-medium">
                            <span>Toplam</span>
                            <span>{{ formatCurrency(selectedAddon.price * quantity, selectedAddon.currency) }}</span>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showPurchaseDialog = false">Vazgeç</Button>
                    <Button :disabled="purchasing" @click="confirmPurchase">
                        {{ purchasing ? 'İşleniyor...' : 'Satın Al' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Cancel Dialog -->
        <Dialog v-model:open="showCancelDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Eklenti İptal</DialogTitle>
                    <DialogDescription>
                        Bu eklentiyi iptal etmek istediğinize emin misiniz?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showCancelDialog = false">Vazgeç</Button>
                    <Button variant="destructive" :disabled="canceling" @click="confirmCancel">
                        {{ canceling ? 'İptal ediliyor...' : 'Evet, İptal Et' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
