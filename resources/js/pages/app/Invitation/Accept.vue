<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { MailCheck, Users } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/app';
import { process } from '@/routes/app/invitation/accept';
import type { BreadcrumbItem } from '@/types';
import type { Invitation } from '@/types/tenant';

const props = defineProps<{
    invitation: Invitation;
    token: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Davet Kabul', href: '#' },
];

function roleLabel(role: number): string {
    switch (role) {
        case 1: return 'Sahip';
        case 2: return 'Personel';
        default: return 'Üye';
    }
}

function accept() {
    router.post(process(props.token).url);
}
</script>

<template>
    <Head title="Davet Kabul" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="mx-auto w-full max-w-lg">
                <Card>
                    <CardHeader class="text-center">
                        <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10">
                            <MailCheck class="h-6 w-6 text-primary" />
                        </div>
                        <CardTitle>Davetiye</CardTitle>
                        <CardDescription>
                            Bir hesaba katılmaya davet edildiniz.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="rounded-lg border p-4 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">E-posta</span>
                                <span class="font-medium">{{ invitation.email }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Rol</span>
                                <Badge variant="outline">
                                    <Users class="mr-1 h-3 w-3" />
                                    {{ roleLabel(invitation.role) }}
                                </Badge>
                            </div>
                        </div>

                        <Button class="w-full" @click="accept">
                            <MailCheck class="mr-2 h-4 w-4" />
                            Daveti Kabul Et
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
