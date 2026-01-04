import { h } from 'vue'
import DropdownAction from '@/components/Reservation/data-table-dropdown.vue'
import type { ColumnDef } from '@tanstack/vue-table'
import type { Reservation } from '@/types'
import DataTableColumnHeader from '@/components/columnHeader.vue'

const headerClass = 'text-xs font-semibold uppercase tracking-wide text-muted-foreground'

const currencyFormatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
})

const formatCurrency = (value: number | string | null | undefined) => {
    if (typeof value === 'number' && Number.isFinite(value)) {
        return currencyFormatter.format(value)
    }
    if (typeof value === 'string') {
        const parsed = Number(value)
        return Number.isFinite(parsed) ? currencyFormatter.format(parsed) : currencyFormatter.format(0)
    }
    return currencyFormatter.format(0)
}

const defaultBabysitterPhoto = '/bbsiter.png'
const defaultParentPhoto = '/parent.png'

type PersonWithMedia = {
    name?: string
    email?: string
    first_name?: string
    last_name?: string
    avatar?: string
    profile_picture?: string
    media?: Array<{ is_profile_picture?: boolean; file_path?: string }>
}

const resolvePersonName = (person: PersonWithMedia | undefined, fallbackLabel: string) => {
    const fullName = [person?.first_name, person?.last_name].filter(Boolean).join(' ').trim()
    return person?.name ?? (fullName || fallbackLabel)
}

const resolvePersonImage = (person: PersonWithMedia | undefined, fallback: string) => {
    if (!person) {
        return fallback
    }
    const media = person.media ?? []
    return person.profile_picture
        ?? person.avatar
        ?? media.find(item => item.is_profile_picture)?.file_path
        ?? media[0]?.file_path
        ?? fallback
}

const renderPersonCell = (
    person: PersonWithMedia | undefined,
    withAvatar: boolean,
    fallback: string,
    fallbackLabel: string,
) => {
    const nameLabel = resolvePersonName(person, fallbackLabel)
    const imageUrl = resolvePersonImage(person, fallback)
    const initial = nameLabel.trim().charAt(0).toUpperCase() || '?'

    if (!withAvatar) {
        return h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'text-sm font-medium text-foreground' }, nameLabel),
            h('span', { class: 'text-xs text-muted-foreground' }, person?.email ?? '-'),
        ])
    }

    return h('div', { class: 'flex items-center gap-3' }, [
        h('div', { class: 'h-9 w-9 shrink-0 overflow-hidden rounded-sm bg-muted' }, [
            imageUrl
                ? h('img', {
                    src: imageUrl,
                    alt: nameLabel,
                    class: 'h-full w-full object-cover',
                })
                : h('div', { class: 'flex h-full w-full items-center justify-center text-xs font-semibold text-muted-foreground' }, initial),
        ]),
        h('div', { class: 'flex flex-col' }, [
            h('span', { class: 'text-sm font-medium text-foreground' }, nameLabel),
            h('span', { class: 'text-xs text-muted-foreground' }, person?.email ?? '-'),
        ]),
    ])
}

const renderDateCell = (details?: { date?: string; start_time?: string; end_time?: string } | Array<{ date?: string; start_time?: string; end_time?: string }>) => {
    const resolved = Array.isArray(details) ? details[0] : details
    const dateLabel = resolved?.date ?? '-'
    const timeLabel = resolved?.start_time && resolved?.end_time
        ? `${resolved.start_time} - ${resolved.end_time}`
        : '-'

    return h('div', { class: 'flex flex-col' }, [
        h('span', { class: 'text-sm text-foreground' }, dateLabel),
        h('span', { class: 'text-xs text-muted-foreground' }, timeLabel),
    ])
}

