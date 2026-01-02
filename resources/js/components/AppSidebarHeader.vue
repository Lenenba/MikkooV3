<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Globe } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

withDefaults(defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>(),{
    breadcrumbs:()=>[]
});

const page = usePage<SharedData>();
const { t } = useI18n();
const currentLocale = computed(() => page.props.locale ?? 'en');
const availableLocales = computed(() => page.props.availableLocales ?? ['en']);
const localeOptions = computed(() =>
    availableLocales.value.map((locale) => ({
        value: locale,
        label: t(`common.languages.${locale}`),
    })),
);
const currentLocaleLabel = computed(() => {
    const match = localeOptions.value.find((option) => option.value === currentLocale.value);
    return match?.label ?? currentLocale.value;
});
const currentLocaleShort = computed(() => currentLocale.value.toUpperCase());

const switchLocale = (locale: string) => {
    if (!locale || locale === currentLocale.value) {
        return;
    }
    const target = new URL(page.url, window.location.origin);
    target.searchParams.set('lang', locale);
    window.location.assign(target.toString());
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        variant="outline"
                        size="sm"
                        class="h-9 gap-2 px-3 text-xs font-semibold"
                        :aria-label="`${$t('common.labels.language')}: ${currentLocaleLabel}`"
                    >
                        <Globe class="h-4 w-4" />
                        <span class="sm:hidden">{{ currentLocaleShort }}</span>
                        <span class="hidden sm:inline">{{ currentLocaleLabel }}</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="min-w-[10rem]">
                    <DropdownMenuItem
                        v-for="option in localeOptions"
                        :key="option.value"
                        :class="option.value === currentLocale ? 'font-semibold text-foreground' : ''"
                        @click="switchLocale(option.value)"
                    >
                        {{ option.label }}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
