<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { type Announcement, type AnnouncementChild, type BreadcrumbItem } from '@/types'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import DataTable from '@/components/Reservation/data-table.vue'
import { getAnnouncementColumns } from '@/components/Announcement/columns'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    CalendarCheck,
    CheckCircle,
    Layers,
    Megaphone,
    Plus,
    TrendingUp,
    XCircle,
} from 'lucide-vue-next'
import { Checkbox } from '@/components/ui/checkbox'
import { resolveChildPhoto } from '@/lib/childPhotos'

interface AnnouncementsPayload {
    items: Announcement[]
    suggestions: string[]
    children?: AnnouncementChild[]
}

const page = usePage()
const announcementsPayload = computed(() => page.props.announcements as AnnouncementsPayload | undefined)

const announcements = computed(() => announcementsPayload.value?.items ?? [])
const announcementSuggestions = computed(() => announcementsPayload.value?.suggestions ?? [])
const availableChildren = computed(() => announcementsPayload.value?.children ?? [])

const role = computed(() => (page.props.auth?.role ?? '').toString().toLowerCase())
const isAdmin = computed(() => role.value === 'superadmin' || role.value === 'admin')
const tableColumns = computed(() => getAnnouncementColumns(role.value))

const statusOptions = [
    { value: 'all', label: 'Tous les statuts' },
    { value: 'open', label: 'Ouverte' },
    { value: 'closed', label: 'Pourvue' },
]

const numberFormatter = new Intl.NumberFormat('en-US')
const formatCount = (value: number) => numberFormatter.format(value)

const totalCount = computed(() => announcements.value.length)
const openCount = computed(() =>
    announcements.value.filter((item) => (item.status ?? '').toString().toLowerCase() !== 'closed').length,
)
const closedCount = computed(() => Math.max(0, totalCount.value - openCount.value))
const recentCount = computed(() => {
    const cutoff = new Date()
    cutoff.setDate(cutoff.getDate() - 30)

    return announcements.value.filter((item) => {
        if (!item.created_at) {
            return false
        }
        const createdAt = new Date(item.created_at)
        if (Number.isNaN(createdAt.getTime())) {
            return false
        }
        return createdAt >= cutoff
    }).length
})
const resolveAnnouncementServices = (announcement: Announcement) => {
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

const uniqueServiceCount = computed(() => {
    const services = announcements.value.flatMap((item) => resolveAnnouncementServices(item))

    return new Set(services).size
})

const sparklinePoints = (values: number[]) => {
    if (!values.length) {
        return ''
    }
    const width = 120
    const height = 40
    const max = Math.max(...values)
    const min = Math.min(...values)
    const range = max - min || 1
    const step = values.length > 1 ? width / (values.length - 1) : width

    return values
        .map((value, index) => {
            const x = Number((index * step).toFixed(2))
            const y = Number((height - ((value - min) / range) * height).toFixed(2))
            return `${x},${y}`
        })
        .join(' ')
}

const buildTrendSeries = (previous: number, current: number) => {
    const start = previous
    const end = current
    const length = 10
    const step = (end - start) / Math.max(length - 1, 1)
    const variance = Math.abs(end - start) * 0.08

    return Array.from({ length }, (_, index) => {
        const base = start + step * index
        const wave = Math.sin(index * 0.9) * variance
        return Math.max(0, Number((base + wave).toFixed(2)))
    })
}

const statCards = computed(() => [
    {
        title: 'Annonces ouvertes',
        value: formatCount(openCount.value),
        change: '',
        changeText: 'actives',
        trendIcon: TrendingUp,
        trendClass: 'text-muted-foreground/70',
        showTrend: false,
        icon: CheckCircle,
        iconClass: 'bg-emerald-100 text-emerald-600',
        sparkline: buildTrendSeries(Math.max(0, openCount.value - 1), openCount.value),
        sparklineClass: 'stroke-emerald-400',
    },
    {
        title: 'Annonces fermees',
        value: formatCount(closedCount.value),
        change: '',
        changeText: 'au total',
        trendIcon: TrendingUp,
        trendClass: 'text-muted-foreground/70',
        showTrend: false,
        icon: XCircle,
        iconClass: 'bg-rose-100 text-rose-600',
        sparkline: buildTrendSeries(Math.max(0, closedCount.value - 1), closedCount.value),
        sparklineClass: 'stroke-rose-400',
    },
    {
        title: 'Total annonces',
        value: formatCount(totalCount.value),
        change: '',
        changeText: 'au total',
        trendIcon: TrendingUp,
        trendClass: 'text-muted-foreground/70',
        showTrend: false,
        icon: Megaphone,
        iconClass: 'bg-violet-100 text-violet-600',
        sparkline: buildTrendSeries(Math.max(0, totalCount.value - 1), totalCount.value),
        sparklineClass: 'stroke-violet-400',
    },
    {
        title: 'Derniers 30 jours',
        value: formatCount(recentCount.value),
        change: '',
        changeText: 'publications',
        trendIcon: TrendingUp,
        trendClass: 'text-muted-foreground/70',
        showTrend: false,
        icon: CalendarCheck,
        iconClass: 'bg-amber-100 text-amber-600',
        sparkline: buildTrendSeries(Math.max(0, recentCount.value - 1), recentCount.value),
        sparklineClass: 'stroke-amber-400',
    },
    {
        title: 'Services differents',
        value: formatCount(uniqueServiceCount.value),
        change: '',
        changeText: 'demandes',
        trendIcon: TrendingUp,
        trendClass: 'text-muted-foreground/70',
        showTrend: false,
        icon: Layers,
        iconClass: 'bg-sky-100 text-sky-600',
        sparkline: buildTrendSeries(Math.max(0, uniqueServiceCount.value - 1), uniqueServiceCount.value),
        sparklineClass: 'stroke-sky-400',
    },
])

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: isAdmin.value ? 'Annonces' : 'Mes annonces',
        href: '/announcements',
    },
])

