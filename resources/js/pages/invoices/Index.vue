<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
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
const { t } = useI18n()

const invoices = computed<Invoice[]>(() => page.props.invoices?.data ?? [])
const role = computed(() => (page.props.auth?.role ?? '').toString().toLowerCase())
const isAdmin = computed(() => role.value === 'superadmin' || role.value === 'admin')
const tableColumns = computed(() => getInvoiceColumns(role.value, t))
const stats = computed(() => (page.props as { stats?: Record<string, number | string> }).stats ?? {})

const numberFormatter = new Intl.NumberFormat('en-US')
const formatCount = (value: number | string | null | undefined) => numberFormatter.format(Number(value ?? 0))

const formatCurrency = (value: number | string | null | undefined) => {
    const amount = Number(value ?? 0)
    const currency = (stats.value.currency as string | undefined) ?? 'USD'
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(Number.isFinite(amount) ? amount : 0)
}

const statusOptions = computed(() => [
    { value: 'all', label: t('invoices.status.all') },
    { value: 'draft', label: t('invoices.status.draft') },
    { value: 'issued', label: t('invoices.status.issued') },
    { value: 'paid', label: t('invoices.status.paid') },
    { value: 'void', label: t('invoices.status.void') },
])

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('invoices.title'),
        href: '/invoices',
    },
])

const statCards = computed(() => {
    if (isAdmin.value) {
        return [
            {
                title: t('invoices.stats.drafts'),
                value: formatCount(stats.value.draft_count as number),
                icon: ClipboardList,
                iconClass: 'bg-amber-100 text-amber-600',
            },
            {
                title: t('invoices.stats.issued'),
                value: formatCount(stats.value.issued_count as number),
                icon: CreditCard,
                iconClass: 'bg-sky-100 text-sky-600',
            },
            {
                title: t('invoices.stats.paid'),
                value: formatCount(stats.value.paid_count as number),
                icon: CheckCircle,
                iconClass: 'bg-emerald-100 text-emerald-600',
            },
            {
                title: t('invoices.stats.total_paid'),
                value: formatCurrency(stats.value.paid_total as number),
                icon: Wallet,
                iconClass: 'bg-emerald-100 text-emerald-600',
            },
        ]
    }

    if (role.value === 'babysitter') {
        return [
            {
                title: t('invoices.stats.drafts'),
                value: formatCount(stats.value.draft_count as number),
                icon: ClipboardList,
                iconClass: 'bg-amber-100 text-amber-600',
            },
            {
                title: t('invoices.stats.issued'),
                value: formatCount(stats.value.issued_count as number),
                icon: CreditCard,
                iconClass: 'bg-sky-100 text-sky-600',
            },
            {
                title: t('invoices.stats.total_due'),
                value: formatCurrency(stats.value.issued_total as number),
                icon: Wallet,
                iconClass: 'bg-emerald-100 text-emerald-600',
            },
            {
                title: t('invoices.stats.total_paid'),
                value: formatCurrency(stats.value.paid_total as number),
                icon: CheckCircle,
                iconClass: 'bg-emerald-100 text-emerald-600',
            },
        ]
    }

    return [
        {
            title: t('invoices.stats.to_pay'),
            value: formatCount(stats.value.due_count as number),
            icon: ClipboardList,
            iconClass: 'bg-amber-100 text-amber-600',
        },
        {
            title: t('invoices.stats.total_to_pay'),
            value: formatCurrency(stats.value.due_total as number),
            icon: CreditCard,
            iconClass: 'bg-sky-100 text-sky-600',
        },
        {
            title: t('invoices.stats.paid'),
            value: formatCount(stats.value.paid_count as number),
            icon: CheckCircle,
            iconClass: 'bg-emerald-100 text-emerald-600',
        },
        {
            title: t('invoices.stats.total_paid'),
            value: formatCurrency(stats.value.paid_total as number),
            icon: Wallet,
            iconClass: 'bg-emerald-100 text-emerald-600',
        },
    ]
})
</script>

<template>
    <Head :title="$t('invoices.title')" />

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
                :search-placeholder="$t('invoices.table.search')"
                :empty-message="$t('invoices.table.empty')"
            >
                <template #toolbar-filters="{ table }">
                    <Select
                        :model-value="(table.getColumn('status')?.getFilterValue() as string) ?? 'all'"
                        @update:model-value="value => table.getColumn('status')?.setFilterValue(value === 'all' ? undefined : value)"
                    >
                        <SelectTrigger class="h-9 w-full sm:w-48">
                            <SelectValue :placeholder="$t('invoices.status.all')" />
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
                        {{ $t('common.actions.clear') }}
                    </Button>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
