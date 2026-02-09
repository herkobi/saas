<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import { computed, ref } from 'vue';
import ToastListener from '@/components/ToastListener.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useInitials } from '@/composables/useInitials';
import { logout } from '@/routes/index';
import type { AppPageProps, NavItem } from '@/types';

const props = withDefaults(
    defineProps<{
        title?: string;
    }>(),
    {
        title: 'Panel',
    },
);

const page = usePage<AppPageProps>();
const { isCurrentUrl } = useCurrentUrl();
const { getInitials } = useInitials();

const user = computed(() => page.props.auth.user);
const tenant = computed(() => page.props.tenant);
const appName = computed(() => page.props.site?.name ?? 'Herkobi');

// Sidebar toggle (mobile)
const sidebarOpen = ref(false);

// User menu
const userMenu = ref();
const userMenuItems = ref([
    {
        label: 'Profil',
        items: [
            { label: 'Profil Düzenle', icon: 'pi pi-user', url: '/app/profile' },
            { label: 'Şifre Değiştir', icon: 'pi pi-lock', url: '/app/profile/password' },
            { label: 'Bildirimler', icon: 'pi pi-bell', url: '/app/profile/notifications' },
        ],
    },
    {
        separator: true,
    },
    {
        label: 'Çıkış Yap',
        icon: 'pi pi-sign-out',
        command: () => {
            useForm({}).post(logout.url());
        },
    },
]);

const toggleUserMenu = (event: Event) => {
    userMenu.value.toggle(event);
};

// Navigation
const mainNav: NavItem[] = [
    { title: 'Panel', href: '/app/dashboard', icon: 'pi pi-home' },
];

const profileNav: NavItem[] = [
    { title: 'Profil', href: '/app/profile', icon: 'pi pi-user' },
    { title: 'Şifre', href: '/app/profile/password', icon: 'pi pi-lock' },
    { title: 'İki Adımlı Doğrulama', href: '/app/profile/two-factor', icon: 'pi pi-shield' },
    { title: 'Bildirimler', href: '/app/profile/notifications', icon: 'pi pi-bell' },
];

const accountNav = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        { title: 'Abonelik', href: '/app/account/subscription', icon: 'pi pi-credit-card' },
        { title: 'Fatura Bilgileri', href: '/app/account/billing', icon: 'pi pi-file-edit' },
        { title: 'Ödemeler', href: '/app/account/payments', icon: 'pi pi-wallet' },
        { title: 'Plan Değiştir', href: '/app/account/plan-change', icon: 'pi pi-arrow-right-arrow-left' },
        { title: 'Eklentiler', href: '/app/account/addons', icon: 'pi pi-box' },
    ];
    if (page.props.site?.allow_team_members) {
        items.push({ title: 'Kullanıcılar', href: '/app/account/users', icon: 'pi pi-users' });
    }
    return items;
});

const isActive = (href: string) => {
    if (href === '/app/dashboard') return isCurrentUrl(href);
    return isCurrentUrl(href) || page.url.startsWith(href);
};
</script>

<template>
    <Head :title="props.title" />

    <Toast position="top-right" />
    <ToastListener />

    <div class="flex min-h-screen bg-surface-50 dark:bg-surface-950">
        <!-- Sidebar Overlay (Mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-surface-200 bg-white transition-transform duration-200 dark:border-surface-800 dark:bg-surface-900 lg:static lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo / Tenant -->
            <div class="flex h-16 items-center gap-3 border-b border-surface-200 px-5 dark:border-surface-800">
                <div class="flex flex-col truncate">
                    <span class="truncate text-sm font-bold text-surface-900 dark:text-surface-0">{{ appName }}</span>
                    <span v-if="tenant" class="truncate text-xs text-surface-500 dark:text-surface-400">{{ tenant.name }}</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4">
                <!-- Ana Menü -->
                <ul class="space-y-1">
                    <li v-for="item in mainNav" :key="item.href">
                        <Link
                            :href="item.href"
                            :class="[
                                'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                isActive(item.href as string)
                                    ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400'
                                    : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900 dark:text-surface-400 dark:hover:bg-surface-800 dark:hover:text-surface-100',
                            ]"
                            @click="sidebarOpen = false"
                        >
                            <i :class="item.icon" class="text-base" />
                            {{ item.title }}
                        </Link>
                    </li>
                </ul>

                <!-- Profil -->
                <div class="mt-6">
                    <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-surface-400 dark:text-surface-500">Profil</p>
                    <ul class="space-y-1">
                        <li v-for="item in profileNav" :key="item.href">
                            <Link
                                :href="item.href"
                                :class="[
                                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                    isActive(item.href as string)
                                        ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400'
                                        : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900 dark:text-surface-400 dark:hover:bg-surface-800 dark:hover:text-surface-100',
                                ]"
                                @click="sidebarOpen = false"
                            >
                                <i :class="item.icon" class="text-base" />
                                {{ item.title }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Hesap Yönetimi -->
                <div class="mt-6">
                    <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-surface-400 dark:text-surface-500">Hesap</p>
                    <ul class="space-y-1">
                        <li v-for="item in accountNav" :key="item.href">
                            <Link
                                :href="item.href"
                                :class="[
                                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                    isActive(item.href as string)
                                        ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400'
                                        : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900 dark:text-surface-400 dark:hover:bg-surface-800 dark:hover:text-surface-100',
                                ]"
                                @click="sidebarOpen = false"
                            >
                                <i :class="item.icon" class="text-base" />
                                {{ item.title }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col">
            <!-- Top Header -->
            <header class="flex h-16 items-center justify-between border-b border-surface-200 bg-white px-4 dark:border-surface-800 dark:bg-surface-900 lg:px-6">
                <!-- Mobile menu button -->
                <Button
                    icon="pi pi-bars"
                    text
                    severity="secondary"
                    class="lg:!hidden"
                    @click="sidebarOpen = true"
                    aria-label="Menüyü aç"
                />

                <div class="hidden lg:block" />

                <!-- User Menu -->
                <div class="flex items-center gap-3">
                    <button
                        class="flex items-center gap-2 rounded-lg p-1.5 transition-colors hover:bg-surface-100 dark:hover:bg-surface-800"
                        @click="toggleUserMenu"
                        aria-label="Kullanıcı menüsü"
                    >
                        <Avatar
                            :label="getInitials(user?.name)"
                            shape="circle"
                            class="!bg-primary-100 !text-primary-700 dark:!bg-primary-900/30 dark:!text-primary-400"
                        />
                        <span class="hidden text-sm font-medium text-surface-700 dark:text-surface-300 md:inline">
                            {{ user?.name }}
                        </span>
                        <i class="pi pi-chevron-down hidden text-xs text-surface-400 md:inline" />
                    </button>
                    <Menu ref="userMenu" :model="userMenuItems" :popup="true" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
