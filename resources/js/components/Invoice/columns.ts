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

const resolveName = (person?: Person) => {
    const fullName = [person?.first_name, person?.last_name].filter(Boolean).join(' ').trim()
    return person?.name ?? (fullName || '-')
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

const getPersonColumn = (key: 'parent' | 'babysitter', label: string): ColumnDef<Invoice> => ({
    id: key,
    header: () => h('span', { class: headerClass }, label),
    cell: ({ row }) => {
        const person = (row.original[key] ?? undefined) as Person | undefined
        return h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'text-sm font-medium text-foreground' }, resolveName(person)),
            h('span', { class: 'text-xs text-muted-foreground' }, person?.email ?? '-'),
        ])
    },
    enableSorting: false,
})

const getCounterpartyColumn = (role?: string): ColumnDef<Invoice> => {
    const roleKey = (role ?? '').toString().toLowerCase()
    const isBabysitter = roleKey === 'babysitter'

    return getPersonColumn(isBabysitter ? 'parent' : 'babysitter', isBabysitter ? 'Parent' : 'Babysitter')
}

export const getInvoiceColumns = (role?: string): ColumnDef<Invoice>[] => {
    const roleKey = (role ?? '').toString().toLowerCase()
    const isAdmin = roleKey === 'superadmin' || roleKey === 'admin'

    const columns: ColumnDef<Invoice>[] = [
        {
            accessorKey: 'number',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Facture', class: headerClass }),
            cell: ({ row }) => {
                const ref = row.getValue('number') ?? row.original.id
                return h('span', { class: 'text-sm font-semibold text-foreground' }, `#${ref ?? '-'}`)
            },
        },
    ]

    if (isAdmin) {
        columns.push(getPersonColumn('parent', 'Parent'))
        columns.push(getPersonColumn('babysitter', 'Babysitter'))
    } else {
        columns.push(getCounterpartyColumn(role))
    }

    columns.push(
        {
            id: 'period',
            header: () => h('span', { class: headerClass }, 'Periode'),
            cell: ({ row }) => {
                const start = formatDate(row.original.period_start ?? null)
                const end = formatDate(row.original.period_end ?? null)
                return h('span', { class: 'text-sm text-foreground' }, `${start} - ${end}`)
            },
            enableSorting: false,
        },
        {
            accessorKey: 'total_amount',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Total', class: headerClass }),
            cell: ({ row }) =>
                h('span', { class: 'text-sm font-semibold text-foreground' }, formatCurrency(row.getValue('total_amount'), row.original.currency)),
        },
        {
            accessorKey: 'status',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Statut', class: headerClass }),
            cell: ({ row }) => {
                const status = (row.getValue<string>('status') ?? '').toString().toLowerCase()
                const statusMap: Record<string, { text: string; classes: string }> = {
                    draft: { text: 'Brouillon', classes: 'bg-amber-50 text-amber-700' },
                    issued: { text: 'Emise', classes: 'bg-sky-50 text-sky-700' },
                    paid: { text: 'Payee', classes: 'bg-emerald-50 text-emerald-700' },
                    void: { text: 'Annulee', classes: 'bg-red-50 text-red-700' },
                }
                const mapped = statusMap[status] ?? { text: status || 'Inconnu', classes: 'bg-muted text-foreground' }
                return h('span', { class: `rounded-full px-2.5 py-1 text-xs font-semibold ${mapped.classes}` }, mapped.text)
            },
        },
        {
            accessorKey: 'issued_at',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Emise le', class: headerClass }),
            cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, formatDate(row.getValue('issued_at') as string | null)),
        },
        {
            accessorKey: 'due_at',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Echeance', class: headerClass }),
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
