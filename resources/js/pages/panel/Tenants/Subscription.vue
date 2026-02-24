<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Ban,
    Calendar,
    Check,
    Clock,
    CreditCard,
    Pencil,
    Plus,
    RefreshCw,
    Repeat,
    Save,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmDialog from '@/components/common/ConfirmDialog.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import InputError from '@/components/common/InputError.vue';
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
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import { useSubscriptionStatus } from '@/composables/useSubscriptionStatus';
import PanelLayout from '@/layouts/PanelLayout.vue';
import TenantLayout from '@/pages/panel/Tenants/layout/Layout.vue';
import { index, show as tenantShow } from '@/routes/panel/tenants';
import {
    show,
    store,
    cancel,
    renew,
    changePlan,
    extendTrial,
    extendGracePeriod,
} from '@/routes/panel/tenants/subscription';
import type { BreadcrumbItem } from '@/types';
import type { Subscription, PlanPrice } from '@/types/billing';
import type { Tenant } from '@/types/tenant';

type Props = {
    tenant: Tenant;
    subscription: (Subscription & {
        plan_price?: PlanPrice & { plan?: { id: string; name: string } };
        next_plan_price?: PlanPrice & { plan?: { id: string; name: string } };
    }) | null;
    history: Subscription[];
    statistics: {
        plans: { id: string; name: string; prices: { id: string; label: string; trial_days: number }[] }[];
        statuses: { value: string; label: string }[];
    };
};

const props = defineProps<Props>();
const { statusLabel } = useSubscriptionStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: tenantShow(props.tenant.id).url },
    { title: 'Abonelik', href: show(props.tenant.id).url },
];

// Dialog states
const showCreateDialog = ref(false);
const showCancelDialog = ref(false);
const showExtendTrialDialog = ref(false);
const showExtendGraceDialog = ref(false);
const showChangePlanDialog = ref(false);
const showCustomPriceDialog = ref(false);

// Forms
const createForm = useForm({
    plan_price_id: '',
    trial_days: 0,
});

const cancelForm = useForm({
    immediate: false,
    reason: '',
});

const extendTrialForm = useForm({
    days: 7,
});

const extendGraceForm = useForm({
    days: 7,
});

const changePlanForm = useForm({
    plan_price_id: '',
    immediate: true,
});

const customPriceForm = useForm({
    custom_price: props.subscription?.custom_price ?? null as number | null,
    custom_currency: props.subscription?.custom_currency ?? 'TRY',
});

function submitCreate() {
    createForm.post(store(props.tenant.id).url, {
        onSuccess: () => { showCreateDialog.value = false; createForm.reset(); },
    });
}

function submitCancel() {
    cancelForm.post(cancel(props.tenant.id).url, {
        onSuccess: () => { showCancelDialog.value = false; cancelForm.reset(); },
    });
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

function submitRenew() {
    requestConfirm(() => {
        router.post(renew(props.tenant.id).url, {}, { preserveScroll: true });
    });
}

function submitExtendTrial() {
    extendTrialForm.post(extendTrial(props.tenant.id).url, {
        onSuccess: () => { showExtendTrialDialog.value = false; extendTrialForm.reset(); },
    });
}

function submitExtendGrace() {
    extendGraceForm.post(extendGracePeriod(props.tenant.id).url, {
        onSuccess: () => { showExtendGraceDialog.value = false; extendGraceForm.reset(); },
    });
}

function submitChangePlan() {
    changePlanForm.post(changePlan(props.tenant.id).url, {
        onSuccess: () => { showChangePlanDialog.value = false; changePlanForm.reset(); },
    });
}

function openCustomPriceDialog() {
    customPriceForm.custom_price = props.subscription?.custom_price ?? null;
    customPriceForm.custom_currency = props.subscription?.custom_currency ?? 'TRY';
    showCustomPriceDialog.value = true;
}

function submitCustomPrice() {
    customPriceForm.put(`/panel/tenants/${props.tenant.id}/subscription/custom-price`, {
        onSuccess: () => { showCustomPriceDialog.value = false; },
    });
}

function removeCustomPrice() {
    const form = useForm({ custom_price: null, custom_currency: null });
    form.put(`/panel/tenants/${props.tenant.id}/subscription/custom-price`, {
        onSuccess: () => { showCustomPriceDialog.value = false; },
    });
}

function subscriptionBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active': return 'default';
        case 'trialing': return 'secondary';
        case 'past_due': return 'destructive';
        case 'canceled':
        case 'expired': return 'outline';
        default: return 'secondary';
    }
}

