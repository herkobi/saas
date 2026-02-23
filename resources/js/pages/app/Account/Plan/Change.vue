<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowDown,
    ArrowUp,
    CalendarClock,
    CheckCircle,
    Crown,
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
import { Separator } from '@/components/ui/separator';
import { formatCurrency } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as planChangeIndex, change } from '@/routes/app/account/plans';
import { index as checkoutIndex } from '@/routes/app/account/checkout';
import { cancel as cancelDowngrade } from '@/routes/app/account/downgrade';
import { index as subscriptionIndex } from '@/routes/app/account/subscription';
import type { BreadcrumbItem } from '@/types';

type PlanOption = {
    id: string;
    name: string;
    description?: string;
    prices: {
        id: string;
        price: number;
        currency: string;
        interval: string;
        interval_label: string;
    }[];
    features?: string[];
};

type CurrentSubscription = {
    id: string;
    ends_at: string | null;
    price: {
        id: string;
        price: number;
        currency: string;
        interval: string;
        plan: { id: string; name: string };
    };
    next_price: {
        id: string;
        price: number;
        plan: { name: string };
    } | null;
};

const props = defineProps<{
    currentSubscription?: CurrentSubscription;
    upgradeOptions?: PlanOption[];
    downgradeOptions?: PlanOption[];
    error?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Plan Değiştir', href: planChangeIndex().url },
];

const intervalLabels: Record<string, string> = {
    month: 'Aylık',
    year: 'Yıllık',
    day: 'Günlük',
};

// Change plan
const showChangeDialog = ref(false);
const selectedPriceId = ref<string | null>(null);
const selectedPlanName = ref('');
const isUpgrade = ref(true);
const processing = ref(false);

function selectPlan(priceId: string, planName: string, upgrade: boolean) {
    selectedPriceId.value = priceId;
    selectedPlanName.value = planName;
    isUpgrade.value = upgrade;
    showChangeDialog.value = true;
}

function confirmChange() {
    if (!selectedPriceId.value) return;
    processing.value = true;
    router.post(change().url, {
        plan_price_id: selectedPriceId.value,
    }, {
        onFinish: () => {
            processing.value = false;
            showChangeDialog.value = false;
        },
    });
}

// Cancel downgrade
const cancelingDowngrade = ref(false);

function handleCancelDowngrade() {
    cancelingDowngrade.value = true;
    router.post(cancelDowngrade().url, {}, {
        onFinish: () => {
            cancelingDowngrade.value = false;
        },
    });
}
</script>

<template>
    <Head title="Plan Değiştir" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <!-- Error State -->
            <Card v-if="error" class="border-destructive/50">
                <CardContent class="flex items-center gap-4 py-8">
                    <AlertTriangle class="h-8 w-8 shrink-0 text-destructive" />
                    <div>
                        <p class="font-medium">{{ error }}</p>
                    </div>
                </CardContent>
            </Card>

            <template v-else-if="currentSubscription">
                <!-- Current Plan -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Crown class="h-5 w-5 text-primary" />
                            Mevcut Plan
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-lg font-semibold">{{ currentSubscription.price.plan.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatCurrency(currentSubscription.price.price, currentSubscription.price.currency) }}
                                    / {{ intervalLabels[currentSubscription.price.interval] ?? currentSubscription.price.interval }}
                                </p>
                            </div>
                            <Badge variant="outline">Aktif</Badge>
                        </div>

                        <!-- Scheduled Downgrade -->
                        <div v-if="currentSubscription.next_price" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/50 dark:bg-amber-900/10">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm">
                                    <CalendarClock class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                    <span class="text-amber-800 dark:text-amber-200">
                                        Dönem sonunda <strong>{{ currentSubscription.next_price.plan.name }}</strong> planına geçilecek.
                                    </span>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-amber-700 hover:text-amber-900 dark:text-amber-300"
                                    :disabled="cancelingDowngrade"
                                    @click="handleCancelDowngrade"
                                >
                                    <XCircle class="mr-1 h-4 w-4" />
                                    {{ cancelingDowngrade ? 'İptal ediliyor...' : 'İptal Et' }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Upgrade Options -->
                <div v-if="upgradeOptions && upgradeOptions.length > 0">
                    <h2 class="mb-3 flex items-center gap-2 text-lg font-semibold">
                        <ArrowUp class="h-5 w-5 text-green-600 dark:text-green-400" />
                        Yükseltme Seçenekleri
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="plan in upgradeOptions" :key="plan.id" class="border-green-200 dark:border-green-900/50">
                            <CardHeader class="pb-3">
                                <CardTitle class="text-base">{{ plan.name }}</CardTitle>
                                <CardDescription v-if="plan.description">{{ plan.description }}</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div v-for="price in plan.prices" :key="price.id" class="flex items-center justify-between">
                                    <div>
                                        <span class="text-lg font-bold">{{ formatCurrency(price.price, price.currency) }}</span>
                                        <span class="text-sm text-muted-foreground"> / {{ price.interval_label }}</span>
                                    </div>
                                    <Button size="sm" @click="selectPlan(price.id, plan.name, true)">
                                        <ArrowUp class="mr-1.5 h-4 w-4" />
                                        Yükselt
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Downgrade Options -->
                <div v-if="downgradeOptions && downgradeOptions.length > 0">
                    <h2 class="mb-3 flex items-center gap-2 text-lg font-semibold">
                        <ArrowDown class="h-5 w-5 text-muted-foreground" />
                        Düşürme Seçenekleri
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="plan in downgradeOptions" :key="plan.id">
                            <CardHeader class="pb-3">
                                <CardTitle class="text-base">{{ plan.name }}</CardTitle>
                                <CardDescription v-if="plan.description">{{ plan.description }}</CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div v-for="price in plan.prices" :key="price.id" class="flex items-center justify-between">
                                    <div>
                                        <span class="text-lg font-bold">{{ formatCurrency(price.price, price.currency) }}</span>
                                        <span class="text-sm text-muted-foreground"> / {{ price.interval_label }}</span>
                                    </div>
                                    <Button variant="outline" size="sm" @click="selectPlan(price.id, plan.name, false)">
                                        <ArrowDown class="mr-1.5 h-4 w-4" />
                                        Düşür
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- No options -->
                <Card v-if="(!upgradeOptions || upgradeOptions.length === 0) && (!downgradeOptions || downgradeOptions.length === 0)">
                    <CardContent class="flex flex-col items-center gap-2 py-12 text-center">
                        <CheckCircle class="h-10 w-10 text-muted-foreground" />
                        <p class="text-muted-foreground">Şu anda değiştirilebilecek başka plan bulunmuyor.</p>
                    </CardContent>
                </Card>
            </template>
        </div>

        </AccountLayout>

        <!-- Confirm Dialog -->
        <Dialog v-model:open="showChangeDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ isUpgrade ? 'Plan Yükseltme' : 'Plan Düşürme' }}</DialogTitle>
                    <DialogDescription>
                        <strong>{{ selectedPlanName }}</strong> planına geçmek istediğinize emin misiniz?
                        <template v-if="isUpgrade">
                            Yükseltme işlemi ödeme sonrası hemen uygulanacaktır.
                        </template>
                        <template v-else>
                            Düşürme işlemi mevcut dönemin sonunda uygulanacaktır.
                        </template>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showChangeDialog = false">Vazgeç</Button>
                    <Button
                        :variant="isUpgrade ? 'default' : 'secondary'"
                        :disabled="processing"
                        @click="confirmChange"
                    >
                        {{ processing ? 'İşleniyor...' : (isUpgrade ? 'Yükselt' : 'Düşür') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
