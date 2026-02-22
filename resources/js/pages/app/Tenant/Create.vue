<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';
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
import InputError from '@/components/common/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/app';
import { create, store } from '@/routes/app/tenant';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Yeni Hesap', href: create().url },
];

const form = useForm({
    name: '',
});

function submit() {
    form.post(store().url);
}
</script>

<template>
    <Head title="Yeni Hesap Oluştur" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="mx-auto w-full max-w-lg">
                <Card>
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                            <Building2 class="h-6 w-6 text-primary" />
                        </div>
                        <CardTitle>Yeni Hesap Oluştur</CardTitle>
                        <CardDescription>
                            Yeni bir hesap oluşturarak platformu kullanmaya başlayın.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Hesap Adı</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Şirket veya proje adı"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="form.processing"
                            >
                                <Building2 v-if="!form.processing" class="mr-2 h-4 w-4" />
                                {{ form.processing ? 'Oluşturuluyor...' : 'Hesap Oluştur' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
