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
import { CheckCircle, ClipboardList, CreditCard, Wallet } from 'lucide-vue-next'

const page = usePage<SharedData>()

const invoices = computed<Invoice[]>(() => page.props.invoices?.data ?? [])
const role = computed(() => (page.props.auth?.role ?? '').toString().toLowerCase())
const tableColumns = computed(() => getInvoiceColumns(role.value))
const stats = computed(() => (page.props as { stats?: Record<string, number | string> }).stats ?? {})

const numberFormatter = new Intl.NumberFormat('en-US')
const formatCount = (value: number | string | null | undefined) => numberFormatter.format(Number(value ?? 0))

const formatCurrency = (value: number | string | null | undefined) => {
    const amount = Number(value ?? 0)
    const currency = (stats.value.currency as string | undefined) ?? 'USD'
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(Number.isFinite(amount) ? amount : 0)
}

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

const statCards = computed(() => {
    if (role.value === 'babysitter') {
        return [
            {
                title: 'Brouillons',
                value: formatCount(stats.value.draft_count as number),
                icon: ClipboardList,
                iconClass: 'bg-amber-100 text-amber-600',
            },
            {
                title: 'Factures emises',
                value: formatCount(stats.value.issued_count as number),
                icon: CreditCard,
                iconClass: 'bg-sky-100 text-sky-600',
            },
            {
                title: 'Total a recevoir',
                value: formatCurrency(stats.value.issued_total as number),
                icon: Wallet,
                iconClass: 'bg-emerald-100 text-emerald-600',
            },
            {
                title: 'Total encaisse',
                value: formatCurrency(stats.value.paid_total as number),
                icon: CheckCircle,
                iconClass: 'bg-emerald-100 text-emerald-600',
            },
        ]
    }

    return [
        {
            title: 'Factures a payer',
            value: formatCount(stats.value.due_count as number),
            icon: ClipboardList,
            iconClass: 'bg-amber-100 text-amber-600',
        },
        {
            title: 'Total a payer',
            value: formatCurrency(stats.value.due_total as number),
            icon: CreditCard,
            iconClass: 'bg-sky-100 text-sky-600',
        },
        {
            title: 'Factures payees',
            value: formatCount(stats.value.paid_count as number),
            icon: CheckCircle,
            iconClass: 'bg-emerald-100 text-emerald-600',
        },
        {
            title: 'Total paye',
            value: formatCurrency(stats.value.paid_total as number),
            icon: Wallet,
            iconClass: 'bg-emerald-100 text-emerald-600',
        },
    ]
})
</script>

<template>
    <Head title="Factures" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.title"
                    class="rounded-sm border border-border bg-card p-4 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div :class="['flex h-10 w-10 items-center justify-center rounded-full', stat.iconClass]">
                                <component :is="stat.icon" class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    {{ stat.title }}
                                </p>
                                <p class="text-2xl font-semibold text-foreground">{{ stat.value }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
