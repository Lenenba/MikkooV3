import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import type { Announcement } from '@/types'
import DataTableColumnHeader from '@/components/columnHeader.vue'
import DropdownAction from '@/components/Announcement/data-table-dropdown.vue'

const headerClass = 'text-xs font-semibold uppercase tracking-wide text-muted-foreground'

const renderTitleCell = (announcement: Announcement, fallbackLabel: string) => {
    const title = announcement.title ?? '-'
    const description = announcement.description ?? ''

    return h('div', { class: 'flex max-w-[260px] flex-col' }, [
        h('span', { class: 'truncate text-sm font-semibold text-foreground' }, title),
        description
            ? h('span', { class: 'text-xs text-muted-foreground' }, description)
            : h('span', { class: 'text-xs text-muted-foreground/70' }, fallbackLabel),
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

const renderServiceCell = (announcement: Announcement, fallbackLabel: string) => {
    const labels = resolveServiceLabels(announcement)
    if (!labels.length) {
        return h('span', { class: 'text-xs text-muted-foreground' }, fallbackLabel)
    }

    return h('div', { class: 'flex flex-wrap gap-1' }, labels.map((label) =>
        h('span', { class: 'rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700' }, label)
    ))
}

const renderChildCell = (announcement: Announcement, fallbackLabel: string, countLabel: string) => {
    const children = (announcement.children ?? []).filter(Boolean)
    const names = children
        .map((child) => (child?.name ?? '').toString().trim())
        .filter(Boolean)
    const nameLabel = names.length ? names.join(', ') : (announcement.child_name?.trim() || '')
    const ageValue = announcement.child_age?.toString().trim() || ''
    const label = [nameLabel, ageValue].filter(Boolean).join(' · ') || '-'
    const notes = announcement.child_notes?.trim() || ''
    const countValue = names.length > 1 ? countLabel : ''

    return h('div', { class: 'flex max-w-[220px] flex-col' }, [
        h('span', { class: 'text-sm font-medium text-foreground' }, label),
        notes
            ? h('span', { class: 'text-xs text-muted-foreground' }, notes)
            : countValue
                ? h('span', { class: 'text-xs text-muted-foreground' }, countValue)
                : h('span', { class: 'text-xs text-muted-foreground/70' }, fallbackLabel),
    ])
}

const renderStatusCell = (
    announcement: Announcement,
    labels: { open: string; closed: string; applications: (count: number) => string; unknown: string },
) => {
    const key = (announcement.status ?? '').toString().toLowerCase()
    const pendingCount = Number(announcement.pending_applications_count ?? 0)
    const statusMap: Record<string, { text: string; classes: string }> = {
        open: {
            text: pendingCount > 0 ? labels.applications(pendingCount) : labels.open,
            classes: pendingCount > 0 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700',
        },
        closed: {
            text: labels.closed,
            classes: 'bg-slate-100 text-slate-600',
        },
    }

    const mapped = statusMap[key] ?? {
        text: key || labels.unknown,
        classes: 'bg-muted text-foreground',
    }

    return h('span', { class: `rounded-full px-2.5 py-1 text-xs font-semibold ${mapped.classes}` }, mapped.text)
}

const getRoleKey = (role?: string) => (role ?? '').toString().toLowerCase()

const renderParentCell = (announcement: Announcement) => {
    const parent = announcement.parent
    const name = parent?.name ?? '-'
    const city = parent?.city ?? '-'
    return h('div', { class: 'flex flex-col' }, [
        h('span', { class: 'text-sm font-medium text-foreground' }, name),
        h('span', { class: 'text-xs text-muted-foreground' }, city),
    ])
}

type Translator = (key: string, params?: Record<string, string | number>) => string

export const getAnnouncementColumns = (role?: string, t?: Translator): ColumnDef<Announcement>[] => {
    const translate = t ?? ((key: string) => key)
    const roleKey = getRoleKey(role)
    const isAdmin = roleKey === 'superadmin' || roleKey === 'admin'
    const fallbackLabel = translate('common.misc.unknown')
    const childCountLabel = (count: number) => translate('announcements.child.count', { count })
    const statusLabels = {
        open: translate('announcements.status.open'),
        closed: translate('announcements.status.closed'),
        applications: (count: number) => translate('announcements.status.applications', { count }),
        unknown: translate('common.misc.unknown'),
    }

    const columns: ColumnDef<Announcement>[] = [
        {
            accessorKey: 'title',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('announcements.columns.title'), class: headerClass }),
            cell: ({ row }) => renderTitleCell(row.original, fallbackLabel),
        },
    ]

    if (isAdmin) {
        columns.push({
            id: 'parent',
            header: () => h('span', { class: headerClass }, translate('announcements.columns.parent')),
            cell: ({ row }) => renderParentCell(row.original),
            enableSorting: false,
        })
    }

    columns.push(
        {
            accessorKey: 'child_name',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('announcements.columns.child'), class: headerClass }),
            cell: ({ row }) => renderChildCell(row.original, fallbackLabel, childCountLabel((row.original.children ?? []).length)),
        },
        {
            accessorKey: 'service',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('announcements.columns.service'), class: headerClass }),
            cell: ({ row }) => renderServiceCell(row.original, fallbackLabel),
        },
        {
            accessorKey: 'status',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('announcements.columns.status'), class: headerClass }),
            cell: ({ row }) => renderStatusCell(row.original, statusLabels),
        },
        {
            accessorKey: 'created_at',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('announcements.columns.created_at'), class: headerClass }),
            cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.getValue('created_at') ?? '-'),
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) =>
                h('div', { class: 'flex justify-end' }, h(DropdownAction, { announcement: row.original, readOnly: isAdmin })),
        },
    )

    return columns
}
