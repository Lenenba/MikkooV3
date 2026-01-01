import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import type { Announcement } from '@/types'
import DataTableColumnHeader from '@/components/columnHeader.vue'
import DropdownAction from '@/components/Announcement/data-table-dropdown.vue'

const headerClass = 'text-xs font-semibold uppercase tracking-wide text-muted-foreground'

const renderTitleCell = (announcement: Announcement) => {
    const title = announcement.title ?? '-'
    const description = announcement.description ?? ''

    return h('div', { class: 'flex max-w-[260px] flex-col' }, [
        h('span', { class: 'truncate text-sm font-semibold text-foreground' }, title),
        description
            ? h('span', { class: 'text-xs text-muted-foreground' }, description)
            : h('span', { class: 'text-xs text-muted-foreground/70' }, '-'),
    ])
}

const renderServiceCell = (service?: string | null) => {
    const label = service?.trim() || '-'
    return h('span', { class: 'rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700' }, label)
}

const renderChildCell = (announcement: Announcement) => {
    const children = (announcement.children ?? []).filter(Boolean)
    const names = children
        .map((child) => (child?.name ?? '').toString().trim())
        .filter(Boolean)
    const nameLabel = names.length ? names.join(', ') : (announcement.child_name?.trim() || '')
    const ageValue = announcement.child_age?.toString().trim() || ''
    const label = [nameLabel, ageValue].filter(Boolean).join(' · ') || '-'
    const notes = announcement.child_notes?.trim() || ''
    const countLabel = names.length > 1 ? `${names.length} enfants` : ''

    return h('div', { class: 'flex max-w-[220px] flex-col' }, [
        h('span', { class: 'text-sm font-medium text-foreground' }, label),
        notes
            ? h('span', { class: 'text-xs text-muted-foreground' }, notes)
            : countLabel
                ? h('span', { class: 'text-xs text-muted-foreground' }, countLabel)
                : h('span', { class: 'text-xs text-muted-foreground/70' }, '-'),
    ])
}

const renderStatusCell = (status?: string | null) => {
    const key = (status ?? '').toString().toLowerCase()
    const statusMap: Record<string, { text: string; classes: string }> = {
        open: {
            text: 'Ouverte',
            classes: 'bg-emerald-50 text-emerald-700',
        },
        closed: {
            text: 'Fermee',
            classes: 'bg-slate-100 text-slate-600',
        },
    }

    const mapped = statusMap[key] ?? {
        text: key || 'Inconnu',
        classes: 'bg-muted text-foreground',
    }

    return h('span', { class: `rounded-full px-2.5 py-1 text-xs font-semibold ${mapped.classes}` }, mapped.text)
}

export const getAnnouncementColumns = (): ColumnDef<Announcement>[] => [
    {
        accessorKey: 'title',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Titre', class: headerClass }),
        cell: ({ row }) => renderTitleCell(row.original),
    },
    {
        accessorKey: 'child_name',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Enfant', class: headerClass }),
        cell: ({ row }) => renderChildCell(row.original),
    },
    {
        accessorKey: 'service',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Service', class: headerClass }),
        cell: ({ row }) => renderServiceCell(row.getValue('service')),
    },
    {
        accessorKey: 'status',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Statut', class: headerClass }),
        cell: ({ row }) => renderStatusCell(row.getValue('status')),
    },
    {
        accessorKey: 'created_at',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Cree le', class: headerClass }),
        cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.getValue('created_at') ?? '-'),
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => h('div', { class: 'flex justify-end' }, h(DropdownAction, { announcement: row.original })),
    },
]
