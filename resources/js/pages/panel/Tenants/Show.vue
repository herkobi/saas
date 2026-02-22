<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Building2,
    CreditCard,
    Globe,
    Hash,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatCurrency, formatDate, formatDateTime } from '@/composables/useFormatting';
import { useSubscriptionStatus } from '@/composables/useSubscriptionStatus';
import { useTenantTabs } from '@/composables/useTenantTabs';
import PanelLayout from '@/layouts/PanelLayout.vue';
import { index, show } from '@/routes/panel/tenants';
import type { BreadcrumbItem } from '@/types';
import type { Activity } from '@/types/common';
import type { Tenant } from '@/types/tenant';
import type { TenantStatistics } from '@/types/panel';

type Props = {
    tenant: Tenant;
    statistics: TenantStatistics;
    activities: Activity[];
};

const props = defineProps<Props>();
const { statusLabel, statusColor } = useSubscriptionStatus();
const tabs = useTenantTabs(props.tenant.id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Müşteriler', href: index().url },
    { title: props.tenant.name, href: show(props.tenant.id).url },
];

function subscriptionBadgeVariant(status?: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'active': return 'default';
        case 'trialing': return 'secondary';
        case 'past_due': return 'destructive';
        case 'canceled':
        case 'expired': return 'outline';
        default: return 'secondary';
    }
}
</script>

<template>
    <Head :title="tenant.name" />

    <PanelLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div>
                <h1 class="text-lg font-semibold">{{ tenant.name }}</h1>
                <p class="text-sm text-muted-foreground">{{ tenant.code }} &middot; {{ tenant.slug }}</p>
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

            <!-- Stat Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Kullanıcılar</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.total_users }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam Ödeme</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.total_payments }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam Gelir</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.total_revenue) }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Abonelik</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <Badge
                            v-if="statistics.subscription_status"
                            :variant="subscriptionBadgeVariant(statistics.subscription_status)"
                        >
                            {{ statusLabel(statistics.subscription_status) }}
                        </Badge>
                        <span v-else class="text-sm text-muted-foreground">Yok</span>
                    </CardContent>
                </Card>
            </div>

            <!-- Two Column Layout -->
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Left: Tenant Info -->
                <div class="lg:col-span-2 space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Hesap Bilgileri</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-muted-foreground">Hesap Adı</dt>
                                    <dd class="font-medium">{{ tenant.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted-foreground">Kod</dt>
                                    <dd class="font-medium font-mono">{{ tenant.code }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted-foreground">Slug</dt>
                                    <dd class="font-medium">{{ tenant.slug }}</dd>
                                </div>
                                <div v-if="tenant.domain">
                                    <dt class="text-muted-foreground">Domain</dt>
                                    <dd class="font-medium">{{ tenant.domain }}</dd>
                                </div>
                                <div v-if="statistics.current_plan">
                                    <dt class="text-muted-foreground">Plan</dt>
                                    <dd class="font-medium">{{ statistics.current_plan }}</dd>
                                </div>
                                <div v-if="statistics.subscription_ends_at">
                                    <dt class="text-muted-foreground">Abonelik Bitiş</dt>
                                    <dd class="font-medium">{{ formatDate(statistics.subscription_ends_at) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-muted-foreground">Kayıt Tarihi</dt>
                                    <dd class="font-medium">{{ formatDate(statistics.created_at) }}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card v-if="tenant.account">
                        <CardHeader>
                            <CardTitle class="text-sm font-medium">Firma Bilgileri</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                <div v-if="tenant.account.company_name">
                                    <dt class="text-muted-foreground">Firma</dt>
                                    <dd class="font-medium">{{ tenant.account.company_name }}</dd>
                                </div>
                                <div v-if="tenant.account.tax_number">
                                    <dt class="text-muted-foreground">Vergi No</dt>
                                    <dd class="font-medium">{{ tenant.account.tax_number }}</dd>
                                </div>
                                <div v-if="tenant.account.tax_office">
                                    <dt class="text-muted-foreground">Vergi Dairesi</dt>
                                    <dd class="font-medium">{{ tenant.account.tax_office }}</dd>
                                </div>
                                <div v-if="tenant.account.phone">
                                    <dt class="text-muted-foreground">Telefon</dt>
                                    <dd class="font-medium">{{ tenant.account.phone }}</dd>
                                </div>
                                <div v-if="tenant.account.address" class="sm:col-span-2">
                                    <dt class="text-muted-foreground">Adres</dt>
                                    <dd class="font-medium">
                                        {{ tenant.account.address }}
                                        <template v-if="tenant.account.city">, {{ tenant.account.city }}</template>
                                    </dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right: Activities -->
                <Card class="h-fit lg:col-span-1">
                    <CardHeader>
                        <CardTitle class="text-sm font-medium">Son Aktiviteler</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="activities.length > 0" class="space-y-4">
                            <div
                                v-for="activity in activities.slice(0, 10)"
                                :key="activity.id"
                                class="flex flex-col gap-0.5 text-sm"
                            >
                                <p class="leading-snug">{{ activity.description }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatDateTime(activity.created_at) }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Henüz aktivite bulunmuyor.</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </PanelLayout>
</template>