function intervalLabel(interval: string, count: number): string {
    const labels: Record<string, string> = { month: 'Aylık', year: 'Yıllık', day: 'Günlük' };
    return count > 1 ? `${count} ${labels[interval] ?? interval}` : labels[interval] ?? interval;
}
</script>

<template>
    <Head title="Abonelik Yönetimi" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <TenantLayout
            :tenant-id="tenant.id"
            :tenant-name="tenant.name"
            :tenant-code="tenant.code"
            :tenant-slug="tenant.slug"
        >
            <!-- Current Subscription -->
            <template v-if="subscription">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-sm font-medium">Mevcut Abonelik</CardTitle>
                            <CardDescription>
                                {{ subscription.plan_price?.plan?.name ?? 'Bilinmeyen Plan' }}
                                &middot;
                                {{ intervalLabel(subscription.plan_price?.interval ?? '', subscription.plan_price?.interval_count ?? 1) }}
                            </CardDescription>
                        </div>
                        <Badge :variant="subscriptionBadgeVariant(subscription.status)">
                            {{ statusLabel(subscription.status) }}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <dl class="grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <dt class="text-muted-foreground">Fiyat</dt>
                                <dd class="flex items-center gap-2 font-medium">
                                    {{ formatCurrency(subscription.custom_price ?? subscription.plan_price?.price ?? 0) }}
                                    <Badge v-if="subscription.custom_price" variant="outline" class="text-xs">Özel</Badge>
                                    <Button variant="ghost" size="sm" class="h-6 px-1.5" @click="openCustomPriceDialog">
                                        <Pencil class="h-3 w-3" />
                                        <span class="hidden sm:inline text-xs">Düzenle</span>
                                    </Button>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">Başlangıç</dt>
                                <dd class="font-medium">{{ formatDate(subscription.starts_at) }}</dd>
                            </div>
                            <div v-if="subscription.ends_at">
                                <dt class="text-muted-foreground">Bitiş</dt>
                                <dd class="font-medium">{{ formatDate(subscription.ends_at) }}</dd>
                            </div>
                            <div v-if="subscription.trial_ends_at">
                                <dt class="text-muted-foreground">Deneme Bitiş</dt>
                                <dd class="font-medium">{{ formatDate(subscription.trial_ends_at) }}</dd>
                            </div>
                            <div v-if="subscription.canceled_at">
                                <dt class="text-muted-foreground">İptal Tarihi</dt>
                                <dd class="font-medium">{{ formatDate(subscription.canceled_at) }}</dd>
                            </div>
                            <div v-if="subscription.grace_period_ends_at">
                                <dt class="text-muted-foreground">Ek Süre Bitiş</dt>
                                <dd class="font-medium">{{ formatDate(subscription.grace_period_ends_at) }}</dd>
                            </div>
                            <div v-if="subscription.next_plan_price">
                                <dt class="text-muted-foreground">Planlanmış Değişiklik</dt>
                                <dd class="font-medium">
                                    {{ subscription.next_plan_price.plan?.name ?? '—' }}
                                </dd>
                            </div>
                        </dl>

                        <!-- Actions -->
                        <div class="mt-6 flex flex-wrap gap-2">
                            <Button
                                v-if="subscription.status === 'active' || subscription.status === 'trialing'"
                                variant="outline"
                                size="sm"
                                @click="showChangePlanDialog = true"
                            >
                                <Repeat class="mr-1.5 h-4 w-4" />
                                Plan Değiştir
                            </Button>
                            <Button
                                v-if="subscription.status === 'active' || subscription.status === 'trialing'"
                                variant="outline"
                                size="sm"
                                @click="showCancelDialog = true"
                            >
                                <Ban class="mr-1.5 h-4 w-4" />
                                İptal Et
                            </Button>
                            <Button
                                v-if="subscription.status === 'canceled' || subscription.status === 'expired'"
                                variant="outline"
                                size="sm"
                                @click="submitRenew"
                            >
                                <RefreshCw class="mr-1.5 h-4 w-4" />
                                Yenile
                            </Button>
                            <Button
                                v-if="subscription.trial_ends_at"
                                variant="outline"
                                size="sm"
                                @click="showExtendTrialDialog = true"
                            >
                                <Clock class="mr-1.5 h-4 w-4" />
                                Deneme Uzat
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="showExtendGraceDialog = true"
                            >
                                <Calendar class="mr-1.5 h-4 w-4" />
                                Ek Süre Ver
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </template>

            <!-- No Subscription -->
            <template v-else>
                <Card>
                    <CardContent>
                        <EmptyState :icon="CreditCard" message="Aktif abonelik bulunmuyor">
                            <Button size="sm" @click="showCreateDialog = true">
                                <Plus class="mr-1.5 h-4 w-4" />
                                Abonelik Oluştur
                            </Button>
                        </EmptyState>
                    </CardContent>
                </Card>
            </template>

            <!-- Subscription History -->
            <Card v-if="history && history.length > 0">
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Abonelik Geçmişi</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Durum</TableHead>
                                <TableHead>Başlangıç</TableHead>
                                <TableHead>Bitiş</TableHead>
                                <TableHead>Oluşturulma</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="sub in history" :key="sub.id">
                                <TableCell>
                                    <Badge :variant="subscriptionBadgeVariant(sub.status)">
                                        {{ statusLabel(sub.status) }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm">{{ formatDate(sub.starts_at) }}</TableCell>
                                <TableCell class="text-sm">{{ sub.ends_at ? formatDate(sub.ends_at) : '—' }}</TableCell>
                                <TableCell class="text-sm text-muted-foreground">{{ formatDate(sub.created_at) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </TenantLayout>

        <!-- Create Subscription Dialog -->
        <Dialog v-model:open="showCreateDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Abonelik Oluştur</DialogTitle>
                    <DialogDescription>Bu müşteri için yeni bir abonelik oluşturun.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="plan_price_id">Plan ve Fiyat</Label>
                        <Select v-model="createForm.plan_price_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Plan seçin" />
                            </SelectTrigger>
                            <SelectContent>
                                <template v-for="plan in statistics.plans" :key="plan.id">
                                    <SelectItem
                                        v-for="price in plan.prices"
                                        :key="price.id"
                                        :value="price.id"
                                    >
                                        {{ plan.name }} — {{ price.label }}
                                    </SelectItem>
                                </template>
                            </SelectContent>
                        </Select>
                        <InputError :message="createForm.errors.plan_price_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="trial_days">Deneme Süresi (gün)</Label>
                        <Input id="trial_days" type="number" v-model.number="createForm.trial_days" min="0" max="365" />
                        <InputError :message="createForm.errors.trial_days" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showCreateDialog = false"><X class="mr-1.5 h-4 w-4" />İptal</Button>
                        <Button type="submit" :disabled="createForm.processing"><Plus class="mr-1.5 h-4 w-4" />Oluştur</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Cancel Dialog -->
        <Dialog v-model:open="showCancelDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Abonelik İptali</DialogTitle>
                    <DialogDescription>Bu aboneliği iptal etmek istediğinize emin misiniz?</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCancel" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="cancel_reason">Sebep (opsiyonel)</Label>
                        <Input id="cancel_reason" v-model="cancelForm.reason" />
                        <InputError :message="cancelForm.errors.reason" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showCancelDialog = false"><X class="mr-1.5 h-4 w-4" />Vazgeç</Button>
                        <Button type="submit" variant="destructive" :disabled="cancelForm.processing"><Ban class="mr-1.5 h-4 w-4" />İptal Et</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Extend Trial Dialog -->
        <Dialog v-model:open="showExtendTrialDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Deneme Süresini Uzat</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitExtendTrial" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="extend_trial_days">Gün Sayısı</Label>
                        <Input id="extend_trial_days" type="number" v-model.number="extendTrialForm.days" min="1" max="365" />
                        <InputError :message="extendTrialForm.errors.days" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showExtendTrialDialog = false"><X class="mr-1.5 h-4 w-4" />İptal</Button>
                        <Button type="submit" :disabled="extendTrialForm.processing"><Clock class="mr-1.5 h-4 w-4" />Uzat</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Extend Grace Period Dialog -->
        <Dialog v-model:open="showExtendGraceDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Ek Süre Ver</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitExtendGrace" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="extend_grace_days">Gün Sayısı</Label>
                        <Input id="extend_grace_days" type="number" v-model.number="extendGraceForm.days" min="1" max="90" />
                        <InputError :message="extendGraceForm.errors.days" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showExtendGraceDialog = false"><X class="mr-1.5 h-4 w-4" />İptal</Button>
                        <Button type="submit" :disabled="extendGraceForm.processing"><Clock class="mr-1.5 h-4 w-4" />Uzat</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Change Plan Dialog -->
        <Dialog v-model:open="showChangePlanDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Plan Değiştir</DialogTitle>
                    <DialogDescription>Yeni bir plan seçin.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitChangePlan" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="change_plan_price_id">Yeni Plan ve Fiyat</Label>
                        <Select v-model="changePlanForm.plan_price_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Plan seçin" />
                            </SelectTrigger>
                            <SelectContent>
                                <template v-for="plan in statistics.plans" :key="plan.id">
                                    <SelectItem
                                        v-for="price in plan.prices"
                                        :key="price.id"
                                        :value="price.id"
                                    >
                                        {{ plan.name }} — {{ price.label }}
                                    </SelectItem>
                                </template>
                            </SelectContent>
                        </Select>
                        <InputError :message="changePlanForm.errors.plan_price_id" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showChangePlanDialog = false"><X class="mr-1.5 h-4 w-4" />İptal</Button>
                        <Button type="submit" :disabled="changePlanForm.processing"><Check class="mr-1.5 h-4 w-4" />Değiştir</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
        <!-- Custom Price Dialog -->
        <Dialog v-model:open="showCustomPriceDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Özel Fiyat Belirle</DialogTitle>
                    <DialogDescription>Bu müşteri için plan fiyatından bağımsız özel bir fiyat belirleyin. Boş bırakırsanız plan fiyatı geçerli olur.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitCustomPrice" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="custom_price">Özel Fiyat</Label>
                        <div class="flex gap-2">
                            <Input
                                id="custom_price"
                                type="number"
                                v-model.number="customPriceForm.custom_price"
                                min="0"
                                step="0.01"
                                placeholder="Plan fiyatı geçerli"
                                class="flex-1"
                            />
                            <Input
                                id="custom_currency"
                                v-model="customPriceForm.custom_currency"
                                maxlength="3"
                                class="w-20"
                                placeholder="TRY"
                            />
                        </div>
                        <InputError :message="customPriceForm.errors.custom_price" />
                        <InputError :message="customPriceForm.errors.custom_currency" />
                        <p v-if="subscription?.plan_price?.price" class="text-xs text-muted-foreground">
                            Plan fiyatı: {{ formatCurrency(subscription.plan_price.price) }}
                        </p>
                    </div>
                    <DialogFooter class="gap-2 sm:gap-0">
                        <Button
                            v-if="subscription?.custom_price"
                            type="button"
                            variant="outline"
                            @click="removeCustomPrice"
                        >
                            <X class="mr-1.5 h-4 w-4" />
                            Özel Fiyatı Kaldır
                        </Button>
                        <Button type="button" variant="outline" @click="showCustomPriceDialog = false"><X class="mr-1.5 h-4 w-4" />İptal</Button>
                        <Button type="submit" :disabled="customPriceForm.processing"><Save class="mr-1.5 h-4 w-4" />Kaydet</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            v-model="showConfirm"
            description="Aboneliği yenilemek istediğinize emin misiniz?"
            :destructive="false"
            confirm-label="Yenile"
            @confirm="onConfirmed"
        />
    </PanelLayout>
</template>
