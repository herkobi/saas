<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowRight,
    CalendarClock,
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
import { Separator } from '@/components/ui/separator';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as planChangeIndex, change } from '@/routes/app/account/plans';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    currentPlan: { name: string; price: number };
    newPlan: { id: string; name: string; price: number; currency: string };
    isUpgrade: boolean;
    prorationType: string;
    proration: {
        remaining_days: number;
        total_days: number;
        credit: number;
        new_amount: number;
        final_amount: number;
    };
    effectiveAt: string | null;
    error?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Plan Değiştir', href: planChangeIndex().url },
    { title: 'Onay', href: '#' },
];

const processing = ref(false);

function confirm() {
    processing.value = true;
    router.post(change().url, {
        plan_price_id: props.newPlan.id,
    }, {
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Plan Değişikliği Onayı" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <!-- Error State -->
            <Card v-if="error" class="border-destructive/50">
                <CardContent class="flex items-center gap-4 py-8">
                    <AlertTriangle class="h-8 w-8 shrink-0 text-destructive" />
                    <p class="font-medium">{{ error }}</p>
                </CardContent>
            </Card>

            <template v-else>
                <div class="mx-auto w-full max-w-xl">
                    <!-- Plan Comparison -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Plan Değişikliği Onayı</CardTitle>
                            <CardDescription>
                                Aşağıdaki değişikliği onaylamak üzeresiniz.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Plan Transition -->
                            <div class="flex items-center justify-center gap-4">
                                <div class="rounded-lg border p-4 text-center flex-1">
                                    <p class="text-sm text-muted-foreground">Mevcut Plan</p>
                                    <p class="mt-1 text-lg font-semibold">{{ currentPlan.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ formatCurrency(currentPlan.price, newPlan.currency) }}</p>
                                </div>
                                <ArrowRight class="h-5 w-5 shrink-0 text-muted-foreground" />
                                <div class="rounded-lg border p-4 text-center flex-1" :class="isUpgrade ? 'border-green-200 bg-green-50 dark:border-green-900/50 dark:bg-green-900/10' : 'border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-900/10'">
                                    <p class="text-sm text-muted-foreground">Yeni Plan</p>
                                    <p class="mt-1 text-lg font-semibold">{{ newPlan.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ formatCurrency(newPlan.price, newPlan.currency) }}</p>
                                </div>
                            </div>

                            <Badge :variant="isUpgrade ? 'default' : 'secondary'" class="w-full justify-center py-1">
                                <Zap v-if="isUpgrade" class="mr-1 h-3 w-3" />
                                {{ isUpgrade ? 'Yükseltme' : 'Düşürme' }}
                            </Badge>

                            <!-- Proration Details -->
                            <div class="rounded-lg border p-4 space-y-3 text-sm">
                                <h4 class="font-medium">Hesaplama Detayları</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Yeni Plan Tutarı</span>
                                        <span>{{ formatCurrency(proration.new_amount, newPlan.currency) }}</span>
                                    </div>
                                    <div v-if="proration.credit > 0" class="flex justify-between text-green-600 dark:text-green-400">
                                        <span>Kalan Süre Kredisi ({{ proration.remaining_days }}/{{ proration.total_days }} gün)</span>
                                        <span>-{{ formatCurrency(proration.credit, newPlan.currency) }}</span>
                                    </div>
                                    <Separator />
                                    <div class="flex justify-between font-medium text-base">
                                        <span>Ödenecek Tutar</span>
                                        <span>{{ formatCurrency(proration.final_amount, newPlan.currency) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Effective Date -->
                            <div v-if="effectiveAt" class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm dark:border-amber-900/50 dark:bg-amber-900/10">
                                <CalendarClock class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" />
                                <span class="text-amber-800 dark:text-amber-200">
                                    Bu değişiklik <strong>{{ formatDate(effectiveAt) }}</strong> tarihinde uygulanacaktır.
                                </span>
                            </div>
                            <div v-else class="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm dark:border-green-900/50 dark:bg-green-900/10">
                                <Zap class="h-4 w-4 shrink-0 text-green-600 dark:text-green-400" />
                                <span class="text-green-800 dark:text-green-200">
                                    Bu değişiklik ödeme sonrası hemen uygulanacaktır.
                                </span>
                            </div>

                            <div class="flex gap-3">
                                <Button variant="outline" class="flex-1" as-child>
                                    <Link :href="planChangeIndex().url">Vazgeç</Link>
                                </Button>
                                <Button class="flex-1" :disabled="processing" @click="confirm">
                                    {{ processing ? 'İşleniyor...' : 'Onayla' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </template>
        </div>
        </AccountLayout>
    </AppLayout>
</template>
