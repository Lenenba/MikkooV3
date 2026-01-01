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
import { type Announcement, type BreadcrumbItem } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
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

interface AnnouncementsPayload {
    items: Announcement[]
    suggestions: string[]
}

const page = usePage()
const announcementsPayload = computed(() => page.props.announcements as AnnouncementsPayload | undefined)

const announcements = computed(() => announcementsPayload.value?.items ?? [])
const announcementSuggestions = computed(() => announcementsPayload.value?.suggestions ?? [])

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
        trendClass: 'text-gray-400',
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
        trendClass: 'text-gray-400',
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
        trendClass: 'text-gray-400',
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
        trendClass: 'text-gray-400',
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
        trendClass: 'text-gray-400',
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
    child_name: '',
    child_age: '',
    child_notes: '',
    description: '',
})

const resetAnnouncementForm = () => {
    announcementForm.reset()
    announcementForm.clearErrors()
}

const openAnnouncementDialog = () => {
    resetAnnouncementForm()
    isAnnouncementDialogOpen.value = true
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
                    class="rounded-sm border border-gray-100 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div :class="['flex h-10 w-10 items-center justify-center rounded-full', stat.iconClass]">
                                <component :is="stat.icon" class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ stat.title }}
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
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
                        <span class="text-gray-400">{{ stat.changeText }}</span>
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
                        <Label for="announcement-child-name">Enfant concerne</Label>
                        <Input
                            id="announcement-child-name"
                            v-model="announcementForm.child_name"
                            placeholder="ex: Lina"
                        />
                        <InputError :message="announcementForm.errors.child_name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="announcement-child-age">Age</Label>
                        <Input
                            id="announcement-child-age"
                            v-model="announcementForm.child_age"
                            placeholder="ex: 4 ans"
                        />
                        <InputError :message="announcementForm.errors.child_age" />
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
