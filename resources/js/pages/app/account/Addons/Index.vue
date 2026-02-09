<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import AppLayout from '@/layouts/App.vue';

interface AvailableAddon {
    id: string;
    name: string;
    description?: string;
    price: number;
    currency: string;
    type: string;
    type_label: string;
    feature_name: string;
    is_recurring: boolean;
}

interface CurrentAddon {
    id: string;
    addon_id: string;
    name: string;
    quantity: number;
    is_active: boolean;
    expires_at: string | null;
}

defineProps<{
    availableAddons: AvailableAddon[];
    currentAddons: CurrentAddon[];
}>();

const purchaseAddon = (addonId: string) => {
    useForm({ addon_id: addonId }).post('/app/account/addons/purchase');
};

const cancelAddon = (addonId: string) => {
    useForm({}).post(`/app/account/addons/${addonId}/cancel`);
};
</script>

<template>
    <AppLayout title="Eklentiler">
        <div class="mx-auto max-w-4xl flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Eklentiler</h2>

            <!-- Current Addons -->
            <div v-if="currentAddons.length > 0">
                <h3 class="mb-3 text-base font-semibold text-surface-700 dark:text-surface-300">Aktif Eklentiler</h3>
                <div class="flex flex-col gap-3">
                    <Card v-for="addon in currentAddons" :key="addon.id">
                        <template #content>
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-surface-900 dark:text-surface-0">{{ addon.name }}</span>
                                    <div class="flex items-center gap-2">
                                        <Tag :value="addon.is_active ? 'Aktif' : 'Pasif'" :severity="addon.is_active ? 'success' : 'secondary'" />
                                        <span v-if="addon.expires_at" class="text-xs text-surface-400">Bitiş: {{ formatDate(addon.expires_at) }}</span>
                                    </div>
                                </div>
                                <Button v-if="addon.is_active" label="İptal Et" icon="pi pi-times" severity="danger" outlined size="small" @click="cancelAddon(addon.addon_id)" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Available Addons -->
            <div v-if="availableAddons.length > 0">
                <h3 class="mb-3 text-base font-semibold text-surface-700 dark:text-surface-300">Satın Alınabilir Eklentiler</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="addon in availableAddons" :key="addon.id">
                        <template #content>
                            <div class="flex flex-col gap-3">
                                <div>
                                    <span class="text-base font-semibold text-surface-900 dark:text-surface-0">{{ addon.name }}</span>
                                    <p v-if="addon.description" class="mt-1 text-xs text-surface-400">{{ addon.description }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Tag :value="addon.type_label" severity="info" />
                                    <span class="text-xs text-surface-400">{{ addon.feature_name }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-surface-900 dark:text-surface-0">{{ formatCurrency(addon.price, addon.currency) }}</span>
                                    <Tag v-if="addon.is_recurring" value="Tekrarlayan" severity="secondary" />
                                </div>
                                <Button label="Satın Al" icon="pi pi-shopping-cart" size="small" fluid @click="purchaseAddon(addon.id)" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <div v-if="availableAddons.length === 0 && currentAddons.length === 0" class="py-12 text-center">
                <i class="pi pi-box mb-3 text-4xl text-surface-300 dark:text-surface-600" />
                <p class="text-sm text-surface-500 dark:text-surface-400">Henüz eklenti bulunmuyor.</p>
            </div>
        </div>
    </AppLayout>
</template>
