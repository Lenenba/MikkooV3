<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { BookOpen, Briefcase, Calendar, LayoutGrid, Megaphone, Receipt } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();
const role = computed(() => {
    const props = page.props as { role?: string; auth?: { role?: string } };
    return (props.role ?? props.auth?.role ?? '').toString().toLowerCase();
});

const mainNavItems = computed(() => {
    const items: NavItem[] = [
        {
            title: 'Tableau de bord',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Babysitters',
            href: '/search',
            icon: BookOpen,
        },
        {
            title: 'Mes reservations',
            href: '/reservations',
            icon: Calendar,
        },
    ];

    if (role.value === 'parent' || role.value === 'babysitter') {
        items.push({
            title: 'Factures',
            href: '/invoices',
            icon: Receipt,
        });
    }

    if (role.value === 'parent') {
        items.push({
            title: 'Mes annonces',
            href: '/announcements',
            icon: Megaphone,
        });
    }

    if (role.value === 'babysitter') {
        items.push({
            title: 'Services',
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
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
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