const getPersonColumn = (
    key: 'babysitter' | 'parent',
    label: string,
    fallbackLabel: string,
): ColumnDef<Reservation> => {
    const fallback = key === 'parent' ? defaultParentPhoto : defaultBabysitterPhoto
    return {
        id: key,
        header: () => h('span', { class: headerClass }, label),
        cell: ({ row }) => renderPersonCell(row.original[key] as PersonWithMedia, true, fallback, fallbackLabel),
        enableSorting: false,
    }
}

const getRoleKey = (role?: string) => (role ?? '').toString().toLowerCase()

type Translator = (key: string) => string

export const getReservationColumns = (role?: string, t?: Translator): ColumnDef<Reservation>[] => {
    const translate = t ?? ((key: string) => key)
    const roleKey = getRoleKey(role)
    const isAdmin = roleKey === 'superadmin' || roleKey === 'admin'
    const fallbackLabel = translate('common.misc.unknown')
    const personColumn = roleKey === 'babysitter'
        ? getPersonColumn('parent', translate('reservations.columns.parent'), fallbackLabel)
        : getPersonColumn('babysitter', translate('reservations.columns.babysitter'), fallbackLabel)

    const columns: ColumnDef<Reservation>[] = [
        {
            accessorKey: 'ref',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('reservations.columns.reference'), class: headerClass }),
            cell: ({ row }) => {
                const refValue = row.getValue('ref') ?? row.original.id
                return h('span', { class: 'text-sm font-semibold text-foreground' }, `#${refValue ?? '-'}`)
            },
        },
    ]

    if (isAdmin) {
        columns.push(getPersonColumn('parent', translate('reservations.columns.parent'), fallbackLabel))
        columns.push(getPersonColumn('babysitter', translate('reservations.columns.babysitter'), fallbackLabel))
    } else {
        columns.push(personColumn)
    }

    columns.push(
        {
            id: 'details',
            header: () => h('span', { class: headerClass }, translate('reservations.columns.date')),
            cell: ({ row }) => renderDateCell(row.original.details as { date?: string; start_time?: string; end_time?: string }),
            enableSorting: false,
        },
        {
            accessorKey: 'total_amount',
            header: () => h('span', { class: headerClass }, translate('reservations.columns.total')),
            cell: ({ row }) =>
                h('span', { class: 'text-sm font-semibold text-foreground' }, formatCurrency(row.getValue('total_amount'))),
        },
        {
            accessorKey: 'status',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('reservations.columns.status'), class: headerClass }),
            cell: ({ row }) => {
                const status = row.getValue<string>('status')
                const statusMap: Record<string, { text: string; classes: string }> = {
                    pending: {
                        text: translate('common.status.pending'),
                        classes: 'bg-amber-50 text-amber-700',
                    },
                    confirmed: {
                        text: translate('common.status.confirmed'),
                        classes: 'bg-emerald-50 text-emerald-700',
                    },
                    in_progress: {
                        text: translate('common.status.in_progress'),
                        classes: 'bg-indigo-50 text-indigo-700',
                    },
                    completed: {
                        text: translate('common.status.completed'),
                        classes: 'bg-sky-50 text-sky-700',
                    },
                    canceled: {
                        text: translate('common.status.canceled'),
                        classes: 'bg-red-50 text-red-700',
                    },
                }

                const mapped = statusMap[status] ?? {
                    text: status ?? translate('common.misc.unknown'),
                    classes: 'bg-muted text-foreground',
                }

                return h('span', { class: `rounded-full px-2.5 py-1 text-xs font-semibold ${mapped.classes}` }, mapped.text)
            },
        },
        {
            accessorKey: 'created_at',
            header: ({ column }) => h(DataTableColumnHeader, { column, title: translate('reservations.columns.created_at'), class: headerClass }),
            cell: ({ row }) => h('span', { class: 'text-sm text-muted-foreground' }, row.getValue('created_at') ?? '-'),
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => h('div', { class: 'flex justify-end' }, h(DropdownAction, { reservation: row.original })),
        },
    )

    return columns
}

export const columns = getReservationColumns()
