<script setup lang="ts">
import { computed, useAttrs, type HTMLAttributes } from 'vue';
defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}
const props = defineProps<Props>();
const attrs = useAttrs();
const logoClass = computed(() => ['inline-flex h-full w-full items-center justify-center leading-none overflow-visible', props.className, attrs.class]);
const passthroughAttrs = computed(() => {
    const { class: _class, ...rest } = attrs;
    return rest;
});
</script>

<template>
    <span v-bind="passthroughAttrs" :class="logoClass">
        <img src="/1.svg" alt="Mikoo logo" class="block max-h-full w-full object-contain object-center dark:hidden" />
        <img src="/2.svg" alt="Mikoo logo" class="hidden max-h-full w-full object-contain object-center dark:block" />
    </span>
</template>
