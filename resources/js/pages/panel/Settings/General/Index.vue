<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Globe,
    ImageIcon,
    Save,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import InputError from '@/components/common/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import PanelLayout from '@/layouts/PanelLayout.vue';
import SettingsLayout from '@/pages/panel/Settings/layout/Layout.vue';
import { index, update, deleteFile } from '@/routes/panel/settings/general';
import type { BreadcrumbItem } from '@/types';

type Props = {
    settings: Record<string, string | null>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ayarlar', href: index().url },
    { title: 'Genel', href: index().url },
];

const form = useForm({
    site_name: props.settings.site_name ?? '',
    site_description: props.settings.site_description ?? '',
    email: props.settings.email ?? '',
    phone: props.settings.phone ?? '',
    mail_from_name: props.settings.mail_from_name ?? '',
    mail_from_address: props.settings.mail_from_address ?? '',
    logo_light: null as File | null,
    logo_dark: null as File | null,
    favicon: null as File | null,
});

const logoLightPreview = ref<string | null>(null);
const logoDarkPreview = ref<string | null>(null);
const faviconPreview = ref<string | null>(null);

function handleFileChange(field: 'logo_light' | 'logo_dark' | 'favicon', event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    form[field] = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        const result = e.target?.result as string;
        if (field === 'logo_light') logoLightPreview.value = result;
        else if (field === 'logo_dark') logoDarkPreview.value = result;
        else faviconPreview.value = result;
    };
    reader.readAsDataURL(file);
}

const showConfirm = ref(false);
let pendingConfirmAction: (() => void) | null = null;

function requestConfirm(action: () => void) {
    pendingConfirmAction = action;
    showConfirm.value = true;
}

function onConfirmed() {
    pendingConfirmAction?.();
    pendingConfirmAction = null;
}

function handleDeleteFile(key: string) {
    requestConfirm(() => {
        router.delete(deleteFile(key).url, { preserveScroll: true });
    });
}

function getStorageUrl(path: string | null): string | null {
    if (!path) return null;
    return `/storage/${path}`;
}

function submitForm() {
    router.post(update().url, {
        _method: 'PUT',
        ...form.data(),
    }, {
        preserveScroll: true,
        forceFormData: true,
        onError: (errors) => {
            form.errors = errors as any;
        },
        onSuccess: () => {
            logoLightPreview.value = null;
            logoDarkPreview.value = null;
            faviconPreview.value = null;
            form.logo_light = null;
            form.logo_dark = null;
            form.favicon = null;
        },
    });
}
</script>

