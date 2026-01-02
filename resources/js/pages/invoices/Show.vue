<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, useForm, usePage, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { type BreadcrumbItem, type SharedData } from '@/types'

type InvoiceItem = {
    id: number
    description: string
    service_date?: string | null
    quantity: number | string
    unit_price: number | string
    subtotal_amount?: number | string | null
    tax_amount?: number | string | null
    total_amount?: number | string | null
}

type InvoiceShow = {
    id: number
    number?: string | null
    status: string
    currency?: string | null
    vat_rate?: number | string | null
    subtotal_amount?: number | string | null
    tax_amount?: number | string | null
    total_amount?: number | string | null
    period_start?: string | null
    period_end?: string | null
    items?: InvoiceItem[]
}

const page = usePage<SharedData>()
const { t } = useI18n()
const invoice = computed(() => page.props.invoice as InvoiceShow | undefined)
const role = computed(() => (page.props.auth?.role ?? '').toString().toLowerCase())
const isBabysitter = computed(() => role.value === 'babysitter')
const canEdit = computed(() => isBabysitter.value && (invoice.value?.status ?? '') === 'draft')

const form = useForm({
    items: (invoice.value?.items ?? []).map((item) => ({
        id: item.id,
        description: item.description ?? '',
        service_date: item.service_date ?? null,
        quantity: item.quantity ?? 1,
        unit_price: item.unit_price ?? 0,
    })),
})

const toNumber = (value: number | string | null | undefined) => {
    const parsed = Number(value ?? 0)
    return Number.isFinite(parsed) ? parsed : 0
}

const currency = computed(() => invoice.value?.currency ?? 'USD')
const formatCurrency = (value: number | string | null | undefined) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currency.value }).format(toNumber(value))

const vatRate = computed(() => toNumber(invoice.value?.vat_rate ?? 0))

const computedSubtotal = computed(() =>
    form.items.reduce((total, item) => total + toNumber(item.quantity) * toNumber(item.unit_price), 0)
)
const computedTax = computed(() => computedSubtotal.value * vatRate.value)
const computedTotal = computed(() => computedSubtotal.value + computedTax.value)

const displaySubtotal = computed(() =>
    canEdit.value ? computedSubtotal.value : toNumber(invoice.value?.subtotal_amount)
)
const displayTax = computed(() =>
    canEdit.value ? computedTax.value : toNumber(invoice.value?.tax_amount)
)
const displayTotal = computed(() =>
    canEdit.value ? computedTotal.value : toNumber(invoice.value?.total_amount)
)

const formatDate = (value?: string | null) => {
    if (!value) return '-'
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return value
    return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(parsed)
}

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('invoices.title'),
        href: '/invoices',
    },
    {
        title: t('invoices.show.head_title'),
        href: `/invoices/${invoice.value?.id ?? ''}`,
    },
])

const submit = () => {
    if (!invoice.value?.id) {
        return
    }
    form.patch(route('invoices.update', invoice.value.id), { preserveScroll: true })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="$t('invoices.show.head_title')" />

        <div v-if="invoice" class="mx-auto w-full max-w-5xl space-y-6 p-4">
            <div class="rounded-sm border border-border bg-card p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $t('invoices.show.head_title') }}</p>
                        <h1 class="text-2xl font-semibold text-foreground">
                            #{{ invoice.number ?? invoice.id }}
                        </h1>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ $t('invoices.columns.period') }}: {{ formatDate(invoice.period_start) }} - {{ formatDate(invoice.period_end) }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button asChild variant="outline" size="sm">
                            <Link href="/invoices">{{ $t('invoices.show.back') }}</Link>
                        </Button>
                        <Button asChild variant="outline" size="sm">
                            <a :href="route('invoices.download', invoice.id)">{{ $t('invoices.show.download') }}</a>
                        </Button>
                        <Button v-if="canEdit" size="sm" :disabled="form.processing" @click="submit">
                            {{ $t('common.actions.save') }}
                        </Button>
                    </div>
                </div>
            </div>

            <div class="rounded-sm border border-border bg-card p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $t('invoices.show.description') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $t('invoices.show.date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $t('invoices.show.quantity') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $t('invoices.show.unit_price') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ $t('invoices.show.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            <tr v-for="(item, index) in form.items" :key="item.id">
                                <td class="px-4 py-3">
                                    <Input
                                        v-if="canEdit"
                                        v-model="form.items[index].description"
                                        class="h-9"
                                    />
                                    <span v-else class="text-sm text-foreground">{{ item.description }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <Input
                                        v-if="canEdit"
                                        type="date"
                                        v-model="form.items[index].service_date"
                                        class="h-9"
                                    />
                                    <span v-else class="text-sm text-muted-foreground">{{ formatDate(item.service_date) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <Input
                                        v-if="canEdit"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        v-model="form.items[index].quantity"
                                        class="h-9"
                                    />
                                    <span v-else class="text-sm text-foreground">{{ item.quantity }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <Input
                                        v-if="canEdit"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        v-model="form.items[index].unit_price"
                                        class="h-9"
                                    />
                                    <span v-else class="text-sm text-foreground">{{ formatCurrency(item.unit_price) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-foreground">
                                        {{ formatCurrency(toNumber(item.quantity) * toNumber(item.unit_price)) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-sm border border-border bg-card p-6 shadow-sm">
                <div class="grid gap-3 text-sm sm:max-w-md sm:ml-auto">
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">{{ $t('invoices.show.subtotal') }}</span>
                        <span class="font-semibold text-foreground">{{ formatCurrency(displaySubtotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">{{ $t('invoices.show.tax') }}</span>
                        <span class="font-semibold text-foreground">{{ formatCurrency(displayTax) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-border pt-3">
                        <span class="font-semibold text-foreground">{{ $t('invoices.show.total') }}</span>
                        <span class="font-semibold text-foreground">{{ formatCurrency(displayTotal) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="mx-auto w-full max-w-3xl p-6 text-sm text-muted-foreground">
            {{ $t('invoices.show.not_found') }}
        </div>
    </AppLayout>
</template>
