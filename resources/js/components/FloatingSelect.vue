<script setup>
import { computed, onMounted, ref, useAttrs } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    id: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        required: true,
    },
    required: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: '',
    },
    optionLabelKey: {
        type: String,
        default: 'label',
    },
    optionValueKey: {
        type: String,
        default: 'value',
    },
});
const model = defineModel({
    type: [String, Number],
    required: true,
});

const attrs = useAttrs();
const input = ref(null);
const isFocused = ref(false);
const generatedId = `floating-select-${Math.random().toString(36).slice(2, 10)}`;
const selectId = computed(() => props.id || generatedId);

const resolveValue = (option) => {
    if (option && typeof option === 'object') {
        if (props.optionValueKey in option) {
            return option[props.optionValueKey];
        }
        if ('value' in option) {
            return option.value;
        }
        if ('id' in option) {
            return option.id;
        }
        if ('key' in option) {
            return option.key;
        }
    }
    return option;
};

const resolveLabel = (option) => {
    if (option && typeof option === 'object') {
        if (props.optionLabelKey in option) {
            return option[props.optionLabelKey];
        }
        if ('label' in option) {
            return option.label;
        }
        if ('name' in option) {
            return option.name;
        }
        if ('title' in option) {
            return option.title;
        }
    }
    return option;
};

const resolveDisabled = (option) => {
    if (option && typeof option === 'object' && 'disabled' in option) {
        return Boolean(option.disabled);
    }
    return false;
};

const resolvedOptions = computed(() =>
    props.options.map((option) => ({
        value: resolveValue(option),
        label: resolveLabel(option),
        disabled: resolveDisabled(option),
    }))
);

const hasValue = computed(() => model.value !== '' && model.value !== null && model.value !== undefined);
const isFloating = computed(() => isFocused.value || hasValue.value);

const selectClasses = computed(() => [
    'peer p-4 pe-9 block w-full rounded-sm border border-input bg-background text-foreground text-sm focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring/20 disabled:opacity-50 disabled:pointer-events-none',
    isFloating.value ? 'pt-6 pb-2' : '',
]);

const labelClasses = computed(() => [
    'absolute top-0 left-0 p-4 h-full text-sm truncate pointer-events-none transition ease-in-out duration-100 origin-[0_0] peer-disabled:opacity-50 peer-disabled:pointer-events-none peer-focus:text-foreground',
    isFloating.value
        ? 'scale-90 translate-x-0.5 -translate-y-1.5 text-muted-foreground'
        : 'scale-100 translate-x-0 translate-y-0 text-muted-foreground',
]);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <!-- Floating Select -->
    <div class="relative">
        <select :id="selectId" v-model="model" ref="input" v-bind="attrs" :class="selectClasses" :required="required"
            @focus="isFocused = true" @blur="isFocused = false">
            <option v-if="placeholder" value="" :disabled="required">
                {{ placeholder }}
            </option>
            <option v-for="option in resolvedOptions" :key="option.value" class="rounded-sm bg-background text-foreground" :value="option.value"
                :disabled="option.disabled">
                {{ option.label }}
            </option>
        </select>
        <label :for="selectId" :class="labelClasses">
            <span>{{ label }}</span>
            <span v-if="required" class="text-red-500 dark:text-red-400"> *</span>
        </label>
    </div>
    <!-- End Floating Select -->
</template>
