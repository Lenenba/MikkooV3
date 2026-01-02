import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import type { Invoice } from '@/types'
import InvoiceDropdown from '@/components/Invoice/data-table-dropdown.vue'
import DataTableColumnHeader from '@/components/columnHeader.vue'

const headerClass = 'text-xs font-semibold uppercase tracking-wide text-muted-foreground'

type Person = {
    name?: string
    first_name?: string
    last_name?: string
    email?: string
}

const resolveName = (person: Person | undefined, fallbackLabel: string) => {
    const fullName = [person?.first_name, person?.last_name].filter(Boolean).join(' ').trim()
    return person?.name ?? (fullName || fallbackLabel)
}

const formatCurrency = (value: number | string | null | undefined, currency?: string) => {
    const amount = typeof value === 'string' ? Number(value) : value ?? 0
    const safe = Number.isFinite(amount) ? amount : 0
    const unit = currency ?? 'USD'
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: unit }).format(safe)
}

const formatDate = (value?: string | null) => {
    if (!value) return '-'
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return value
    return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(parsed)
}

const getPersonColumn = (key: 'parent' | 'babysitter', label: string, fallbackLabel: string): ColumnDef<Invoice> => ({
    id: key,
    header: () => h('span', { class: headerClass }, label),
    cell: ({ row }) => {
        const person = (row.original[key] ?? undefined) as Person | undefined
        return h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'text-sm font-medium text-foreground' }, resolveName(person, fallbackLabel)),
            h('span', { class: 'text-xs text-muted-foreground' }, person?.email ?? '-'),
        ])
    },
    enableSorting: false,
})

const getCounterpartyColumn = (role: string | undefined, fallbackLabel: string, t: (key: string) => string): ColumnDef<Invoice> => {
    const roleKey = (role ?? '').toString().toLowerCase()
    const isBabysitter = roleKey === 'babysitter'

    return getPersonColumn(
        isBabysitter ? 'parent' : 'babysitter',
        isBabysitter ? t('invoices.columns.parent') : t('invoices.columns.babysitter'),
        fallbackLabel,
    )
}

type Translator = (key: string) => string

export const getInvoiceColumns = (role?: string, t?: Translator): ColumnDef<Invoice>[] => {
    const translate = t ?? ((key: string) => key)
    const roleKey = (role ?? '').toString().toLowerCase()
    const isAdmin = roleKey === 'superadmin' || roleKey === 'admin'
    const fallbackLabel = translate('common.misc.unknown')

    const columns: ColumnDef<Invoice>[] = [
        {
            accessorKey: 'number',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('invoices.columns.invoice'), class: headerClass }),
            cell: ({ row }) => {
                const ref = row.getValue('number') ?? row.original.id
                return h('span', { class: 'text-sm font-semibold text-foreground' }, `#${ref ?? '-'}`)
            },
        },
    ]

    if (isAdmin) {
        columns.push(getPersonColumn('parent', translate('invoices.columns.parent'), fallbackLabel))
        columns.push(getPersonColumn('babysitter', translate('invoices.columns.babysitter'), fallbackLabel))
    } else {
        columns.push(getCounterpartyColumn(role, fallbackLabel, translate))
    }

    columns.push(
        {
            id: 'period',
            header: () => h('span', { class: headerClass }, translate('invoices.columns.period')),
            cell: ({ row }) => {
                const start = formatDate(row.original.period_start ?? null)
                const end = formatDate(row.original.period_end ?? null)
                return h('span', { class: 'text-sm text-foreground' }, `${start} - ${end}`)
            },
            enableSorting: false,
        },
        {
            accessorKey: 'total_amount',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('common.labels.total'), class: headerClass }),
            cell: ({ row }) =>
                h('span', { class: 'text-sm font-semibold text-foreground' }, formatCurrency(row.getValue('total_amount'), row.original.currency)),
        },
        {
            accessorKey: 'status',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('common.labels.status'), class: headerClass }),
            cell: ({ row }) => {
                const status = (row.getValue<string>('status') ?? '').toString().toLowerCase()
                const statusMap: Record<string, { text: string; classes: string }> = {
                    draft: { text: translate('invoices.status.draft'), classes: 'bg-amber-50 text-amber-700' },
                    issued: { text: translate('invoices.status.issued'), classes: 'bg-sky-50 text-sky-700' },
                    paid: { text: translate('invoices.status.paid'), classes: 'bg-emerald-50 text-emerald-700' },
                    void: { text: translate('invoices.status.void'), classes: 'bg-red-50 text-red-700' },
                }
                const mapped = statusMap[status] ?? { text: status || translate('common.misc.unknown'), classes: 'bg-muted text-foreground' }
                return h('span', { class: `rounded-full px-2.5 py-1 text-xs font-semibold ${mapped.classes}` }, mapped.text)
            },
        },
        {
            accessorKey: 'issued_at',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('invoices.columns.issued_at'), class: headerClass }),
            cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, formatDate(row.getValue('issued_at') as string | null)),
        },
        {
            accessorKey: 'due_at',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('invoices.columns.due_at'), class: headerClass }),
            cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, formatDate(row.getValue('due_at') as string | null)),
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => h('div', { class: 'flex justify-end' }, h(InvoiceDropdown, { invoice: row.original })),
        },
    )

    return columns
}
