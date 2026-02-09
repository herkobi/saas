<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import { ref } from 'vue';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import PanelLayout from '@/layouts/Panel.vue';
import type { User } from '@/types';

const props = defineProps<{
    user: User;
    twoFactorEnabled: boolean;
    requiresConfirmation: boolean;
}>();

const { qrCode, setupKey, recoveryCodes, fetchQrCode, fetchSetupKey, fetchRecoveryCodes } = useTwoFactorAuth();

const showingRecoveryCodes = ref(false);
const confirming = ref(false);

const enableForm = useForm({});
const confirmForm = useForm({ code: '' });
const disableForm = useForm({});
const regenerateForm = useForm({});

const enableTwoFactor = () => {
    enableForm.post('/user/two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            fetchQrCode();
            fetchSetupKey();
            if (props.requiresConfirmation) {
                confirming.value = true;
            } else {
                fetchRecoveryCodes();
            }
        },
    });
};

const confirmTwoFactor = () => {
    confirmForm.post('/user/confirmed-two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            confirming.value = false;
            fetchRecoveryCodes();
        },
    });
};

const disableTwoFactor = () => {
    disableForm.delete('/user/two-factor-authentication', {
        preserveScroll: true,
    });
};

const regenerateRecoveryCodes = () => {
    regenerateForm.post('/user/two-factor-recovery-codes', {
        preserveScroll: true,
        onSuccess: () => fetchRecoveryCodes(),
    });
};

const showRecoveryCodes = () => {
    fetchRecoveryCodes();
    showingRecoveryCodes.value = true;
};
</script>

<template>
    <PanelLayout title="İki Adımlı Doğrulama">
        <div class="mx-auto max-w-2xl">
            <h2 class="mb-1 text-xl font-semibold text-surface-900 dark:text-surface-0">İki Adımlı Doğrulama</h2>
            <p class="mb-6 text-sm text-surface-500 dark:text-surface-400">Hesabınıza ekstra güvenlik katmanı ekleyin.</p>

            <!-- Not Enabled -->
            <Card v-if="!twoFactorEnabled && !confirming">
                <template #content>
                    <div class="flex flex-col items-center gap-4 py-4 text-center">
                        <i class="pi pi-shield text-4xl text-surface-300 dark:text-surface-600" />
                        <p class="text-sm text-surface-600 dark:text-surface-400">
                            İki adımlı doğrulama etkin değil. Hesabınızı daha güvenli hale getirmek için etkinleştirin.
                        </p>
                        <Button label="Etkinleştir" icon="pi pi-shield" :loading="enableForm.processing" @click="enableTwoFactor" />
                    </div>
                </template>
            </Card>

            <!-- Confirming -->
            <div v-if="confirming" class="flex flex-col gap-4">
                <Card v-if="qrCode">
                    <template #content>
                        <div class="flex flex-col items-center gap-4">
                            <p class="text-sm text-surface-600 dark:text-surface-400">
                                Authenticator uygulamanız ile aşağıdaki QR kodu tarayın.
                            </p>
                            <div v-html="qrCode" class="rounded-lg bg-white p-4" />
                            <div v-if="setupKey" class="text-center">
                                <p class="text-xs text-surface-400">Kurulum Anahtarı:</p>
                                <code class="text-sm font-mono font-semibold">{{ setupKey }}</code>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #content>
                        <form @submit.prevent="confirmTwoFactor" class="flex flex-col gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="code" class="text-sm font-medium text-surface-700 dark:text-surface-300">Doğrulama Kodu</label>
                                <InputText id="code" v-model="confirmForm.code" :invalid="!!confirmForm.errors.code" fluid />
                                <small v-if="confirmForm.errors.code" class="text-red-500">{{ confirmForm.errors.code }}</small>
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button label="İptal" severity="secondary" outlined @click="disableTwoFactor" />
                                <Button type="submit" label="Onayla" icon="pi pi-check" :loading="confirmForm.processing" />
                            </div>
                        </form>
                    </template>
                </Card>
            </div>

            <!-- Enabled -->
            <div v-if="twoFactorEnabled && !confirming" class="flex flex-col gap-4">
                <Message severity="success" :closable="false">
                    İki adımlı doğrulama etkin. Hesabınız ekstra güvenlik ile korunuyor.
                </Message>

                <Card v-if="showingRecoveryCodes && recoveryCodes.length > 0">
                    <template #title><span class="text-base font-semibold">Kurtarma Kodları</span></template>
                    <template #content>
                        <p class="mb-3 text-sm text-surface-600 dark:text-surface-400">
                            Bu kodları güvenli bir yere kaydedin. Authenticator uygulamanıza erişiminizi kaybettiğinizde kullanabilirsiniz.
                        </p>
                        <div class="grid grid-cols-2 gap-2 rounded-lg bg-surface-100 p-4 dark:bg-surface-800">
                            <code v-for="code in recoveryCodes" :key="code" class="text-sm font-mono">{{ code }}</code>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <Button label="Kodları Yenile" icon="pi pi-refresh" severity="secondary" outlined size="small" :loading="regenerateForm.processing" @click="regenerateRecoveryCodes" />
                        </div>
                    </template>
                </Card>

                <div class="flex gap-2">
                    <Button v-if="!showingRecoveryCodes" label="Kurtarma Kodlarını Göster" icon="pi pi-key" outlined @click="showRecoveryCodes" />
                    <Button label="Devre Dışı Bırak" icon="pi pi-times" severity="danger" outlined :loading="disableForm.processing" @click="disableTwoFactor" />
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