const pageTitle = computed(() => (isAdmin.value ? 'Annonces' : 'Mes annonces'))

const isAnnouncementDialogOpen = ref(false)
const announcementForm = useForm({
    title: '',
    services: [] as string[],
    child_ids: [] as number[],
    child_notes: '',
    description: '',
    location: '',
    schedule_type: 'single',
    start_date: '',
    start_time: '',
    end_time: '',
    recurrence_frequency: 'weekly',
    recurrence_interval: 1,
    recurrence_days: [] as number[],
    recurrence_end_date: '',
})
const serviceInput = ref('')

const resetAnnouncementForm = () => {
    announcementForm.reset()
    announcementForm.clearErrors()
    serviceInput.value = ''
}

const openAnnouncementDialog = () => {
    resetAnnouncementForm()
    if (availableChildren.value.length === 1 && availableChildren.value[0].id !== undefined) {
        announcementForm.child_ids = [availableChildren.value[0].id]
    }
    isAnnouncementDialogOpen.value = true
}

const toggleChildSelection = (childId: number, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true
    const ids = new Set(announcementForm.child_ids)
    if (isChecked) {
        ids.add(childId)
    } else {
        ids.delete(childId)
    }
    announcementForm.child_ids = Array.from(ids)
}

const recurrenceOptions = [
    { value: 'daily', label: 'Chaque jour' },
    { value: 'weekly', label: 'Chaque semaine' },
    { value: 'monthly', label: 'Chaque mois' },
]

const weekdayOptions = [
    { value: 1, label: 'Lun' },
    { value: 2, label: 'Mar' },
    { value: 3, label: 'Mer' },
    { value: 4, label: 'Jeu' },
    { value: 5, label: 'Ven' },
    { value: 6, label: 'Sam' },
    { value: 7, label: 'Dim' },
]

const getIsoWeekday = (value: string) => {
    if (!value) return null
    const parsed = new Date(`${value}T00:00:00`)
    if (Number.isNaN(parsed.getTime())) return null
    const day = parsed.getDay()
    return day === 0 ? 7 : day
}

const toggleRecurrenceDay = (day: number, checked: boolean | 'indeterminate') => {
    const isChecked = checked === true
    const days = new Set(announcementForm.recurrence_days)
    if (isChecked) {
        days.add(day)
    } else {
        days.delete(day)
    }
    announcementForm.recurrence_days = Array.from(days)
}

