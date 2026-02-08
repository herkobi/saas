<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import AppLayout from '@/layouts/App.vue';

const props = defineProps<{
    currentPlan: { name: string; price: number };
    newPlan: { id: string; name: string; price: number; currency: string };
    isUpgrade: boolean;
    prorationType: string;
    proration: {
        credit: number;
        charge: number;
        net_amount: number;
    } | null;
    effectiveAt: string | null;
}>();

const form = useForm({
    plan_price_id: props.newPlan.id,
});

const submit = () => {
    form.post('/app/account/plan-change');
};

const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency }).format(amount / 100);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppLayout title="Plan Değişikliği Onayı">
        <div class="mx-auto max-w-lg flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link href="/app/account/plan-change">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Plan Değişikliği Onayı</h2>
            </div>

            <Card>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <!-- Direction -->
                        <div class="flex items-center justify-center gap-4">
                            <div class="text-center">
                                <p class="text-xs text-surface-400">Mevcut</p>
                                <p class="font-semibold text-surface-900 dark:text-surface-0">{{ currentPlan.name }}</p>
                                <p class="text-sm text-surface-500">{{ formatCurrency(currentPlan.price, newPlan.currency) }}</p>
                            </div>
                            <i :class="isUpgrade ? 'pi pi-arrow-right text-green-600' : 'pi pi-arrow-right text-orange-600'" class="text-xl" />
                            <div class="text-center">
                                <p class="text-xs text-surface-400">Yeni</p>
                                <p class="font-semibold text-surface-900 dark:text-surface-0">{{ newPlan.name }}</p>
                                <p class="text-sm text-surface-500">{{ formatCurrency(newPlan.price, newPlan.currency) }}</p>
                            </div>
                        </div>

                        <Tag :value="isUpgrade ? 'Yükseltme' : 'Düşürme'" :severity="isUpgrade ? 'success' : 'warn'" class="self-center" />

                        <hr class="border-surface-200 dark:border-surface-700" />

                        <!-- Proration Details -->
                        <div v-if="proration" class="flex flex-col gap-2">
                            <div v-if="proration.credit > 0" class="flex items-center justify-between">
                                <span class="text-sm text-surface-500">Kredi (kalan süre)</span>
                                <span class="text-sm text-green-600">-{{ formatCurrency(proration.credit, newPlan.currency) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-surface-500">Yeni plan ücreti</span>
                                <span class="text-sm text-surface-700 dark:text-surface-300">{{ formatCurrency(proration.charge, newPlan.currency) }}</span>
                            </div>
                            <hr class="border-surface-200 dark:border-surface-700" />
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-surface-900 dark:text-surface-0">Ödenecek Tutar</span>
                                <span class="text-lg font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(proration.net_amount, newPlan.currency) }}</span>
                            </div>
                        </div>

                        <!-- Effective Date -->
                        <div v-if="effectiveAt" class="rounded-lg bg-surface-100 p-3 text-center dark:bg-surface-800">
                            <p class="text-sm text-surface-600 dark:text-surface-400">
                                Değişiklik <strong>{{ formatDate(effectiveAt) }}</strong> tarihinde uygulanacak.
                            </p>
                        </div>
                        <div v-else class="rounded-lg bg-blue-50 p-3 text-center dark:bg-blue-900/20">
                            <p class="text-sm text-blue-700 dark:text-blue-400">Değişiklik hemen uygulanacak.</p>
                        </div>
                    </div>
                </template>
            </Card>

            <Button :label="isUpgrade ? 'Yükseltmeyi Onayla' : 'Düşürmeyi Onayla'" :icon="isUpgrade ? 'pi pi-arrow-up' : 'pi pi-arrow-down'" :loading="form.processing" fluid @click="submit" />
        </div>
    </AppLayout>
</template>
