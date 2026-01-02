<script setup lang="ts">
import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, useAttrs, type HTMLAttributes } from 'vue';
import { useI18n } from 'vue-i18n';
defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}
const props = defineProps<Props>();
const attrs = useAttrs();
const page = usePage<SharedData>();
const { t } = useI18n();
const appName = computed(() => page.props.name ?? '');
const logoClass = computed(() => ['inline-flex h-full w-full items-center justify-center leading-none overflow-visible', props.className, attrs.class]);
const logoAlt = computed(() => {
    const providedAlt = attrs.alt as string | undefined;
    if (providedAlt) {
        return providedAlt;
    }
    return t('common.labels.logo_alt', { app: appName.value || '' }).trim();
});
const passthroughAttrs = computed(() => {
    const { class: _class, alt: _alt, ...rest } = attrs;
    return rest;
});
</script>

<template>
    <span v-bind="passthroughAttrs" :class="logoClass">
        <img src="/2.svg" :alt="logoAlt" class="block max-h-full w-full object-contain object-center dark:hidden" />
        <img src="/2.svg" :alt="logoAlt" class="hidden max-h-full w-full object-contain object-center dark:block" />
    </span>
</template>
