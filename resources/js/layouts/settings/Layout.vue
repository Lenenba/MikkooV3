<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const { t } = useI18n();
const sidebarNavItems = computed<NavItem[]>(() => ([
    { title: t('settings.nav.profile'), href: '/settings/profile' },
    { title: t('settings.nav.password'), href: '/settings/password' },
    { title: t('settings.nav.appearance'), href: '/settings/appearance' },
]));

const page = usePage();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="px-4 py-8">
        <Heading :title="t('settings.title')" :description="t('settings.description')" />

        <div class="mt-6 grid gap-6 lg:grid-cols-[240px_1fr]">
            <aside class="rounded-lg border border-border bg-card p-4 shadow-sm">
                <nav class="flex flex-col space-x-0 space-y-2">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="[
                            'w-full justify-start rounded-md px-3 py-2 text-sm',
                            currentPath === item.href ? 'bg-muted text-foreground' : 'text-muted-foreground',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                <section class="space-y-10">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
