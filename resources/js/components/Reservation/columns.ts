import { h } from 'vue'

export const columns: ColumnDef<Payment>[] = [
    {
        accessorKey: 'ref',
        header: () => h('div', { class: 'text-left' }, 'Ref #'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left' }, row.getValue('ref'))
        },
    },
    {
        accessorKey: 'notes',
        header: () => h('div', { class: 'text-left' }, 'Notes'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left' }, row.getValue('notes'))
        },
    },
    {
        accessorKey: 'ref',
        header: () => h('div', { class: 'text-left' }, 'Ref #'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left' }, row.getValue('ref'))
        },
    },
    {
        accessorKey: 'ref',
        header: () => h('div', { class: 'text-left' }, 'Ref #'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left' }, row.getValue('ref'))
        },
    },
    {
        accessorKey: 'ref',
        header: () => h('div', { class: 'text-left' }, 'Ref #'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left' }, row.getValue('ref'))
        },
    },
    {
        accessorKey: 'total_amount',
        header: () => h('div', { class: 'text-right' }, 'Amount'),
        cell: ({ row }) => {
            const amount = Number.parseFloat(row.getValue('total_amount'))
            const formatted = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            }).format(amount)

            return h('div', { class: 'text-right font-medium' }, formatted)
        },
    }
]
