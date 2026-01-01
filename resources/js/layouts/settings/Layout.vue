<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    { title: 'Profile', href: '/settings/profile' },
    { title: 'Password', href: '/settings/password' },
    { title: 'Appearance', href: '/settings/appearance' },
];

const page = usePage();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="px-4 py-8">
        <Heading title="Settings" description="Manage your profile and onboarding details" />

        <div class="mt-6 grid gap-6 lg:grid-cols-[240px_1fr]">
            <aside class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <nav class="flex flex-col space-x-0 space-y-2">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="[
                            'w-full justify-start rounded-md px-3 py-2 text-sm',
                            currentPath === item.href ? 'bg-stone-100 text-gray-900' : 'text-gray-600',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <section class="space-y-10">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
