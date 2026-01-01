<script setup lang="ts">
import { computed, ref } from 'vue'
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

const tableColumns = computed(() => getAnnouncementColumns())

const statusOptions = [
    { value: 'all', label: 'Tous les statuts' },
    { value: 'open', label: 'Ouverte' },
    { value: 'closed', label: 'Fermee' },
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
const uniqueServiceCount = computed(() => {
    const services = announcements.value
        .map((item) => item.service?.trim())
        .filter((value): value is string => Boolean(value))

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mes annonces',
        href: '/announcements',
    },
]

const isAnnouncementDialogOpen = ref(false)
const announcementForm = useForm({
    title: '',
    service: '',
    child_ids: [] as number[],
    child_notes: '',
    description: '',
})

const resetAnnouncementForm = () => {
    announcementForm.reset()
    announcementForm.clearErrors()
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

const childPhotoFallback = (child: AnnouncementChild) => {
    const name = (child.name ?? '').toString().trim()
    return name ? name.charAt(0).toUpperCase() : '?'
}

const submitAnnouncement = () => {
    announcementForm.post(route('announcements.store'), {
        preserveScroll: true,
        onSuccess: () => {
            resetAnnouncementForm()
            isAnnouncementDialogOpen.value = false
        },
    })
}
</script>

<template>
    <Head title="Mes annonces" />

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

        <Dialog v-model:open="isAnnouncementDialogOpen">
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
                        <Label for="announcement-service">Service recherche</Label>
                        <Input
                            id="announcement-service"
                            v-model="announcementForm.service"
                            list="service-suggestions"
                            placeholder="ex: Lavage, Garde reguliere"
                        />
                        <datalist id="service-suggestions">
                            <option
                                v-for="suggestion in announcementSuggestions"
                                :key="suggestion"
                                :value="suggestion"
                            />
                        </datalist>
                        <InputError :message="announcementForm.errors.service" />
                    </div>

                    <div class="space-y-2">
                        <Label>Enfant(s) concernes</Label>
                        <div v-if="availableChildren.length" class="grid gap-3 sm:grid-cols-2">
                            <label
                                v-for="child in availableChildren"
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
                                            v-if="child.photo"
                                            :src="child.photo"
                                            :alt="child.name ?? 'Enfant'"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else>{{ childPhotoFallback(child) }}</span>
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