<template>
    <Head title="Genel Ayarlar" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout>
            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Site Info -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Globe class="h-4 w-4 text-muted-foreground" />
                            <CardTitle class="text-sm font-medium">Site Bilgileri</CardTitle>
                        </div>
                        <CardDescription>Temel site ayarlarını yapılandırın</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="site_name">Site Adı</Label>
                                <Input id="site_name" v-model="form.site_name" />
                                <InputError :message="form.errors.site_name" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="email">E-posta</Label>
                                <Input id="email" type="email" v-model="form.email" />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="site_description">Açıklama</Label>
                            <Textarea id="site_description" v-model="form.site_description" rows="2" />
                            <InputError :message="form.errors.site_description" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="grid gap-2">
                                <Label for="phone">Telefon</Label>
                                <Input id="phone" v-model="form.phone" />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="mail_from_name">Gönderici Adı</Label>
                                <Input id="mail_from_name" v-model="form.mail_from_name" />
                                <InputError :message="form.errors.mail_from_name" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="mail_from_address">Gönderici E-posta</Label>
                                <Input id="mail_from_address" type="email" v-model="form.mail_from_address" />
                                <InputError :message="form.errors.mail_from_address" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Logo & Favicon -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <ImageIcon class="h-4 w-4 text-muted-foreground" />
                            <CardTitle class="text-sm font-medium">Logo ve Favicon</CardTitle>
                        </div>
                        <CardDescription>Site logo ve favicon dosyalarını yönetin</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 sm:grid-cols-3">
                            <!-- Logo Light -->
                            <div class="space-y-3">
                                <Label>Açık Tema Logo</Label>
                                <div class="flex h-24 items-center justify-center rounded-md border border-dashed bg-muted/30">
                                    <img
                                        v-if="logoLightPreview"
                                        :src="logoLightPreview"
                                        alt="Logo Light Preview"
                                        class="max-h-20 max-w-full object-contain"
                                    />
                                    <img
                                        v-else-if="settings.logo_light"
                                        :src="getStorageUrl(settings.logo_light)!"
                                        alt="Logo Light"
                                        class="max-h-20 max-w-full object-contain"
                                    />
                                    <ImageIcon v-else class="h-8 w-8 text-muted-foreground/30" />
                                </div>
                                <div class="flex gap-2">
                                    <Label
                                        for="logo_light_input"
                                        class="flex h-8 cursor-pointer items-center gap-1.5 rounded-md border px-3 text-xs font-medium hover:bg-accent"
                                    >
                                        <Upload class="h-3.5 w-3.5" />
                                        Yükle
                                    </Label>
                                    <input
                                        id="logo_light_input"
                                        type="file"
                                        accept="image/png,image/jpeg,image/svg+xml"
                                        class="hidden"
                                        @change="handleFileChange('logo_light', $event)"
                                    />
                                    <Button
                                        v-if="settings.logo_light"
                                        variant="ghost"
                                        size="sm"
                                        type="button"
                                        class="h-8 px-2"
                                        @click="handleDeleteFile('logo_light')"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <InputError :message="form.errors.logo_light" />
                            </div>

                            <!-- Logo Dark -->
                            <div class="space-y-3">
                                <Label>Koyu Tema Logo</Label>
                                <div class="flex h-24 items-center justify-center rounded-md border border-dashed bg-zinc-900">
                                    <img
                                        v-if="logoDarkPreview"
                                        :src="logoDarkPreview"
                                        alt="Logo Dark Preview"
                                        class="max-h-20 max-w-full object-contain"
                                    />
                                    <img
                                        v-else-if="settings.logo_dark"
                                        :src="getStorageUrl(settings.logo_dark)!"
                                        alt="Logo Dark"
                                        class="max-h-20 max-w-full object-contain"
                                    />
                                    <ImageIcon v-else class="h-8 w-8 text-zinc-600" />
                                </div>
                                <div class="flex gap-2">
                                    <Label
                                        for="logo_dark_input"
                                        class="flex h-8 cursor-pointer items-center gap-1.5 rounded-md border px-3 text-xs font-medium hover:bg-accent"
                                    >
                                        <Upload class="h-3.5 w-3.5" />
                                        Yükle
                                    </Label>
                                    <input
                                        id="logo_dark_input"
                                        type="file"
                                        accept="image/png,image/jpeg,image/svg+xml"
                                        class="hidden"
                                        @change="handleFileChange('logo_dark', $event)"
                                    />
                                    <Button
                                        v-if="settings.logo_dark"
                                        variant="ghost"
                                        size="sm"
                                        type="button"
                                        class="h-8 px-2"
                                        @click="handleDeleteFile('logo_dark')"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <InputError :message="form.errors.logo_dark" />
                            </div>

                            <!-- Favicon -->
                            <div class="space-y-3">
                                <Label>Favicon</Label>
                                <div class="flex h-24 items-center justify-center rounded-md border border-dashed bg-muted/30">
                                    <img
                                        v-if="faviconPreview"
                                        :src="faviconPreview"
                                        alt="Favicon Preview"
                                        class="max-h-12 max-w-full object-contain"
                                    />
                                    <img
                                        v-else-if="settings.favicon"
                                        :src="getStorageUrl(settings.favicon)!"
                                        alt="Favicon"
                                        class="max-h-12 max-w-full object-contain"
                                    />
                                    <ImageIcon v-else class="h-8 w-8 text-muted-foreground/30" />
                                </div>
                                <div class="flex gap-2">
                                    <Label
                                        for="favicon_input"
                                        class="flex h-8 cursor-pointer items-center gap-1.5 rounded-md border px-3 text-xs font-medium hover:bg-accent"
                                    >
                                        <Upload class="h-3.5 w-3.5" />
                                        Yükle
                                    </Label>
                                    <input
                                        id="favicon_input"
                                        type="file"
                                        accept="image/png,image/x-icon,image/svg+xml"
                                        class="hidden"
                                        @change="handleFileChange('favicon', $event)"
                                    />
                                    <Button
                                        v-if="settings.favicon"
                                        variant="ghost"
                                        size="sm"
                                        type="button"
                                        class="h-8 px-2"
                                        @click="handleDeleteFile('favicon')"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                                <InputError :message="form.errors.favicon" />
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-muted-foreground">
                            Logo: PNG, JPG, SVG (maks 2MB) &middot; Favicon: PNG, ICO, SVG (maks 512KB)
                        </p>
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-1.5 h-4 w-4" />
                        Ayarları Kaydet
                    </Button>
                </div>
            </form>
        </SettingsLayout>
        <ConfirmDialog v-model="showConfirm" description="Bu dosyayı silmek istediğinize emin misiniz?" @confirm="onConfirmed" />
    </PanelLayout>
</template>
