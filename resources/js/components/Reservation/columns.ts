import { h } from 'vue'
import DropdownAction from '@/components/Reservation/data-table-dropdown.vue'
import { ColumnDef } from '@tanstack/vue-table'
import { Reservation } from '@/types'
import { Checkbox } from '@/components/ui/checkbox'
import DataTableColumnHeader from '@/components/columnHeader.vue'

export const columns: ColumnDef<Reservation>[] = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'modelValue': table.getIsAllPageRowsSelected(),
            'onUpdate:modelValue': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            'modelValue': row.getIsSelected(),
            'onUpdate:modelValue': (value: boolean) => row.toggleSelected(!!value),
            'ariaLabel': 'Select row',
        }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'ref',
        header: ({ column }) => (
            h(DataTableColumnHeader, {
                column: column,
                title: 'ref'
            })
        ),
    },
    {
        accessorKey: 'notes',
        header: () => h('div', { class: 'text-left' }, 'Notes'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left' }, row.getValue('notes'))
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
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const reservation = row.original

            return h('div', { class: 'relative' }, h(DropdownAction, {
                reservation,
            }))
        },
      },
]
