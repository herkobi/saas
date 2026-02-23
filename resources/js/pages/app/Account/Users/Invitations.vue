<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    Clock,
    Mail,
    MailPlus,
    RefreshCw,
    Trash2,
    UserPlus,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import InputError from '@/components/common/InputError.vue';
import { formatDate, formatDateTime } from '@/composables/useFormatting';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as invitationsIndex, store, revoke, resend } from '@/routes/app/account/users/invitations';
import type { BreadcrumbItem } from '@/types';
import type { Invitation } from '@/types/tenant';

const props = defineProps<{
    invitations: Invitation[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Davetiyeler', href: invitationsIndex().url },
];

const statusLabels: Record<string, string> = {
    pending: 'Bekliyor',
    accepted: 'Kabul Edildi',
    expired: 'Süresi Doldu',
    revoked: 'İptal Edildi',
};

function statusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'pending': return 'secondary';
        case 'accepted': return 'default';
        case 'expired': return 'outline';
        case 'revoked': return 'destructive';
        default: return 'outline';
    }
}

// Invite form
const showInviteDialog = ref(false);
const form = useForm({
    email: '',
    role: '2',
});

function submitInvite() {
    form.post(store().url, {
        onSuccess: () => {
            showInviteDialog.value = false;
            form.reset();
        },
    });
}

// Revoke
const showRevokeDialog = ref(false);
const revokeId = ref<string | null>(null);
const revoking = ref(false);

function openRevokeDialog(id: string) {
    revokeId.value = id;
    showRevokeDialog.value = true;
}

function confirmRevoke() {
    if (!revokeId.value) return;
    revoking.value = true;
    router.delete(revoke(revokeId.value).url, {
        onFinish: () => {
            revoking.value = false;
            showRevokeDialog.value = false;
        },
    });
}

// Resend
const resending = ref<string | null>(null);

function handleResend(id: string) {
    resending.value = id;
    router.post(resend(id).url, {}, {
        onFinish: () => {
            resending.value = null;
        },
    });
}
</script>

<template>
    <Head title="Davetiyeler" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="flex items-center gap-2 text-xl font-bold">
                    <Mail class="h-5 w-5" />
                    Davetiyeler
                </h1>
                <Button @click="showInviteDialog = true">
                    <UserPlus class="mr-2 h-4 w-4" />
                    Davet Gönder
                </Button>
            </div>

            <!-- Invitations Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>E-posta</TableHead>
                                <TableHead>Rol</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Bitiş Tarihi</TableHead>
                                <TableHead>Davet Eden</TableHead>
                                <TableHead class="w-[120px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="invitation in invitations" :key="invitation.id">
                                <TableCell class="font-medium">{{ invitation.email }}</TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ invitation.role === 1 ? 'Sahip' : 'Personel' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="statusVariant(invitation.status)">
                                        {{ statusLabels[invitation.status] ?? invitation.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(invitation.expires_at) }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ invitation.invited_by_user?.name ?? '-' }}
                                </TableCell>
                                <TableCell>
                                    <div v-if="invitation.status === 'pending'" class="flex gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            :disabled="resending === invitation.id"
                                            @click="handleResend(invitation.id)"
                                            title="Tekrar Gönder"
                                        >
                                            <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': resending === invitation.id }" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="text-destructive hover:text-destructive"
                                            @click="openRevokeDialog(invitation.id)"
                                            title="İptal Et"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="invitations.length === 0">
                                <TableCell :colspan="6" class="py-12 text-center text-muted-foreground">
                                    Davetiye bulunamadı.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        </AccountLayout>

        <!-- Invite Dialog -->
        <Dialog v-model:open="showInviteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Davet Gönder</DialogTitle>
                    <DialogDescription>
                        Yeni bir kullanıcıyı hesabınıza davet edin.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitInvite" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email">E-posta Adresi</Label>
                        <Input id="email" v-model="form.email" type="email" placeholder="ornek@email.com" :disabled="form.processing" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="space-y-2">
                        <Label for="role">Rol</Label>
                        <Select v-model="form.role">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="1">Sahip</SelectItem>
                                <SelectItem value="2">Personel</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.role" />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" type="button" @click="showInviteDialog = false">Vazgeç</Button>
                        <Button type="submit" :disabled="form.processing">
                            <MailPlus class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Gönderiliyor...' : 'Davet Gönder' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Revoke Dialog -->
        <Dialog v-model:open="showRevokeDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Davetiye İptal</DialogTitle>
                    <DialogDescription>
                        Bu davetiyeyi iptal etmek istediğinize emin misiniz?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showRevokeDialog = false">Vazgeç</Button>
                    <Button variant="destructive" :disabled="revoking" @click="confirmRevoke">
                        {{ revoking ? 'İptal ediliyor...' : 'Evet, İptal Et' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
