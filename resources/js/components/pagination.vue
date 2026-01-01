<script setup lang="ts">
import { type Table } from '@tanstack/vue-table'
import { computed } from 'vue'

import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

interface DataTablePaginationProps {
    table: Table<any>
}
const props = defineProps<DataTablePaginationProps>()

const totalResults = computed(() => props.table.getFilteredRowModel().rows.length)
const resultLabel = computed(() => (totalResults.value === 1 ? 'resultat' : 'resultats'))
const pageCount = computed(() => Math.max(props.table.getPageCount(), 1))
const pageIndex = computed(() => Math.min(props.table.getState().pagination.pageIndex + 1, pageCount.value))
</script>

<template>
    <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">
            {{ totalResults }} {{ resultLabel }}
        </p>
        <div class="flex items-center gap-3">
            <span class="text-sm text-muted-foreground">
                {{ pageIndex }} sur {{ pageCount }}
            </span>
            <div class="flex items-center gap-1">
                <Button variant="outline" class="h-8 w-8 p-0" :disabled="!props.table.getCanPreviousPage()"
                    @click="props.table.previousPage()">
                    <span class="sr-only">Page precedente</span>
                    <ChevronLeftIcon class="h-4 w-4" />
                </Button>
                <Button variant="outline" class="h-8 w-8 p-0" :disabled="!props.table.getCanNextPage()"
                    @click="props.table.nextPage()">
                    <span class="sr-only">Page suivante</span>
                    <ChevronRightIcon class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
