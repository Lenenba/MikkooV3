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

const resolveServiceLabels = (announcement: Announcement) => {
    const services = (announcement.services ?? [])
        .map((value) => value?.toString().trim())
        .filter((value): value is string => Boolean(value))

    if (services.length) {
        return services
    }

    const fallback = announcement.service?.toString().trim() ?? ''
    if (!fallback) {
        return []
    }

    return fallback
        .split(',')
        .map((value) => value.trim())
        .filter(Boolean)
}

const renderServiceCell = (announcement: Announcement) => {
    const labels = resolveServiceLabels(announcement)
    if (!labels.length) {
        return h('span', { class: 'text-xs text-muted-foreground' }, '-')
    }

    return h('div', { class: 'flex flex-wrap gap-1' }, labels.map((label) =>
        h('span', { class: 'rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700' }, label)
    ))
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

const renderStatusCell = (announcement: Announcement) => {
    const key = (announcement.status ?? '').toString().toLowerCase()
    const pendingCount = Number(announcement.pending_applications_count ?? 0)
    const statusMap: Record<string, { text: string; classes: string }> = {
        open: {
            text: pendingCount > 0 ? `Candidatures (${pendingCount})` : 'Ouverte',
            classes: pendingCount > 0 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700',
        },
        closed: {
            text: 'Pourvue',
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
        cell: ({ row }) => renderServiceCell(row.original),
    },
    {
        accessorKey: 'status',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Statut', class: headerClass }),
        cell: ({ row }) => renderStatusCell(row.original),
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
