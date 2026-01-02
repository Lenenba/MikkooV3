<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineOptions({
    inheritAttrs: false,
});

type LogoSize = 'sm' | 'md' | 'lg' | 'xl';

const props = withDefaults(defineProps<{ size?: LogoSize }>(), {
    size: 'lg',
});

const attrs = useAttrs();
const sizeClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'h-8 w-8';
        case 'md':
            return 'h-10 w-10';
        case 'xl':
            return 'h-14 w-14';
        default:
            return 'h-12 w-12';
    }
});

const logoClass = computed(() => ['p-1', sizeClass.value, attrs.class]);
const passthroughAttrs = computed(() => {
    const { class: _class, ...rest } = attrs;
    return rest;
});
</script>

<template>
    <AppLogoIcon v-bind="passthroughAttrs" :class="logoClass" />
</template>
