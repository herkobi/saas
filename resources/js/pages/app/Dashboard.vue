<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    CalendarClock,
    CheckCircle,
    CreditCard,
    Crown,
    Users,
    Zap,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/app';
import type { BreadcrumbItem } from '@/types';

type FeatureUsage =
    | { type: 'boolean'; enabled: boolean }
    | { type: 'numeric'; limit: number | null; used: number; remaining: number | null; percentage: number; is_unlimited: boolean; reset_at: string | null };

type Feature = {
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

type Props = {
    tenant: { id: string; name: string; code: string };
    hasActiveSubscription: boolean;
    subscription: {
        status: { label: string; badge: string };
        plan: { name: string };
        price: { amount: number; currency: string; interval_label: string };
        ends_at: string | null;
    } | null;
    features: Feature[];
    statistics: {
        total_payments: number;
        total_amount: number;
        team_members: number;
    };
    recentPayments: {
        id: string;
        type: string;
        type_label: string;
        description: string;
        amount: number;
        currency: string;
        status: string;
        status_label: string;
        status_badge: string;
        created_at: string;
    }[];
    canUpgrade: boolean;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Başlangıç',
        href: dashboard().url,
    },
];

function paymentStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'completed':
            return 'default';
        case 'pending':
        case 'processing':
            return 'secondary';
        case 'failed':
        case 'cancelled':
            return 'destructive';
        default:
            return 'outline';
    }
}

const numericFeatures = props.features.filter(
    (f): f is Feature & { usage: Extract<FeatureUsage, { type: 'numeric' }> } =>
        f.usage.type === 'numeric',
);

const booleanFeatures = props.features.filter(
    (f): f is Feature & { usage: Extract<FeatureUsage, { type: 'boolean' }> } =>
        f.usage.type === 'boolean',
);
</script>

<template>
    <Head title="Başlangıç" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- No Subscription Alert -->
            <Card v-if="!hasActiveSubscription" class="border-amber-200 dark:border-amber-900/50">
                <CardContent class="flex items-center gap-4 pt-6">
                    <AlertTriangle class="h-8 w-8 shrink-0 text-amber-600 dark:text-amber-400" />
                    <div class="flex-1">
                        <p class="font-medium">Aktif aboneliğiniz bulunmuyor</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Tüm özelliklere erişmek için bir plan seçin.
                        </p>
                    </div>
                    <Button v-if="canUpgrade" size="sm">
                        Plan Seç
                        <ArrowRight class="ml-1.5 h-4 w-4" />
                    </Button>
                </CardContent>
            </Card>

            <!-- Stat Cards -->
            <div v-if="subscription" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Plan</CardTitle>
                        <Crown class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ subscription.plan.name }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatCurrency(subscription.price.amount, subscription.price.currency) }}
                            / {{ subscription.price.interval_label }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Abonelik Durumu</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <Badge variant="outline">{{ subscription.status.label }}</Badge>
                        </div>
                        <p v-if="subscription.ends_at" class="mt-1 text-xs text-muted-foreground">
                            {{ formatDate(subscription.ends_at) }} tarihine kadar
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam Ödeme</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.total_amount) }}</div>
                        <p class="text-xs text-muted-foreground">{{ statistics.total_payments }} ödeme</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Ekip Üyeleri</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.team_members }}</div>
                        <p class="text-xs text-muted-foreground">aktif kullanıcı</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Two Column Layout -->
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Left Column -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <!-- Feature Usage (numeric) -->
                    <Card v-if="numericFeatures.length > 0">
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Kullanım</CardTitle>
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
                                        v-if="!feature.usage.is_unlimited"
                                        :model-value="feature.usage.percentage"
                                        class="h-2"
                                    />
                                    <Progress
                                        v-else
                                        :model-value="0"
                                        class="h-2"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Payments -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Son Ödemeler</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentPayments.length > 0" class="space-y-3">
                                <div
                                    v-for="payment in recentPayments"
                                    :key="payment.id"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <div>
                                        <p class="font-medium">{{ payment.description }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ payment.type_label }} &middot; {{ formatDate(payment.created_at) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Badge :variant="paymentStatusVariant(payment.status)">
                                            {{ payment.status_label }}
                                        </Badge>
                                        <span class="font-medium">
                                            {{ formatCurrency(payment.amount, payment.currency) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">Henüz ödeme bulunmuyor.</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column -->
                <div class="h-fit lg:col-span-1">
                    <!-- Boolean Features -->
                    <Card v-if="booleanFeatures.length > 0">
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Özellikler</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="feature in booleanFeatures"
                                    :key="feature.id"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span>{{ feature.name }}</span>
                                    <Badge :variant="feature.usage.enabled ? 'default' : 'secondary'">
                                        {{ feature.usage.enabled ? 'Aktif' : 'Pasif' }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Subscription End Date -->
                    <Card v-if="subscription?.ends_at">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-2">
                                <CalendarClock class="h-4 w-4 text-muted-foreground" />
                                <CardTitle class="text-sm font-medium">Abonelik Süresi</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-muted-foreground">
                                Aboneliğiniz <span class="font-medium text-foreground">{{ formatDate(subscription.ends_at) }}</span> tarihinde sona erecek.
                            </p>
                            <Button v-if="canUpgrade" variant="outline" size="sm" class="mt-3 w-full">
                                <Zap class="mr-1.5 h-4 w-4" />
                                Planı Yükselt
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
