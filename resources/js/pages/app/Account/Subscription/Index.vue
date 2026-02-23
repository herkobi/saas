<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowUpRight,
    CalendarClock,
    CheckCircle,
    Crown,
    Shield,
    XCircle,
    Zap,
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
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import { useSubscriptionStatus } from '@/composables/useSubscriptionStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as subscriptionIndex, cancel } from '@/routes/app/account/subscription';
import { index as planChangeIndex } from '@/routes/app/account/plans';
import { index as addonsIndex } from '@/routes/app/account/addons';
import type { BreadcrumbItem } from '@/types';

type FeatureUsage =
    | { type: 'boolean'; enabled: boolean }
    | { type: 'numeric'; limit: number | null; used: number; remaining: number | null; percentage: number; is_unlimited: boolean; reset_at: string | null };

type SubscriptionFeature = {
    id: string;
    name: string;
    slug: string;
    description: string;
    type: string;
    type_label: string;
    unit: string | null;
    reset_period: string | null;
    plan_value: string | number;
    effective_value: string;
    has_override: boolean;
    usage: FeatureUsage;
};

type SubscriptionDetail = {
    id: string;
    status: string;
    status_label: string;
    status_badge: string;
    plan: { id: string; name: string; description?: string };
    price: { id: string; amount: number; currency: string; interval: string; interval_label: string };
    starts_at: string;
    ends_at: string | null;
    trial_ends_at: string | null;
    canceled_at: string | null;
    grace_period_ends_at: string | null;
    next_plan?: { name: string; price: number } | null;
};

type AvailablePlan = {
    id: string;
    name: string;
    description?: string;
    prices: { id: string; price: number; currency: string; interval: string; interval_label: string }[];
};

const props = defineProps<{
    subscription: SubscriptionDetail | null;
    features: SubscriptionFeature[];
    availablePlans: AvailablePlan[];
    canUpgrade: boolean;
    canDowngrade: boolean;
}>();

const { getStatusLabel, getStatusVariant } = useSubscriptionStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Abonelik', href: subscriptionIndex().url },
];

const showCancelDialog = ref(false);
const canceling = ref(false);

function confirmCancel() {
    canceling.value = true;
    router.post(cancel().url, {}, {
        onFinish: () => {
            canceling.value = false;
            showCancelDialog.value = false;
        },
    });
}

const numericFeatures = props.features.filter(
    (f): f is SubscriptionFeature & { usage: Extract<FeatureUsage, { type: 'numeric' }> } =>
        f.usage.type === 'numeric',
);

const booleanFeatures = props.features.filter(
    (f): f is SubscriptionFeature & { usage: Extract<FeatureUsage, { type: 'boolean' }> } =>
        f.usage.type === 'boolean',
);
</script>

