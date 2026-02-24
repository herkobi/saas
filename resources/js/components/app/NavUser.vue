<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, ChevronsUpDown, LogOut, User } from 'lucide-vue-next';
import { computed } from 'vue';
import UserInfo from '@/components/common/UserInfo.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { logout } from '@/routes';
import { edit } from '@/routes/app/profile';
import { index as notificationsIndex } from '@/routes/app/profile/notifications';

const page = usePage();
const auth = computed(() => page.props.auth);
const { isMobile } = useSidebar();

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <UserInfo
                            v-if="auth.user"
                            :user="auth.user"
                            :show-email="true"
                        />
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : 'right'"
                    align="end"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="p-0 font-normal">
                        <div
                            class="flex items-center gap-2 px-1 py-1.5 text-left text-sm"
                        >
                            <UserInfo
                                v-if="auth.user"
                                :user="auth.user"
                                :show-email="true"
                            />
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuItem :as-child="true">
                            <Link
                                class="block w-full cursor-pointer"
                                :href="edit()"
                                prefetch
                            >
                                <User class="mr-2 h-4 w-4" />
                                Profil Bilgileri
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem :as-child="true">
                            <Link
                                class="block w-full cursor-pointer"
                                :href="notificationsIndex()"
                                prefetch
                            >
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
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
