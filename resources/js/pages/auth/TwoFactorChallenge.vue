<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import AuthLayout from '@/layouts/Auth.vue';
import { store } from '@/routes/two-factor/login';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggleRecovery = () => {
    recovery.value = !recovery.value;
    form.code = '';
    form.recovery_code = '';
};

const submit = () => {
    form.post(store.url(), {
        onFinish: () => {
            form.reset('code', 'recovery_code');
        },
    });
};
</script>

<template>
    <AuthLayout title="İki Adımlı Doğrulama" subtitle="Hesabınıza erişmek için doğrulama kodunuzu girin.">
        <form @submit.prevent="submit" class="flex flex-col gap-4">
            <p class="text-sm text-surface-600 dark:text-surface-400">
                <template v-if="!recovery">
                    Kimlik doğrulama uygulamanızdaki doğrulama kodunu girin.
                </template>
                <template v-else>
                    Kurtarma kodlarınızdan birini girin.
                </template>
            </p>

            <div v-if="!recovery" class="flex flex-col gap-2">
                <label for="code" class="text-sm font-medium text-surface-700 dark:text-surface-300">Doğrulama Kodu</label>
                <InputText id="code" v-model="form.code" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" :invalid="!!form.errors.code" autofocus fluid />
                <small v-if="form.errors.code" class="text-red-500">{{ form.errors.code }}</small>
            </div>

            <div v-else class="flex flex-col gap-2">
                <label for="recovery_code" class="text-sm font-medium text-surface-700 dark:text-surface-300">Kurtarma Kodu</label>
                <InputText id="recovery_code" v-model="form.recovery_code" autocomplete="one-time-code" placeholder="xxxx-xxxx" :invalid="!!form.errors.recovery_code" autofocus fluid />
                <small v-if="form.errors.recovery_code" class="text-red-500">{{ form.errors.recovery_code }}</small>
            </div>

            <Button type="submit" label="Doğrula" icon="pi pi-shield" :loading="form.processing" class="mt-2" fluid />

            <div class="mt-2 text-center">
                <button type="button" class="text-sm font-semibold text-primary-600 hover:text-primary-500" @click="toggleRecovery">
                    <template v-if="!recovery">Kurtarma kodu kullan</template>
                    <template v-else>Doğrulama kodu kullan</template>
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
