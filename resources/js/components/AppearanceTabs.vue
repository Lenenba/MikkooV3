<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();
const { t } = useI18n();

const tabs = computed(() => ([
    { value: 'light', Icon: Sun, label: t('common.appearance.light') },
    { value: 'dark', Icon: Moon, label: t('common.appearance.dark') },
    { value: 'system', Icon: Monitor, label: t('common.appearance.system') },
] as const));
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg bg-muted p-1">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            @click="updateAppearance(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                appearance === value
                    ? 'bg-card text-foreground shadow-xs'
                    : 'text-muted-foreground hover:bg-muted/70 hover:text-foreground',
            ]"
        >
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>