const addService = () => {
    const raw = serviceInput.value.trim()
    if (!raw) {
        return
    }
    const parts = raw
        .split(/[,;]+/)
        .map((value) => value.replace(/\s+/g, ' ').trim())
        .filter(Boolean)
    const existing = new Set(announcementForm.services.map((service) => service.toLowerCase()))
    const updated = [...announcementForm.services]
    for (const part of parts) {
        const key = part.toLowerCase()
        if (!existing.has(key)) {
            existing.add(key)
            updated.push(part)
        }
    }
    announcementForm.services = updated
    serviceInput.value = ''
}

const removeService = (service: string) => {
    announcementForm.services = announcementForm.services.filter((item) => item !== service)
}

watch(
    () => [announcementForm.schedule_type, announcementForm.recurrence_frequency, announcementForm.start_date],
    () => {
        if (announcementForm.schedule_type !== 'recurring') {
            return
        }
        if (!announcementForm.recurrence_interval || Number(announcementForm.recurrence_interval) < 1) {
            announcementForm.recurrence_interval = 1
        }
        if (announcementForm.recurrence_frequency === 'weekly' && announcementForm.recurrence_days.length === 0) {
            const defaultDay = getIsoWeekday(announcementForm.start_date)
            if (defaultDay) {
                announcementForm.recurrence_days = [defaultDay]
            }
        }
    }
)

const formatChildAge = (value?: string | number | null) => {
    if (value === null || value === undefined || value === '') {
        return ''
    }
    const raw = value.toString().trim()
    if (!raw) {
        return ''
    }
    return /^\d+$/.test(raw) ? `${raw} ans` : raw
}

const childPhotoUrl = (child: AnnouncementChild, index: number) =>
    resolveChildPhoto(child.photo, [child.name, child.age], index)

