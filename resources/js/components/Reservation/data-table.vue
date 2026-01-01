<script setup lang="ts" generic="TData, TValue">
import DataTablePagination from '@/components/pagination.vue'
import DataTableViewOptions from '@/components/columnToggle.vue'
import { Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { computed, ref } from 'vue'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { valueUpdater } from '@/lib/utils'
import type {
    ColumnDef,
    ColumnFiltersState,
} from '@tanstack/vue-table'

import {
    FlexRender,
    getCoreRowModel,
    getPaginationRowModel,
    getFilteredRowModel,
    useVueTable,
} from '@tanstack/vue-table'

const props = withDefaults(defineProps<{
    columns: ColumnDef<TData, TValue>[]
    data: TData[]
    searchColumn?: string
    searchPlaceholder?: string
    showToolbar?: boolean
    emptyMessage?: string
}>(), {
    showToolbar: true,
})

const columnFilters = ref<ColumnFiltersState>([])
const rowSelection = ref({})

const table = useVueTable({
    get data() { return props.data },
    get columns() { return props.columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    getFilteredRowModel: getFilteredRowModel(),
    onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
    state: {
        get columnFilters() { return columnFilters.value },
        get rowSelection() { return rowSelection.value },
    },
})

const searchColumn = computed(() => table.getColumn(props.searchColumn ?? 'ref'))
const searchPlaceholder = computed(() => props.searchPlaceholder ?? 'Search')
</script>

<template>
    <div class="overflow-hidden rounded-sm border border-border border-t-2 border-t-primary bg-card shadow-sm">
        <div v-if="props.showToolbar" class="border-b border-border px-4 py-3">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative w-full lg:flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/70" />
                    <Input
                        id="table-search"
                        class="h-9 w-full pl-9"
                        :placeholder="searchPlaceholder"
                        :model-value="searchColumn?.getFilterValue() as string"
                        @update:model-value="searchColumn?.setFilterValue($event)"
                    />
                </div>
                <div class="flex w-full flex-wrap items-center gap-2 lg:w-auto">
                    <slot name="toolbar-filters" :table="table" />
                    <DataTableViewOptions
                        :table="table"
                        label="Filtres"
                        menu-label="Colonnes"
                        button-class="h-9 w-full sm:w-auto"
                    />
                    <slot name="toolbar-actions" :table="table" />
                </div>
            </div>
        </div>
        <Table class="min-w-[900px]">
            <TableHeader>
                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="bg-card hover:bg-transparent">
                    <TableHead v-for="header in headerGroup.headers" :key="header.id"
                        class="h-auto px-4 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                            :props="header.getContext()" />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                        :data-state="row.getIsSelected() ? 'selected' : undefined">
                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-3">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </TableCell>
                    </TableRow>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell :colspan="props.columns.length" class="h-24 text-center text-sm text-muted-foreground">
                            {{ props.emptyMessage ?? 'No results.' }}
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
        <div class="border-t border-border px-5 py-4">
            <DataTablePagination :table="table" />
        </div>
    </div>
</template>
