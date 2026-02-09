<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import PanelLayout from '@/layouts/Panel.vue';
import { update } from '@/routes/panel/settings/general';

const props = defineProps<{
    settings: Record<string, any>;
}>();

const form = useForm({
    site_name: props.settings?.site_name ?? '',
    site_description: props.settings?.site_description ?? '',
    support_email: props.settings?.support_email ?? '',
    support_phone: props.settings?.support_phone ?? '',
    logo_light: null as File | null,
    logo_dark: null as File | null,
    favicon: null as File | null,
});

const submit = () => {
    form.post(update.url(), {
        forceFormData: true,
    });
};

const deleteFile = (key: string) => {
    if (confirm('Bu dosyayı silmek istediğinize emin misiniz?')) {
        router.delete(`/panel/settings/general/file/${key}`);
    }
};

const onFileChange = (field: 'logo_light' | 'logo_dark' | 'favicon', event: Event) => {
    const target = event.target as HTMLInputElement;
    form[field] = target.files?.[0] ?? null;
};
</script>

<template>
    <PanelLayout title="Genel Ayarlar">
        <div class="mx-auto max-w-2xl flex flex-col gap-6">
            <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Genel Ayarlar</h2>

            <Card>
                <template #content>
                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="site_name" class="text-sm font-medium text-surface-700 dark:text-surface-300">Site Adı</label>
                            <InputText id="site_name" v-model="form.site_name" :invalid="!!form.errors.site_name" fluid />
                            <small v-if="form.errors.site_name" class="text-red-500">{{ form.errors.site_name }}</small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="site_description" class="text-sm font-medium text-surface-700 dark:text-surface-300">Site Açıklaması</label>
                            <Textarea id="site_description" v-model="form.site_description" rows="3" fluid />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label for="support_email" class="text-sm font-medium text-surface-700 dark:text-surface-300">Destek E-postası</label>
                                <InputText id="support_email" v-model="form.support_email" type="email" fluid />
                            </div>

                            <div class="flex flex-col gap-2">
                                <label for="support_phone" class="text-sm font-medium text-surface-700 dark:text-surface-300">Destek Telefonu</label>
                                <InputText id="support_phone" v-model="form.support_phone" fluid />
                            </div>
                        </div>

                        <!-- File Uploads -->
                        <div class="flex flex-col gap-4 border-t border-surface-200 pt-4 dark:border-surface-700">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-surface-700 dark:text-surface-300">Logo (Açık Tema)</label>
                                <div class="flex items-center gap-2">
                                    <input type="file" accept="image/*" @change="onFileChange('logo_light', $event)" class="text-sm" />
                                    <Button v-if="settings?.logo_light" icon="pi pi-trash" text size="small" severity="danger" @click="deleteFile('logo_light')" aria-label="Sil" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-surface-700 dark:text-surface-300">Logo (Koyu Tema)</label>
                                <div class="flex items-center gap-2">
                                    <input type="file" accept="image/*" @change="onFileChange('logo_dark', $event)" class="text-sm" />
                                    <Button v-if="settings?.logo_dark" icon="pi pi-trash" text size="small" severity="danger" @click="deleteFile('logo_dark')" aria-label="Sil" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-surface-700 dark:text-surface-300">Favicon</label>
                                <div class="flex items-center gap-2">
                                    <input type="file" accept="image/*" @change="onFileChange('favicon', $event)" class="text-sm" />
                                    <Button v-if="settings?.favicon" icon="pi pi-trash" text size="small" severity="danger" @click="deleteFile('favicon')" aria-label="Sil" />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" label="Kaydet" icon="pi pi-check" :loading="form.processing" />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </PanelLayout>
</template>
