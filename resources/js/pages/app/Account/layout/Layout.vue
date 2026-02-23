<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/common/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { index as subscriptionIndex } from '@/routes/app/account/subscription';
import { index as billingIndex } from '@/routes/app/account/billing';
import { index as paymentsIndex } from '@/routes/app/account/payments';
import { index as addonsIndex } from '@/routes/app/account/addons';
import { index as planChangeIndex } from '@/routes/app/account/plans';
import { index as usersIndex } from '@/routes/app/account/users';
import { index as invitationsIndex } from '@/routes/app/account/users/invitations';
import { type NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    { title: 'Abonelik', href: subscriptionIndex() },
    { title: 'Fatura Bilgileri', href: billingIndex() },
    { title: 'Ödemeler', href: paymentsIndex() },
    { title: 'Eklentiler', href: addonsIndex() },
    { title: 'Plan Değişikliği', href: planChangeIndex() },
    { title: 'Kullanıcılar', href: usersIndex() },
    { title: 'Davetiyeler', href: invitationsIndex() },
];

const { currentUrl, isCurrentUrl } = useCurrentUrl();

function isActiveItem(item: NavItem): boolean {
    const itemUrl = toUrl(item.href);

    if (isCurrentUrl(item.href)) return true;

    if (currentUrl.value.startsWith(itemUrl + '/')) {
        return !sidebarNavItems.some((other) => {
            const otherUrl = toUrl(other.href);
            return otherUrl !== itemUrl && currentUrl.value.startsWith(otherUrl) && otherUrl.length > itemUrl.length;
        });
    }

    return false;
}
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Hesap Yönetimi" description="Abonelik, fatura ve hesap ayarlarınızı yönetin" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0" aria-label="Hesap Yönetimi">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': isActiveItem(item) }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1">
                <slot />
            </div>
        </div>
    </div>
</template>
