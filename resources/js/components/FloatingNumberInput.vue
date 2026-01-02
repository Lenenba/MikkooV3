<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    label: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    step: {
        type: [Number, String],
        default: 1,
    },
});

const model = defineModel({
    type: [Number, String],
    required: true,
});

const input = ref(null);
const { t } = useI18n();
const resolvedLabel = computed(() => props.label || t('common.labels.quantity'));

const toNumber = (value) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
};

const increment = () => {
    model.value = toNumber(model.value) + 1;
};

const decrement = () => {
    const currentValue = toNumber(model.value);
    if (currentValue > 0) {
        model.value = currentValue - 1;
    }
};

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div
        class="py-2 px-3 w-full rounded-sm border border-input bg-background">
        <div class="w-full flex justify-between items-center gap-x-3" data-hs-input-number="">
            <div>
                <span class="block text-xs text-muted-foreground">
                    {{ resolvedLabel }}
                    <span v-if="required" class="text-red-500 dark:text-red-400"> *</span>
                </span>
                <!-- Input -->
                <input ref="input" v-model="model"
                    class="p-0 bg-transparent border-0 text-foreground focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    style="-moz-appearance: textfield;" type="number" aria-roledescription="Number field"
                    :step="props.step"
                    data-hs-input-number-input="" />
            </div>
            <!-- Buttons -->
            <div class="flex justify-end items-center gap-x-1.5">
                <!-- Decrement Button -->
                <button type="button" @click="decrement"
                    class="size-6 inline-flex justify-center items-center gap-x-2 rounded-full border border-input bg-background text-foreground shadow-sm hover:bg-muted focus:outline-none focus:bg-muted disabled:opacity-50 disabled:pointer-events-none"
                    tabindex="-1" :aria-label="t('common.actions.decrease')">
                    <svg class="shrink-0 size-3.5" xmlns="http:
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                    </svg>
                </button>

                <!-- Increment Button -->
                <button type="button" @click="increment"
                    class="size-6 inline-flex justify-center items-center gap-x-2 rounded-full border border-input bg-background text-foreground shadow-sm hover:bg-muted focus:outline-none focus:bg-muted disabled:opacity-50 disabled:pointer-events-none"
                    tabindex="-1" :aria-label="t('common.actions.increase')">
                    <svg class="shrink-0 size-3.5" xmlns="http:
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
