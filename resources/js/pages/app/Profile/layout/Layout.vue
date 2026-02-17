<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/common/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/app/profile';
import { index as notificationsIndex } from '@/routes/app/profile/notifications';
import { edit as editPassword } from '@/routes/app/profile/password';
import { show as showAppearance } from '@/routes/app/profile/appearance';
import { show } from '@/routes/app/profile/two-factor';
import { type NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profil',
        href: editProfile(),
    },
    {
        title: 'Parola',
        href: editPassword(),
    },
    {
        title: 'İki Faktörlü Doğrulama',
        href: show(),
    },
    {
        title: 'Bildirimler',
        href: notificationsIndex(),
    },
    {
        title: 'Görünüm',
        href: showAppearance(),
    },
];

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Ayarlar"
            description="Profil ve hesap ayarlarınızı yönetin"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Ayarlar"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': isCurrentUrl(item.href) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
