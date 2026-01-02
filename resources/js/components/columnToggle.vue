<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'
import type { LucideIcon } from 'lucide-vue-next'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { SlidersHorizontal } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

interface DataTableViewOptionsProps {
    table: Table<any>
    label?: string
    icon?: LucideIcon
    buttonClass?: string
    menuLabel?: string
}

const props = withDefaults(defineProps<DataTableViewOptionsProps>(), {
    label: undefined,
    menuLabel: undefined,
})

const { t } = useI18n()
const columns = computed(() => props.table.getAllColumns()
    .filter(
        column =>
            typeof column.accessorFn !== 'undefined' && column.getCanHide(),
    ))

const triggerIcon = computed(() => props.icon ?? SlidersHorizontal)
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm" :class="['h-9 gap-2', props.buttonClass]">
                <component :is="triggerIcon" class="h-4 w-4" />
                {{ props.label ?? t('common.actions.filter') }}
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-[150px]">
            <DropdownMenuLabel>{{ props.menuLabel ?? t('common.labels.toggle_columns') }}</DropdownMenuLabel>
            <DropdownMenuSeparator />

            <DropdownMenuCheckboxItem v-for="column in columns" :key="column.id" class="capitalize"
                :modelValue="column.getIsVisible()" @update:modelValue="(value) => column.toggleVisibility(!!value)">
                {{ column.id }}
            </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
