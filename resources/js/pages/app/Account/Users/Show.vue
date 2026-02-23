<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    Activity,
    Shield,
    Trash2,
    User,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
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
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { formatDate } from '@/composables/useFormatting';
import { useUserStatus } from '@/composables/useUserStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as usersIndex, activities, remove } from '@/routes/app/account/users';
import { update as updateRole } from '@/routes/app/account/users/role';
import { update as updateStatus } from '@/routes/app/account/users/status';
import type { BreadcrumbItem } from '@/types';
import type { TenantUser } from '@/types/tenant';

const props = defineProps<{
    user: TenantUser;
    pivotStatus: number;
    pivotStatusLabel: string;
    pivotStatusBadge: string;
    canChangeStatus: boolean;
    statusOptions: { value: number; label: string }[];
}>();

const { getRoleLabel, getRoleVariant, getStatusLabel, getStatusVariant } = useUserStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Kullanıcılar', href: usersIndex().url },
    { title: props.user.name, href: '#' },
];

// Role change
const selectedRole = ref(String(props.user.pivot?.role ?? props.user.role));
const updatingRole = ref(false);

function handleRoleUpdate() {
    updatingRole.value = true;
    router.put(updateRole(props.user.id).url, {
        role: Number(selectedRole.value),
    }, {
        onFinish: () => {
            updatingRole.value = false;
        },
    });
}

// Status change
const selectedStatus = ref(String(props.pivotStatus));
const statusReason = ref('');
const updatingStatus = ref(false);

function handleStatusUpdate() {
    updatingStatus.value = true;
    router.put(updateStatus(props.user.id).url, {
        status: Number(selectedStatus.value),
        reason: statusReason.value || undefined,
    }, {
        onFinish: () => {
            updatingStatus.value = false;
        },
    });
}

// Remove
const showRemoveDialog = ref(false);
const removing = ref(false);

function confirmRemove() {
    removing.value = true;
    router.delete(remove(props.user.id).url, {
        onFinish: () => {
            removing.value = false;
            showRemoveDialog.value = false;
        },
    });
}
</script>

<template>
    <Head :title="user.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Main -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <!-- User Info -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="flex items-center gap-2">
                                    <User class="h-5 w-5 text-primary" />
                                    {{ user.name }}
                                </CardTitle>
                                <div class="flex gap-2">
                                    <Badge :variant="getRoleVariant(user.pivot?.role ?? user.role)">
                                        {{ getRoleLabel(user.pivot?.role ?? user.role) }}
                                    </Badge>
                                    <Badge :variant="getStatusVariant(pivotStatus)">
                                        {{ pivotStatusLabel }}
                                    </Badge>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">E-posta</p>
                                    <p class="font-medium">{{ user.email }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm text-muted-foreground">Katılma Tarihi</p>
                                    <p class="font-medium">{{ formatDate(user.created_at) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Role Management -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Shield class="h-4 w-4" />
                                Rol Yönetimi
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-end gap-3">
                                <div class="flex-1 space-y-2">
                                    <Label>Rol</Label>
                                    <Select v-model="selectedRole">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">Sahip</SelectItem>
                                            <SelectItem value="2">Personel</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <Button :disabled="updatingRole" @click="handleRoleUpdate">
                                    {{ updatingRole ? 'Güncelleniyor...' : 'Güncelle' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Status Management -->
                    <Card v-if="canChangeStatus">
                        <CardHeader>
                            <CardTitle class="text-base">Durum Yönetimi</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label>Durum</Label>
                                <Select v-model="selectedStatus">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="opt in statusOptions"
                                            :key="opt.value"
                                            :value="String(opt.value)"
                                        >
                                            {{ opt.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="reason">Sebep (opsiyonel)</Label>
                                <Textarea id="reason" v-model="statusReason" rows="2" placeholder="Durum değişikliği sebebi..." />
                            </div>
                            <div class="flex justify-end">
                                <Button :disabled="updatingStatus" @click="handleStatusUpdate">
                                    {{ updatingStatus ? 'Güncelleniyor...' : 'Durumu Güncelle' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="flex flex-col gap-4">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">İşlemler</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button variant="outline" as-child class="w-full">
                                <Link :href="activities(user.id).url">
                                    <Activity class="mr-2 h-4 w-4" />
                                    Aktivite Geçmişi
                                </Link>
                            </Button>
                            <Separator />
                            <Button
                                variant="ghost"
                                class="w-full text-destructive hover:text-destructive"
                                @click="showRemoveDialog = true"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Kullanıcıyı Çıkar
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        </AccountLayout>

        <!-- Remove Dialog -->
        <Dialog v-model:open="showRemoveDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Kullanıcıyı Çıkar</DialogTitle>
                    <DialogDescription>
                        <strong>{{ user.name }}</strong> kullanıcısını hesaptan çıkarmak istediğinize emin misiniz? Bu işlem geri alınamaz.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showRemoveDialog = false">Vazgeç</Button>
                    <Button variant="destructive" :disabled="removing" @click="confirmRemove">
                        {{ removing ? 'Çıkarılıyor...' : 'Evet, Çıkar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
