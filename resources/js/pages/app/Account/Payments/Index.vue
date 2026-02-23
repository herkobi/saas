<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    CheckCircle,
    Clock,
    CreditCard,
    Eye,
    TrendingUp,
    XCircle,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { formatCurrency, formatDate } from '@/composables/useFormatting';
import { usePaymentStatus } from '@/composables/usePaymentStatus';
import AppLayout from '@/layouts/AppLayout.vue';
import AccountLayout from '@/pages/app/Account/layout/Layout.vue';
import { dashboard } from '@/routes/app';
import { index as paymentsIndex, show } from '@/routes/app/account/payments';
import type { BreadcrumbItem } from '@/types';
import type { PaginatedData } from '@/types/common';
import { useDebounceFn } from '@vueuse/core';

type PaymentItem = {
    id: string;
    amount: number;
    currency: string;
    status: string;
    status_label: string;
    status_badge: string;
    gateway: string | null;
    description: string | null;
    paid_at: string | null;
    created_at: string;
};

const props = defineProps<{
    payments: PaginatedData<PaymentItem>;
    statistics: {
        total_count: number;
        total_amount: number;
        completed_count: number;
        completed_amount: number;
        pending_count: number;
        pending_amount: number;
        failed_count: number;
    };
    filters: Record<string, string | null>;
}>();

const { getStatusLabel, getStatusVariant } = usePaymentStatus();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Başlangıç', href: dashboard().url },
    { title: 'Hesap Yönetimi' },
    { title: 'Ödemeler', href: paymentsIndex().url },
];

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');

function applyFilters() {
    router.get(paymentsIndex().url, {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

const debouncedSearch = useDebounceFn(applyFilters, 300);

watch(search, debouncedSearch);
watch(status, applyFilters);

function goToPage(page: number) {
    router.get(paymentsIndex().url, {
        ...props.filters,
        page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Ödemeler" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <AccountLayout>
        <div class="flex flex-col gap-6">
            <!-- Statistics -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Toplam Ödeme</CardTitle>
                        <CreditCard class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.total_amount) }}</div>
                        <p class="text-xs text-muted-foreground">{{ statistics.total_count }} ödeme</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Tamamlanan</CardTitle>
                        <CheckCircle class="h-4 w-4 text-green-600 dark:text-green-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.completed_amount) }}</div>
                        <p class="text-xs text-muted-foreground">{{ statistics.completed_count }} ödeme</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Bekleyen</CardTitle>
                        <Clock class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(statistics.pending_amount) }}</div>
                        <p class="text-xs text-muted-foreground">{{ statistics.pending_count }} ödeme</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium">Başarısız</CardTitle>
                        <XCircle class="h-4 w-4 text-destructive" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ statistics.failed_count }}</div>
                        <p class="text-xs text-muted-foreground">başarısız ödeme</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <Input
                            v-model="search"
                            placeholder="Ara..."
                            class="sm:max-w-xs"
                        />
                        <Select v-model="status">
                            <SelectTrigger class="sm:w-48">
                                <SelectValue placeholder="Durum" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Tüm Durumlar</SelectItem>
                                <SelectItem value="completed">Tamamlanan</SelectItem>
                                <SelectItem value="pending">Bekleyen</SelectItem>
                                <SelectItem value="processing">İşleniyor</SelectItem>
                                <SelectItem value="failed">Başarısız</SelectItem>
                                <SelectItem value="refunded">İade</SelectItem>
                                <SelectItem value="cancelled">İptal</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- Payments Table -->
            <Card>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Açıklama</TableHead>
                                <TableHead>Tutar</TableHead>
                                <TableHead>Durum</TableHead>
                                <TableHead>Tarih</TableHead>
                                <TableHead class="w-[70px]"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="payment in payments.data" :key="payment.id">
                                <TableCell>
                                    <p class="font-medium">{{ payment.description ?? 'Ödeme' }}</p>
                                    <p v-if="payment.gateway" class="text-xs text-muted-foreground">{{ payment.gateway }}</p>
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ formatCurrency(payment.amount, payment.currency) }}
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusVariant(payment.status)">
                                        {{ payment.status_label }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">
                                    {{ formatDate(payment.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <Button variant="ghost" size="icon" as-child>
                                        <Link :href="show(payment.id).url">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="payments.data.length === 0">
                                <TableCell :colspan="5" class="py-12 text-center text-muted-foreground">
                                    Ödeme kaydı bulunamadı.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="payments.last_page > 1" class="flex justify-center">
                <Pagination
                    :total="payments.total"
                    :items-per-page="payments.per_page"
                    :default-page="payments.current_page"
                    @update:page="goToPage"
                >
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />
                        <template v-for="(item, index) in items" :key="index">
                            <PaginationEllipsis v-if="item.type === 'ellipsis'" :index="index" />
                            <PaginationItem v-else :value="item.value" as-child>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-9 w-9"
                                    :class="{ 'border-primary': item.value === payments.current_page }"
                                >
                                    {{ item.value }}
                                </Button>
                            </PaginationItem>
                        </template>
                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
        </AccountLayout>
    </AppLayout>
</template>
