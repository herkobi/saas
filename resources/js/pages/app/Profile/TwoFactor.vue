<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import Heading from '@/components/common/Heading.vue';
import TwoFactorRecoveryCodes from '@/components/common/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/common/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/pages/app/Profile/layout/Layout.vue';
import { show } from '@/routes/app/profile/two-factor';
import { disable, enable } from '@/routes/two-factor';
import type { BreadcrumbItem } from '@/types';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'İki Faktörlü Doğrulama',
        href: show.url(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="İki Faktörlü Doğrulama" />

        <h1 class="sr-only">İki Faktörlü Doğrulama Ayarları</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="İki Faktörlü Doğrulama"
                    description="İki faktörlü doğrulama ayarlarınızı yönetin"
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="destructive">Devre Dışı</Badge>

                    <p class="text-muted-foreground">
                        İki faktörlü doğrulamayı etkinleştirdiğinizde, giriş
                        sırasında güvenli bir pin kodu girmeniz istenecektir. Bu
                        pin kodunu telefonunuzdaki TOTP destekli bir
                        uygulamadan alabilirsiniz.
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />Kuruluma Devam Et
                        </Button>
                        <Form
                            v-else
                            v-bind="enable.form()"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button type="submit" :disabled="processing">
                                <ShieldCheck />2FA'yı Etkinleştir</Button
                            ></Form
                        >
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="default">Aktif</Badge>

                    <p class="text-muted-foreground">
                        İki faktörlü doğrulama etkinleştirildiğinde, giriş
                        sırasında güvenli ve rastgele bir pin kodu girmeniz
                        istenecektir. Bu kodu telefonunuzdaki TOTP destekli
                        uygulamadan alabilirsiniz.
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="relative inline">
                        <Form v-bind="disable.form()" #default="{ processing }">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                            >
                                <ShieldBan />
                                2FA'yı Devre Dışı Bırak
                            </Button>
                        </Form>
                    </div>
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
