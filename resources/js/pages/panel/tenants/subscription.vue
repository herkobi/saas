<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import PanelLayout from '@/layouts/Panel.vue';

const props = defineProps<{
    tenant: any;
    subscription: any;
    history: any[];
    statistics: Record<string, any>;
}>();

const cancelForm = useForm({ immediate: false });
const renewForm = useForm({});
const extendTrialForm = useForm({ days: 7 });
const extendGraceForm = useForm({ days: 7 });

const cancelSubscription = (immediate: boolean) => {
    if (confirm(immediate ? 'Abonelik hemen iptal edilecek. Emin misiniz?' : 'Abonelik dönem sonunda iptal edilecek. Emin misiniz?')) {
        cancelForm.immediate = immediate;
        cancelForm.post(`/panel/tenants/${props.tenant.id}/subscription/cancel`);
    }
};

const renewSubscription = () => {
    renewForm.post(`/panel/tenants/${props.tenant.id}/subscription/renew`);
};

const extendTrial = () => {
    extendTrialForm.post(`/panel/tenants/${props.tenant.id}/subscription/extend-trial`);
};

const extendGracePeriod = () => {
    extendGraceForm.post(`/panel/tenants/${props.tenant.id}/subscription/extend-grace-period`);
};

</script>

<template>
    <PanelLayout title="Hesap Aboneliği">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <div class="flex items-center gap-3">
                <Link :href="`/panel/tenants/${tenant.id}`">
                    <Button icon="pi pi-arrow-left" text size="small" aria-label="Geri" />
                </Link>
                <div>
                    <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Abonelik Yönetimi</h2>
                    <p class="text-sm text-surface-500">{{ tenant.name }}</p>
                </div>
            </div>

            <!-- Current Subscription -->
            <Card v-if="subscription">
                <template #title><span class="text-base font-semibold">Mevcut Abonelik</span></template>
                <template #content>
                    <div class="flex flex-col gap-4">
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div>
                                <p class="text-xs text-surface-500">Plan</p>
                                <p class="text-sm font-medium text-surface-900 dark:text-surface-0">{{ subscription.plan_name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-surface-500">Durum</p>
                                <Tag :value="subscription.status_label" :severity="(subscription.status_badge as any) ?? 'info'" />
                            </div>
                            <div>
                                <p class="text-xs text-surface-500">Başlangıç</p>
                                <p class="text-sm text-surface-700 dark:text-surface-300">{{ subscription.starts_at ? formatDate(subscription.starts_at) : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-surface-500">Bitiş</p>
                                <p class="text-sm text-surface-700 dark:text-surface-300">{{ subscription.ends_at ? formatDate(subscription.ends_at) : '-' }}</p>
                            </div>
                        </div>

                        <!-- Admin Actions -->
                        <div class="flex flex-wrap gap-2 border-t border-surface-200 pt-4 dark:border-surface-700">
                            <Button label="Yenile" icon="pi pi-refresh" outlined size="small" :loading="renewForm.processing" @click="renewSubscription" />
                            <Button label="Dönem Sonunda İptal" icon="pi pi-clock" severity="warn" outlined size="small" @click="cancelSubscription(false)" />
                            <Button label="Hemen İptal" icon="pi pi-times" severity="danger" outlined size="small" @click="cancelSubscription(true)" />
                        </div>

                        <!-- Extend Trial -->
                        <div v-if="subscription.trial_ends_at" class="flex items-end gap-2 border-t border-surface-200 pt-4 dark:border-surface-700">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-surface-500">Deneme Süresini Uzat (gün)</label>
                                <InputNumber v-model="extendTrialForm.days" :min="1" :max="365" class="w-32" />
                            </div>
                            <Button label="Uzat" size="small" outlined :loading="extendTrialForm.processing" @click="extendTrial" />
                        </div>

                        <!-- Extend Grace Period -->
                        <div v-if="subscription.grace_ends_at" class="flex items-end gap-2 border-t border-surface-200 pt-4 dark:border-surface-700">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs text-surface-500">Ek Süre Uzat (gün)</label>
                                <InputNumber v-model="extendGraceForm.days" :min="1" :max="90" class="w-32" />
                            </div>
                            <Button label="Uzat" size="small" outlined :loading="extendGraceForm.processing" @click="extendGracePeriod" />
                        </div>
                    </div>
                </template>
            </Card>

            <Card v-else>
                <template #content>
                    <div class="py-6 text-center">
                        <i class="pi pi-credit-card mb-3 text-4xl text-surface-300 dark:text-surface-600" />
                        <p class="text-sm text-surface-500">Bu hesabın aktif aboneliği bulunmuyor.</p>
                    </div>
                </template>
            </Card>

            <!-- Subscription History -->
            <Card v-if="history && history.length > 0">
                <template #title><span class="text-base font-semibold">Abonelik Geçmişi</span></template>
                <template #content>
                    <DataTable :value="history" stripedRows>
                        <Column field="plan_name" header="Plan">
                            <template #body="{ data }">
                                <span class="text-sm font-medium">{{ data.plan_name ?? '-' }}</span>
                            </template>
                        </Column>
                        <Column field="status_label" header="Durum">
                            <template #body="{ data }">
                                <Tag :value="data.status_label" :severity="(data.status_badge as any) ?? 'secondary'" />
                            </template>
                        </Column>
                        <Column field="starts_at" header="Başlangıç">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.starts_at ? formatDate(data.starts_at) : '-' }}</span>
                            </template>
                        </Column>
                        <Column field="ends_at" header="Bitiş">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.ends_at ? formatDate(data.ends_at) : '-' }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
