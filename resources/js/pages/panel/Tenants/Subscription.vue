<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Ban,
    Calendar,
    Clock,
    CreditCard,
    Plus,
    RefreshCw,
    Repeat,
} from 'lucide-vue-next';
import { ref } from 'vue';
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
import { useTenantTabs } from '@/composables/useTenantTabs';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index } from '@/routes/panel/tenants';
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
const tabs = useTenantTabs(props.tenant.id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: `/panel/tenants/${props.tenant.id}` },
    { title: 'Abonelik', href: show(props.tenant.id).url },
];

// Dialog states
const showCreateDialog = ref(false);
const showCancelDialog = ref(false);
const showExtendTrialDialog = ref(false);
const showExtendGraceDialog = ref(false);
const showChangePlanDialog = ref(false);

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

function submitRenew() {
    if (confirm('Aboneliği yenilemek istediğinize emin misiniz?')) {
        router.post(renew(props.tenant.id).url, {}, { preserveScroll: true });
    }
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
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-lg font-semibold">{{ tenant.name }}</h1>
                <p class="text-sm text-muted-foreground">Abonelik yönetimi</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-1 overflow-x-auto border-b">
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                    :class="tab.href === show(tenant.id).url
                        ? 'border-primary text-primary'
                        : 'border-transparent text-muted-foreground hover:border-muted-foreground/30 hover:text-foreground'"
                >
                    {{ tab.title }}
                </Link>
            </div>

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
                                <dd class="font-medium">
                                    {{ formatCurrency(subscription.custom_price ?? subscription.plan_price?.price ?? 0) }}
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
                    <CardContent class="flex flex-col items-center justify-center py-12 text-center">
                        <CreditCard class="mb-3 h-10 w-10 text-muted-foreground/50" />
                        <p class="text-sm font-medium text-muted-foreground">Aktif abonelik bulunmuyor</p>
                        <Button size="sm" class="mt-4" @click="showCreateDialog = true">
                            <Plus class="mr-1.5 h-4 w-4" />
                            Abonelik Oluştur
                        </Button>
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
        </div>

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
                        <Button type="button" variant="outline" @click="showCreateDialog = false">İptal</Button>
                        <Button type="submit" :disabled="createForm.processing">Oluştur</Button>
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
                        <Button type="button" variant="outline" @click="showCancelDialog = false">Vazgeç</Button>
                        <Button type="submit" variant="destructive" :disabled="cancelForm.processing">İptal Et</Button>
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
                        <Button type="button" variant="outline" @click="showExtendTrialDialog = false">İptal</Button>
                        <Button type="submit" :disabled="extendTrialForm.processing">Uzat</Button>
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
                        <Button type="button" variant="outline" @click="showExtendGraceDialog = false">İptal</Button>
                        <Button type="submit" :disabled="extendGraceForm.processing">Uzat</Button>
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
                        <Button type="button" variant="outline" @click="showChangePlanDialog = false">İptal</Button>
                        <Button type="submit" :disabled="changePlanForm.processing">Değiştir</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </PanelLayout>
</template>
