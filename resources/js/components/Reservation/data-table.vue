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

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[]
    data: TData[]
    searchColumn?: string
    searchPlaceholder?: string
    showToolbar?: boolean
    emptyMessage?: string
}>()

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
</script>

<template>
    <div class="rounded-sm border border-gray-200 bg-white shadow-sm">
        <div v-if="props.showToolbar !== false" class="border-b border-gray-100 px-5 py-4">
            <slot name="toolbar" :table="table">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:max-w-xs">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <Input class="h-9 w-full pl-9" :placeholder="props.searchPlaceholder ?? 'Search...'"
                            :model-value="searchColumn?.getFilterValue() as string"
                            @update:model-value="searchColumn?.setFilterValue($event)" />
                    </div>
                    <DataTableViewOptions :table="table" />
                </div>
            </slot>
        </div>
        <Table class="min-w-[900px]">
            <TableHeader>
                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="bg-gray-50">
                    <TableHead v-for="header in headerGroup.headers" :key="header.id"
                        class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                            :props="header.getContext()" />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                        :data-state="row.getIsSelected() ? 'selected' : undefined">
                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </TableCell>
                    </TableRow>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell :colspan="props.columns.length" class="h-24 text-center text-sm text-gray-500">
                            {{ props.emptyMessage ?? 'No results.' }}
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
        <div class="border-t border-gray-100 px-5 py-4">
            <DataTablePagination :table="table" />
        </div>
    </div>
</template>
