<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import { ref } from 'vue';
import AppLayout from '@/layouts/App.vue';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { enable, confirm, disable, regenerateRecoveryCodes } from '@/routes/two-factor';
import type { User } from '@/types';

defineProps<{
    user: User;
    twoFactorEnabled: boolean;
    requiresConfirmation: boolean;
}>();

const {
    qrCodeSvg,
    manualSetupKey,
    recoveryCodesList,
    errors: twoFactorErrors,
    hasSetupData,
    clearTwoFactorAuthData,
    fetchSetupData,
    fetchRecoveryCodes,
} = useTwoFactorAuth();

const confirmationCode = ref('');
const showRecoveryCodes = ref(false);

const enableForm = useForm({});
const confirmForm = useForm({});
const disableForm = useForm({});
const regenerateForm = useForm({});

const handleEnable = () => {
    enableForm.post(enable.url(), {
        preserveScroll: true,
        onSuccess: () => {
            fetchSetupData();
        },
    });
};

const handleConfirm = () => {
    confirmForm.post(confirm.url(), {
        data: { code: confirmationCode.value },
        preserveScroll: true,
        onSuccess: () => {
            confirmationCode.value = '';
            clearTwoFactorAuthData();
            fetchRecoveryCodes();
            showRecoveryCodes.value = true;
        },
    });
};

const handleDisable = () => {
    disableForm.delete(disable.url(), {
        preserveScroll: true,
        onSuccess: () => {
            clearTwoFactorAuthData();
            showRecoveryCodes.value = false;
        },
    });
};

const handleRegenerateCodes = () => {
    regenerateForm.post(regenerateRecoveryCodes.url(), {
        preserveScroll: true,
        onSuccess: () => {
            fetchRecoveryCodes();
            showRecoveryCodes.value = true;
        },
    });
};

const handleShowRecoveryCodes = () => {
    fetchRecoveryCodes();
    showRecoveryCodes.value = true;
};
</script>

<template>
    <AppLayout title="İki Adımlı Doğrulama">
        <div class="mx-auto max-w-2xl">
            <h2 class="mb-1 text-xl font-semibold text-surface-900 dark:text-surface-0">İki Adımlı Doğrulama</h2>
            <p class="mb-6 text-sm text-surface-500 dark:text-surface-400">Hesabınızı daha güvenli hale getirmek için iki adımlı doğrulamayı etkinleştirin.</p>

            <!-- Status -->
            <div class="mb-6 flex items-center gap-2">
                <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Durum:</span>
                <Tag v-if="twoFactorEnabled" value="Aktif" severity="success" />
                <Tag v-else value="Pasif" severity="secondary" />
            </div>

            <!-- Error Messages -->
            <Message v-for="error in twoFactorErrors" :key="error" severity="error" variant="simple" size="small" class="mb-4">
                {{ error }}
            </Message>

            <!-- Not Enabled State -->
            <Card v-if="!twoFactorEnabled && !hasSetupData">
                <template #content>
                    <p class="mb-4 text-sm text-surface-600 dark:text-surface-400">
                        İki adımlı doğrulama etkinleştirildiğinde, oturum açma sırasında telefonunuzdaki kimlik doğrulama uygulamasından güvenli bir kod girmeniz istenecektir.
                    </p>
                    <Button label="Etkinleştir" icon="pi pi-shield" :loading="enableForm.processing" @click="handleEnable" />
                </template>
            </Card>

            <!-- Setup QR Code -->
            <Card v-if="hasSetupData && !twoFactorEnabled">
                <template #content>
                    <div class="flex flex-col gap-4">
                        <p class="text-sm text-surface-600 dark:text-surface-400">
                            Kimlik doğrulama uygulamanızla aşağıdaki QR kodu tarayın veya kurulum anahtarını manuel olarak girin.
                        </p>

                        <!-- QR Code -->
                        <div v-if="qrCodeSvg" class="flex justify-center rounded-lg bg-white p-4" v-html="qrCodeSvg" />

                        <!-- Manual Setup Key -->
                        <div v-if="manualSetupKey" class="flex flex-col gap-1">
                            <span class="text-xs font-medium text-surface-500">Kurulum Anahtarı:</span>
                            <code class="break-all rounded bg-surface-100 px-3 py-2 text-sm dark:bg-surface-800">{{ manualSetupKey }}</code>
                        </div>

                        <!-- Confirmation Code -->
                        <div v-if="requiresConfirmation" class="flex flex-col gap-2">
                            <label for="code" class="text-sm font-medium text-surface-700 dark:text-surface-300">Doğrulama Kodu</label>
                            <InputText id="code" v-model="confirmationCode" inputmode="numeric" placeholder="000000" fluid />
                            <Button label="Onayla" icon="pi pi-check" :loading="confirmForm.processing" @click="handleConfirm" />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Enabled State -->
            <div v-if="twoFactorEnabled" class="flex flex-col gap-4">
                <Card>
                    <template #content>
                        <p class="mb-4 text-sm text-surface-600 dark:text-surface-400">
                            İki adımlı doğrulama etkin. Hesabınız artık daha güvenli.
                        </p>
                        <div class="flex gap-2">
                            <Button label="Kurtarma Kodlarını Göster" icon="pi pi-key" outlined @click="handleShowRecoveryCodes" />
                            <Button label="Kodları Yenile" icon="pi pi-refresh" outlined severity="secondary" :loading="regenerateForm.processing" @click="handleRegenerateCodes" />
                            <Button label="Devre Dışı Bırak" icon="pi pi-times" severity="danger" outlined :loading="disableForm.processing" @click="handleDisable" />
                        </div>
                    </template>
                </Card>

                <!-- Recovery Codes -->
                <Card v-if="showRecoveryCodes && recoveryCodesList.length > 0">
                    <template #title>
                        <span class="text-base font-semibold">Kurtarma Kodları</span>
                    </template>
                    <template #content>
                        <p class="mb-3 text-sm text-surface-500 dark:text-surface-400">
                            Bu kodları güvenli bir yerde saklayın. Kimlik doğrulama cihazınıza erişiminizi kaybederseniz bu kodları kullanabilirsiniz.
                        </p>
                        <div class="grid grid-cols-2 gap-2 rounded-lg bg-surface-100 p-4 dark:bg-surface-800">
                            <code v-for="code in recoveryCodesList" :key="code" class="text-sm">{{ code }}</code>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
