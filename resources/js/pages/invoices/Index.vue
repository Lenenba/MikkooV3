<script setup lang="ts">
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import DataTable from '@/components/Reservation/data-table.vue'
import { getInvoiceColumns } from '@/components/Invoice/columns'
import { type BreadcrumbItem, type Invoice, type SharedData } from '@/types'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Button } from '@/components/ui/button'

const page = usePage<SharedData>()

const invoices = computed<Invoice[]>(() => page.props.invoices?.data ?? [])
const role = computed(() => (page.props.auth?.role ?? '').toString().toLowerCase())
const tableColumns = computed(() => getInvoiceColumns(role.value))

const statusOptions = [
    { value: 'all', label: 'Tous les statuts' },
    { value: 'draft', label: 'Brouillon' },
    { value: 'issued', label: 'Emise' },
    { value: 'paid', label: 'Payee' },
    { value: 'void', label: 'Annulee' },
]

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Factures',
        href: '/invoices',
    },
]
</script>

<template>
    <Head title="Factures" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <DataTable
                :columns="tableColumns"
                :data="invoices"
                search-column="number"
                search-placeholder="Rechercher une facture..."
                empty-message="Aucune facture pour le moment."
            >
                <template #toolbar-filters="{ table }">
                    <Select
                        :model-value="(table.getColumn('status')?.getFilterValue() as string) ?? 'all'"
                        @update:model-value="value => table.getColumn('status')?.setFilterValue(value === 'all' ? undefined : value)"
                    >
                        <SelectTrigger class="h-9 w-full sm:w-48">
                            <SelectValue placeholder="Tous les statuts" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </template>
                <template #toolbar-actions="{ table }">
                    <Button variant="outline" class="h-9 w-full sm:w-auto" @click="table.resetColumnFilters()">
                        Effacer
                    </Button>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