const submitAnnouncement = () => {
    if (serviceInput.value.trim()) {
        addService()
    }
    announcementForm.transform((data) => {
        const services = (data.services ?? [])
            .map((value) => value?.toString().trim())
            .filter((value): value is string => Boolean(value))
        const serviceLabel = services.join(', ')

        if (data.schedule_type !== 'recurring') {
            return {
                ...data,
                services,
                service: serviceLabel,
                recurrence_frequency: null,
                recurrence_interval: null,
                recurrence_days: [],
                recurrence_end_date: null,
            }
        }
        return {
            ...data,
            services,
            service: serviceLabel,
        }
    }).post(route('announcements.store'), {
        preserveScroll: true,
        onSuccess: () => {
            resetAnnouncementForm()
            isAnnouncementDialogOpen.value = false
        },
    })
}
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
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
                        <svg class="h-10 w-28" viewBox="0 0 120 40" fill="none">
                            <polyline
                                :points="sparklinePoints(stat.sparkline)"
                                class="fill-none stroke-2"
                                :class="stat.sparklineClass"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-dasharray="2 3"
                            />
                        </svg>
                    </div>
                    <div class="mt-3 flex items-center gap-1 text-xs">
                        <component v-if="stat.showTrend" :is="stat.trendIcon" :class="['h-3 w-3', stat.trendClass]" />
                        <span v-if="stat.change" :class="stat.trendClass">{{ stat.change }}</span>
                        <span class="text-muted-foreground/70">{{ stat.changeText }}</span>
                    </div>
                </div>
            </div>

            <DataTable
                :columns="tableColumns"
                :data="announcements"
                search-column="title"
                search-placeholder="Rechercher une annonce..."
                empty-message="Aucune annonce pour le moment."
            >
                <template #toolbar-filters="{ table }">
                    <Select
                        :model-value="(table.getColumn('status')?.getFilterValue() as string) ?? 'all'"
                        @update:model-value="value => table.getColumn('status')?.setFilterValue(value === 'all' ? undefined : value)"
                    >
                        <SelectTrigger class="h-9 w-full sm:w-48">
                            <SelectValue placeholder="Tous les statuts" />
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
                        Effacer
                    </Button>
                    <Button
                        v-if="!isAdmin"
                        size="sm"
                        class="h-9 w-full bg-emerald-500 text-white hover:bg-emerald-600 sm:w-auto"
                        @click="openAnnouncementDialog"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Nouvelle annonce
                    </Button>
                </template>
            </DataTable>
        </div>

        <Dialog v-if="!isAdmin" v-model:open="isAnnouncementDialogOpen">
            <DialogContent class="rounded-2xl sm:max-w-xl">
                <DialogHeader class="border-b border-border/60 pb-3">
                    <DialogTitle class="text-lg font-semibold text-foreground">
                        Nouvelle annonce
                    </DialogTitle>
                    <DialogDescription class="text-sm text-muted-foreground">
                        Decrivez rapidement le service dont vous avez besoin.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitAnnouncement" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="announcement-title">Titre</Label>
                        <Input
                            id="announcement-title"
                            v-model="announcementForm.title"
                            placeholder="ex: Garde de nuit pour samedi"
                        />
                        <InputError :message="announcementForm.errors.title" />
                    </div>

                    <div class="space-y-2">
                        <Label for="announcement-service">Services recherches</Label>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <Input
                                id="announcement-service"
                                v-model="serviceInput"
                                list="service-suggestions"
                                placeholder="ex: Garde reguliere, Sortie d'ecole"
                                @keydown.enter.prevent="addService"
                            />
                            <Button type="button" variant="outline" class="sm:w-auto" @click="addService">
                                Ajouter
                            </Button>
                        </div>
                        <datalist id="service-suggestions">
                            <option
                                v-for="suggestion in announcementSuggestions"
                                :key="suggestion"
                                :value="suggestion"
                            />
                        </datalist>
                        <InputError :message="announcementForm.errors.services || announcementForm.errors.service" />
                        <div v-if="announcementForm.services.length" class="flex flex-wrap gap-2">
                            <span
                                v-for="service in announcementForm.services"
                                :key="service"
                                class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700"
                            >
                                {{ service }}
                                <button
                                    type="button"
                                    class="text-sky-700/70 transition hover:text-sky-900"
                                    @click="removeService(service)"
                                >
                                    x
                                </button>
                            </span>
                        </div>
                        <p v-else class="text-xs text-muted-foreground">
                            Ajoutez au moins un service pour publier l'annonce.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label>Enfant(s) concernes</Label>
                        <div v-if="availableChildren.length" class="grid gap-3 sm:grid-cols-2">
                            <label
                                v-for="(child, index) in availableChildren"
                                :key="child.id ?? child.name ?? 'child'"
                                class="flex items-center gap-3 rounded-md border border-border/70 bg-background/60 p-3"
                            >
                                <Checkbox
                                    :id="`child-${child.id ?? 'x'}`"
                                    :checked="child.id !== undefined && announcementForm.child_ids.includes(child.id)"
                                    @update:checked="value => child.id !== undefined && toggleChildSelection(child.id, value)"
                                />
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-xs font-semibold text-slate-600">
                                        <img
                                            :src="childPhotoUrl(child, index)"
                                            :alt="child.name ?? 'Enfant'"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-foreground">
                                            {{ child.name || 'Enfant' }}
                                        </p>
                                        <p v-if="formatChildAge(child.age)" class="text-xs text-muted-foreground">
                                            {{ formatChildAge(child.age) }}
                                        </p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div v-else class="rounded-md border border-dashed border-border/70 bg-background/60 p-4 text-sm text-muted-foreground">
                            <p>Ajoutez au moins un enfant dans votre profil pour publier une annonce.</p>
                            <Button variant="outline" size="sm" class="mt-3" as-child>
                                <Link href="/settings/profile">Ajouter un enfant</Link>
                            </Button>
                        </div>
                        <InputError :message="announcementForm.errors.child_ids" />
                    </div>

                    <div class="space-y-2">
                        <Label for="announcement-child-notes">A propos de l'enfant</Label>
                        <Textarea
                            id="announcement-child-notes"
                            v-model="announcementForm.child_notes"
                            rows="3"
                            placeholder="Ce qu'il aime faire, sa routine, besoins particuliers."
                        />
                        <InputError :message="announcementForm.errors.child_notes" />
                    </div>

                    <div class="space-y-2">
                        <Label for="announcement-description">Details de la demande</Label>
                        <Textarea
                            id="announcement-description"
                            v-model="announcementForm.description"
                            rows="3"
                            placeholder="Precisez le contexte, les horaires, et toute information utile."
                        />
                        <InputError :message="announcementForm.errors.description" />
                    </div>

                    <div class="space-y-2">
                        <Label for="announcement-location">Lieu (optionnel)</Label>
                        <Input
                            id="announcement-location"
                            v-model="announcementForm.location"
                            placeholder="ex: Quartier, adresse ou lieu de garde"
                        />
                        <InputError :message="announcementForm.errors.location" />
                    </div>

                    <div class="space-y-3 rounded-xl border border-border/60 bg-background/70 p-4">
                        <div class="flex items-center justify-between">
                            <Label class="text-sm font-semibold text-foreground">Date et heure</Label>
                            <div class="inline-flex rounded-sm border border-border bg-muted/60 p-1">
                                <button
                                    type="button"
                                    class="px-3 py-1 text-xs font-medium rounded-sm transition"
                                    :class="announcementForm.schedule_type === 'single'
                                        ? 'bg-card text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground'"
                                    @click="announcementForm.schedule_type = 'single'"
                                >
                                    Journee unique
                                </button>
                                <button
                                    type="button"
                                    class="px-3 py-1 text-xs font-medium rounded-sm transition"
                                    :class="announcementForm.schedule_type === 'recurring'
                                        ? 'bg-card text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground'"
                                    @click="announcementForm.schedule_type = 'recurring'"
                                >
                                    Recurrence
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="space-y-2 sm:col-span-1">
                                <Label for="announcement-date">
                                    {{ announcementForm.schedule_type === 'recurring' ? 'Date de debut' : 'Date' }}
                                </Label>
                                <Input
                                    id="announcement-date"
                                    type="date"
                                    v-model="announcementForm.start_date"
                                />
                                <InputError :message="announcementForm.errors.start_date" />
                            </div>
                            <div class="space-y-2">
                                <Label for="announcement-start-time">Heure debut</Label>
                                <Input
                                    id="announcement-start-time"
                                    type="time"
                                    v-model="announcementForm.start_time"
                                />
                                <InputError :message="announcementForm.errors.start_time" />
                            </div>
                            <div class="space-y-2">
                                <Label for="announcement-end-time">Heure fin</Label>
                                <Input
                                    id="announcement-end-time"
                                    type="time"
                                    v-model="announcementForm.end_time"
                                />
                                <InputError :message="announcementForm.errors.end_time" />
                            </div>
                        </div>

                        <div v-if="announcementForm.schedule_type === 'recurring'" class="space-y-3">
                            <div class="space-y-2">
                                <Label>Frequence</Label>
                                <Select
                                    :model-value="announcementForm.recurrence_frequency"
                                    @update:model-value="value => (announcementForm.recurrence_frequency = value)"
                                >
                                    <SelectTrigger class="h-9">
                                        <SelectValue placeholder="Choisir une frequence" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="option in recurrenceOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="announcementForm.errors.recurrence_frequency" />
                            </div>

                            <div class="space-y-2">
                                <Label for="announcement-recurrence-interval">Intervalle</Label>
                                <Input
                                    id="announcement-recurrence-interval"
                                    type="number"
                                    min="1"
                                    v-model="announcementForm.recurrence_interval"
                                />
                                <InputError :message="announcementForm.errors.recurrence_interval" />
                            </div>

                            <div v-if="announcementForm.recurrence_frequency === 'weekly'" class="space-y-2">
                                <Label>Jours</Label>
                                <div class="grid grid-cols-7 gap-2 text-xs text-muted-foreground">
                                    <label
                                        v-for="day in weekdayOptions"
                                        :key="day.value"
                                        class="flex items-center gap-1"
                                    >
                                        <Checkbox
                                            :id="`recurrence-${day.value}`"
                                            :checked="announcementForm.recurrence_days.includes(day.value)"
                                            @update:checked="value => toggleRecurrenceDay(day.value, value)"
                                        />
                                        <span>{{ day.label }}</span>
                                    </label>
                                </div>
                                <InputError :message="announcementForm.errors.recurrence_days" />
                            </div>

                            <div class="space-y-2">
                                <Label for="announcement-recurrence-end">Fin de recurrence</Label>
                                <Input
                                    id="announcement-recurrence-end"
                                    type="date"
                                    v-model="announcementForm.recurrence_end_date"
                                />
                                <InputError :message="announcementForm.errors.recurrence_end_date" />
                            </div>
                        </div>
                    </div>

                    <DialogFooter class="mt-4">
                        <DialogClose as-child>
                            <Button type="button" variant="outline" @click="resetAnnouncementForm">
                                Annuler
                            </Button>
                        </DialogClose>
                        <Button type="submit" :disabled="announcementForm.processing">
                            Publier
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
