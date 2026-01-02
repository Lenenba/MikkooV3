<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { BookOpen, Briefcase, Calendar, ClipboardList, LayoutGrid, Megaphone, Receipt } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const { t } = useI18n();
const role = computed(() => {
    const props = page.props as { role?: string; auth?: { role?: string } };
    return (props.role ?? props.auth?.role ?? '').toString().toLowerCase();
});

const mainNavItems = computed(() => {
    if (role.value === 'superadmin' || role.value === 'admin') {
        return [
            {
                title: t('nav.dashboard'),
                href: '/dashboard',
                icon: LayoutGrid,
            },
            {
                title: t('nav.consultations'),
                href: '/superadmin/consultations',
                icon: ClipboardList,
            },
            {
                title: t('nav.reservations'),
                href: '/reservations',
                icon: Calendar,
            },
            {
                title: t('nav.invoices'),
                href: '/invoices',
                icon: Receipt,
            },
            {
                title: t('nav.announcements'),
                href: '/announcements',
                icon: Megaphone,
            },
        ];
    }

    const items: NavItem[] = [
        {
            title: t('nav.dashboard'),
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: t('nav.babysitters'),
            href: '/search',
            icon: BookOpen,
        },
        {
            title: t('nav.my_reservations'),
            href: '/reservations',
            icon: Calendar,
        },
    ];

    if (role.value === 'parent' || role.value === 'babysitter') {
        items.push({
            title: t('nav.invoices'),
            href: '/invoices',
            icon: Receipt,
        });
    }

    if (role.value === 'parent') {
        items.push({
            title: t('nav.my_announcements'),
            href: '/announcements',
            icon: Megaphone,
        });
    }

    if (role.value === 'babysitter') {
        items.push({
            title: t('nav.services'),
            href: '/settings/services',
            icon: Briefcase,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="xl" as-child>
                        <div class="h-32 w-32 mx-auto">
                            <Link :href="route('dashboard')">
                                <AppLogo />
                            </Link>
                        </div>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
