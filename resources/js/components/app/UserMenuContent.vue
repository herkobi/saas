<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Bell, LogOut, User } from 'lucide-vue-next';
import UserInfo from '@/components/common/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import { edit } from '@/routes/app/profile';
import { index as notificationsIndex } from '@/routes/app/profile/notifications';
import type { User as UserType } from '@/types';

type Props = {
    user: UserType;
};

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <User class="mr-2 h-4 w-4" />
                Profil Bilgileri
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="notificationsIndex()" prefetch>
                <Bell class="mr-2 h-4 w-4" />
                Bildirimler
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Çıkış Yap
        </Link>
    </DropdownMenuItem>
</template>