<template>
    <Head title="Abonelik" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <!-- No Subscription -->
            <Card v-if="!subscription" class="border-amber-200 dark:border-amber-900/50">
                <CardContent class="flex flex-col items-center gap-4 py-12 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <AlertTriangle class="h-7 w-7 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Aktif Abonelik Bulunamadı</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Platformu kullanmak için bir plan seçmeniz gerekiyor.
                        </p>
                    </div>
                    <Button v-if="canUpgrade" as-child>
                        <Link :href="planChangeIndex().url">
                            <Crown class="mr-2 h-4 w-4" />
                            Plan Seç
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            <template v-else>
                <!-- Subscription Info -->
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2 flex flex-col gap-4">
                        <!-- Current Plan Card -->
                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle class="flex items-center gap-2">
                                            <Crown class="h-5 w-5 text-primary" />
                                            {{ subscription.plan.name }}
                                        </CardTitle>
                                        <CardDescription v-if="subscription.plan.description">
                                            {{ subscription.plan.description }}
                                        </CardDescription>
                                    </div>
                                    <Badge :variant="getStatusVariant(subscription.status)">
                                        {{ subscription.status_label }}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Fiyat</p>
                                        <p class="text-lg font-semibold">
                                            {{ formatCurrency(subscription.price.amount, subscription.price.currency) }}
                                            <span class="text-sm font-normal text-muted-foreground">/ {{ subscription.price.interval_label }}</span>
                                        </p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Başlangıç</p>
                                        <p class="font-medium">{{ formatDate(subscription.starts_at) }}</p>
                                    </div>
                                    <div v-if="subscription.ends_at" class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Bitiş</p>
                                        <p class="font-medium">{{ formatDate(subscription.ends_at) }}</p>
                                    </div>
                                    <div v-if="subscription.trial_ends_at" class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Deneme Süresi Bitiş</p>
                                        <p class="font-medium">{{ formatDate(subscription.trial_ends_at) }}</p>
                                    </div>
                                    <div v-if="subscription.grace_period_ends_at" class="space-y-1">
                                        <p class="text-sm text-muted-foreground">Ek Süre Bitiş</p>
                                        <p class="font-medium">{{ formatDate(subscription.grace_period_ends_at) }}</p>
                                    </div>
                                    <div v-if="subscription.canceled_at" class="space-y-1">
                                        <p class="text-sm text-muted-foreground">İptal Tarihi</p>
                                        <p class="font-medium text-destructive">{{ formatDate(subscription.canceled_at) }}</p>
                                    </div>
                                </div>

                                <!-- Scheduled Downgrade -->
                                <div v-if="subscription.next_plan" class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/50 dark:bg-amber-900/10">
                                    <div class="flex items-center gap-2 text-sm">
                                        <CalendarClock class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                        <span class="text-amber-800 dark:text-amber-200">
                                            Dönem sonunda <strong>{{ subscription.next_plan.name }}</strong> planına geçilecek.
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Feature Usage -->
                        <Card v-if="numericFeatures.length > 0">
                            <CardHeader>
                                <CardTitle class="text-base">Kullanım Durumu</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-5">
                                    <div v-for="feature in numericFeatures" :key="feature.id">
                                        <div class="mb-1.5 flex items-center justify-between text-sm">
                                            <span class="font-medium">{{ feature.name }}</span>
                                            <span class="text-muted-foreground">
                                                <template v-if="feature.usage.is_unlimited">
                                                    {{ feature.usage.used }} / Sınırsız
                                                </template>
                                                <template v-else>
                                                    {{ feature.usage.used }} / {{ feature.usage.limit }}
                                                    <span v-if="feature.unit" class="ml-0.5">{{ feature.unit }}</span>
                                                </template>
                                            </span>
                                        </div>
                                        <Progress
                                            :model-value="feature.usage.is_unlimited ? 0 : feature.usage.percentage"
                                            class="h-2"
                                        />
                                        <p v-if="feature.usage.reset_at" class="mt-1 text-xs text-muted-foreground">
                                            Sıfırlanma: {{ formatDate(feature.usage.reset_at) }}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Boolean Features -->
                        <Card v-if="booleanFeatures.length > 0">
                            <CardHeader>
                                <CardTitle class="text-base">Özellikler</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <div
                                        v-for="feature in booleanFeatures"
                                        :key="feature.id"
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <div class="flex items-center gap-2">
                                            <CheckCircle v-if="feature.usage.enabled" class="h-4 w-4 text-green-600 dark:text-green-400" />
                                            <XCircle v-else class="h-4 w-4 text-muted-foreground" />
                                            <span>{{ feature.name }}</span>
                                        </div>
                                        <Badge :variant="feature.usage.enabled ? 'default' : 'secondary'">
                                            {{ feature.usage.enabled ? 'Aktif' : 'Pasif' }}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="flex flex-col gap-4">
                        <!-- Quick Actions -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">İşlemler</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-2">
                                <Button v-if="canUpgrade" as-child variant="default" class="w-full">
                                    <Link :href="planChangeIndex().url">
                                        <Zap class="mr-2 h-4 w-4" />
                                        Plan Değiştir
                                    </Link>
                                </Button>
                                <Button as-child variant="outline" class="w-full">
                                    <Link :href="addonsIndex().url">
                                        <ArrowUpRight class="mr-2 h-4 w-4" />
                                        Eklentiler
                                    </Link>
                                </Button>
                                <Separator />
                                <Button
                                    v-if="subscription.status !== 'canceled' && subscription.status !== 'expired'"
                                    variant="ghost"
                                    class="w-full text-destructive hover:text-destructive"
                                    @click="showCancelDialog = true"
                                >
                                    <XCircle class="mr-2 h-4 w-4" />
                                    Aboneliği İptal Et
                                </Button>
                            </CardContent>
                        </Card>

                        <!-- Plan Summary -->
                        <Card>
                            <CardHeader class="pb-3">
                                <div class="flex items-center gap-2">
                                    <Shield class="h-4 w-4 text-muted-foreground" />
                                    <CardTitle class="text-sm font-medium">Plan Özeti</CardTitle>
                                </div>
                            </CardHeader>
                            <CardContent class="text-sm space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Plan</span>
                                    <span class="font-medium">{{ subscription.plan.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Periyot</span>
                                    <span class="font-medium">{{ subscription.price.interval_label }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-muted-foreground">Durum</span>
                                    <Badge :variant="getStatusVariant(subscription.status)" class="text-xs">
                                        {{ subscription.status_label }}
                                    </Badge>
                                </div>
                                <Separator />
                                <div class="flex justify-between font-medium">
                                    <span>Tutar</span>
                                    <span>{{ formatCurrency(subscription.price.amount, subscription.price.currency) }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </template>
        </div>

        </AccountLayout>

        <!-- Cancel Dialog -->
        <Dialog v-model:open="showCancelDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Aboneliği İptal Et</DialogTitle>
                    <DialogDescription>
                        Aboneliğiniz dönem sonuna kadar aktif kalacak, ardından sona erecektir. Bu işlem geri alınabilir.
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
