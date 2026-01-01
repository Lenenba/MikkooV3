<script setup>
import { computed, onMounted, ref, useAttrs } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    modelValue: {
        type: [String, Number],
        required: true,
    },
    type: {
        type: String,
        default: 'text',
    },
    id: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        required: true,
    },
    labelClass: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: null,
    },
    autocomplete: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const attrs = useAttrs();
const input = ref(null);
const generatedId = `floating-input-${Math.random().toString(36).slice(2, 10)}`;

const value = computed({
    get: () => props.modelValue ?? '',
    set: (newValue) => {
        emit('update:modelValue', newValue);
    },
});

const inputId = computed(() => props.id || generatedId);
const resolvedAutocomplete = computed(() => {
    if (props.autocomplete) {
        return props.autocomplete;
    }

    if (props.type === 'password') {
        return 'current-password';
    }

    return 'off';
});

onMounted(() => {
    if (input.value && input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div class="relative">
        <input :id="inputId" v-model="value" ref="input" v-bind="attrs" :type="type" :required="required" :disabled="disabled"
            :readonly="readonly" :autocomplete="resolvedAutocomplete" class="peer p-4 block w-full rounded-sm border border-input bg-background text-foreground text-sm placeholder-transparent focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring/20 disabled:opacity-50 disabled:pointer-events-none
                focus:pt-6
                focus:pb-2
                [&:not(:placeholder-shown)]:pt-6
                [&:not(:placeholder-shown)]:pb-2
                autofill:pt-6
                autofill:pb-2" :placeholder="placeholder ?? label" />
        <label :for="inputId" class="absolute top-0 left-0 p-4 h-full text-sm truncate pointer-events-none transition ease-in-out duration-100 origin-[0_0] peer-disabled:opacity-50 peer-disabled:pointer-events-none
                scale-90
                translate-x-0.5
                -translate-y-1.5
                text-muted-foreground
                peer-placeholder-shown:scale-100
                peer-placeholder-shown:translate-x-0
                peer-placeholder-shown:translate-y-0
                peer-placeholder-shown:text-muted-foreground
                peer-focus:scale-90
                peer-focus:translate-x-0.5
                peer-focus:-translate-y-1.5
                peer-focus:text-foreground" :class="labelClass">
            <span>{{ label }}</span>
            <span v-if="required" class="text-red-500 dark:text-red-400"> *</span>
        </label>
    </div>
</template>
