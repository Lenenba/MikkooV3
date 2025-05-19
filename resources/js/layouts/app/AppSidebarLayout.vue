<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FlashMessage from '@/components/FlashMessage.vue';
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}
const page = usePage();
const flash = page.props.flash as { success?: string; error?: string };
const flashSuccess = computed(
    () => page.props.flash.success
)
const flashError = computed(
    () => page.props.flash.error
)

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <FlashMessage v-if="flashSuccess" :key="flashSuccess" :message="flashSuccess" type="success" />
            <FlashMessage v-if="flashError" :key="flashError" :message="flashError" type="error" />
            <slot />
        </AppContent>
    </AppShell>
</template>
