<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { type Announcement, type AnnouncementApplication, type BreadcrumbItem } from '@/types';
import { resolveChildPhoto } from '@/lib/childPhotos';

interface AnnouncementPageProps {
    announcement?: Announcement | null;
    viewerRole?: string;
    applications?: AnnouncementApplication[] | null;
    myApplication?: AnnouncementApplication | null;
}

const page = usePage();
const props = computed(() => page.props as AnnouncementPageProps);
const announcement = computed(() => props.value.announcement ?? null);
const viewerRole = computed(() => props.value.viewerRole ?? 'Parent');
const applications = computed(() => props.value.applications ?? []);
const myApplication = computed(() => props.value.myApplication ?? null);

const backHref = computed(() =>
    viewerRole.value === 'Babysitter' ? '/dashboard#annonces' : '/announcements'
);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: viewerRole.value === 'Babysitter' ? 'Tableau de bord' : 'Mes annonces',
        href: backHref.value,
    },
    {
        title: 'Annonce',
        href: announcement.value ? `/announcements/${announcement.value.id}` : backHref.value,
    },
]);

const formatDate = (value?: string | null) => {
    if (!value) {
        return '-';
    }
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }
    return new Intl.DateTimeFormat('fr-CA', { dateStyle: 'medium' }).format(parsed);
};

const children = computed(() => announcement.value?.children ?? []);
const childPhotoUrl = (child: { photo?: string | null; name?: string | null; age?: string | number | null }, index: number) =>
    resolveChildPhoto(child.photo, [child.name, child.age], index);

const formatChildAge = (value?: string | number | null) => {
    if (value === null || value === undefined || value === '') {
        return '';
    }
    const raw = value.toString().trim();
    if (!raw) {
        return '';
    }
    return /^\d+$/.test(raw) ? `${raw} ans` : raw;
};

const childLabel = computed(() => {
    const names = children.value
        .map((child) => (child?.name ?? '').toString().trim())
        .filter(Boolean);
    if (names.length) {
        return names.join(', ');
    }
    const parts = [announcement.value?.child_name, announcement.value?.child_age]
        .map((item) => (item ?? '').toString().trim())
        .filter(Boolean);
    return parts.join(' · ');
});

const statusMeta = computed(() => {
    const key = (announcement.value?.status ?? 'open').toString().toLowerCase();
    if (key === 'closed') {
        return { label: 'Pourvue', className: 'bg-slate-100 text-slate-600' };
    }
    return { label: 'Ouverte', className: 'bg-emerald-50 text-emerald-700' };
});

const formatTimeValue = (value?: string | null) => {
    if (!value) {
        return '';
    }
    const parts = value.split(':');
    if (!parts.length) {
        return value;
    }
    const hours = parts[0] ?? '';
    const minutes = parts[1] ?? '';
    return hours && minutes ? `${hours}:${minutes}` : value;
};

const scheduleDateLabel = computed(() => formatDate(announcement.value?.start_date ?? null));
const scheduleTimeLabel = computed(() => {
    const start = formatTimeValue(announcement.value?.start_time ?? null);
    const end = formatTimeValue(announcement.value?.end_time ?? null);
    if (!start || !end) {
        return '-';
    }
    return `${start} - ${end}`;
});

const scheduleMeta = computed(() => {
    if (!announcement.value) {
        return '-';
    }
    const type = (announcement.value.schedule_type ?? 'single').toString().toLowerCase();
    if (type !== 'recurring') {
        return 'Unique';
    }
    const frequency = announcement.value.recurrence_frequency ?? 'weekly';
    const interval = announcement.value.recurrence_interval ?? 1;
    const days = announcement.value.recurrence_days ?? [];
    const dayLabelMap: Record<number, string> = {
        1: 'Lun',
        2: 'Mar',
        3: 'Mer',
        4: 'Jeu',
        5: 'Ven',
        6: 'Sam',
        7: 'Dim',
    };
    const dayLabel = days.length ? days.map((day) => dayLabelMap[day] ?? day).join(', ') : 'Semaine';
    return `Recurrence ${frequency} (toutes les ${interval}) - ${dayLabel}`;
});

const isBabysitter = computed(() => viewerRole.value === 'Babysitter');
const isParent = computed(() => viewerRole.value === 'Parent');
const applicationStatus = computed(() => myApplication.value?.status ?? null);
const canApply = computed(() => isBabysitter.value && !myApplication.value && (announcement.value?.status ?? 'open') === 'open');
const canWithdraw = computed(() => isBabysitter.value && applicationStatus.value === 'pending');

const applicationMessage = ref('');
const isSubmitting = ref(false);
const defaultBabysitterPhoto = '/bbsiter.png';

const submitApplication = () => {
    if (!announcement.value || !canApply.value) {
        return;
    }
    router.post(
        route('announcements.apply', { announcement: announcement.value.id }),
        { message: applicationMessage.value },
        {
            preserveScroll: true,
            onStart: () => {
                isSubmitting.value = true;
            },
            onFinish: () => {
                isSubmitting.value = false;
                applicationMessage.value = '';
            },
        },
    );
};

const withdrawApplication = () => {
    if (!announcement.value || !myApplication.value) {
        return;
    }
    router.post(
        route('announcements.applications.withdraw', {
            announcement: announcement.value.id,
            application: myApplication.value.id,
        }),
        {},
        { preserveScroll: true },
    );
};

const formatDateTime = (value?: string | null) => {
    if (!value) {
        return '-';
    }
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }
    return new Intl.DateTimeFormat('fr-CA', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(parsed);
};

const applicationStatusMeta = (status?: string | null) => {
    const key = (status ?? '').toString().toLowerCase();
    const map: Record<string, { label: string; className: string }> = {
        pending: { label: 'En attente', className: 'bg-amber-50 text-amber-700' },
        accepted: { label: 'Confirmee', className: 'bg-emerald-50 text-emerald-700' },
        rejected: { label: 'Refusee', className: 'bg-red-50 text-red-700' },
        expired: { label: 'Expiree', className: 'bg-slate-100 text-slate-600' },
        withdrawn: { label: 'Retiree', className: 'bg-slate-100 text-slate-600' },
    };
    return map[key] ?? { label: key || 'Inconnu', className: 'bg-muted text-foreground' };
};

const acceptApplication = (applicationId: number) => {
    if (!announcement.value) {
        return;
    }
    router.post(
        route('announcements.applications.accept', {
            announcement: announcement.value.id,
            application: applicationId,
        }),
        {},
        { preserveScroll: true },
    );
};

const rejectApplication = (applicationId: number) => {
    if (!announcement.value) {
        return;
    }
    router.post(
        route('announcements.applications.reject', {
            announcement: announcement.value.id,
            application: applicationId,
        }),
        {},
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="Annonce" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="rounded-sm border border-border bg-card p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold text-foreground">
                            {{ announcement?.title ?? 'Annonce' }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                            <Badge class="border-transparent bg-sky-100 text-sky-700">
                                {{ announcement?.service ?? '-' }}
                            </Badge>
                            <Badge :class="['border-transparent', statusMeta.className]">
                                {{ statusMeta.label }}
                            </Badge>
                            <span>Publiee le {{ formatDate(announcement?.created_at) }}</span>
                        </div>
                    </div>
                    <Button variant="outline" as-child>
                        <Link :href="backHref">Retour</Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Enfant concerne
                        </p>
                        <div v-if="children.length" class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="(child, index) in children"
                                :key="child.id ?? `${child.name ?? 'child'}-${index}`"
                                class="flex items-center gap-3 rounded-md border border-border/60 bg-background/70 p-3"
                            >
                                <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-xs font-semibold text-slate-600">
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
                                    <p v-if="child.allergies" class="text-xs text-muted-foreground">
                                        Allergies: {{ child.allergies }}
                                    </p>
                                    <p v-if="child.details" class="text-xs text-muted-foreground">
                                        {{ child.details }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="mt-2 text-lg font-semibold text-foreground">
                            {{ childLabel || '-' }}
                        </p>
                        <p v-if="announcement?.child_notes" class="mt-2 text-sm text-muted-foreground">
                            {{ announcement.child_notes }}
                        </p>
                    </div>

                    <div class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Details de la demande
                        </p>
                        <p class="mt-2 text-sm text-foreground">
                            {{ announcement?.description || 'Aucun detail fourni pour le moment.' }}
                        </p>
                    </div>

                    <div v-if="isParent" class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Candidatures
                            </p>
                            <span class="text-xs text-muted-foreground">{{ applications.length }} au total</span>
                        </div>
                        <div v-if="applications.length" class="mt-4 space-y-4">
                            <div
                                v-for="application in applications"
                                :key="application.id"
                                class="rounded-xl border border-border/60 bg-background/70 p-4"
                            >
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        <div class="h-12 w-12 overflow-hidden rounded-lg bg-muted">
                                            <img
                                                :src="application.babysitter?.profile_picture || defaultBabysitterPhoto"
                                                :alt="application.babysitter?.name ?? 'Babysitter'"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-sm font-semibold text-foreground">
                                                {{ application.babysitter?.name ?? 'Babysitter' }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ application.babysitter?.city ?? '-' }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                Note: {{ application.babysitter?.rating_avg ?? '-' }}
                                                <span v-if="application.babysitter?.rating_count">
                                                    ({{ application.babysitter.rating_count }})
                                                </span>
                                            </p>
                                            <p v-if="application.babysitter?.price_per_hour" class="text-xs text-muted-foreground">
                                                Tarif: {{ application.babysitter.price_per_hour }} / h
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 text-xs text-muted-foreground sm:items-end">
                                        <Badge :class="['border-transparent', applicationStatusMeta(application.status).className]">
                                            {{ applicationStatusMeta(application.status).label }}
                                        </Badge>
                                        <span>Envoyee le {{ formatDateTime(application.created_at) }}</span>
                                        <span v-if="application.expires_at && application.status === 'pending'">
                                            Expire le {{ formatDateTime(application.expires_at) }}
                                        </span>
                                    </div>
                                </div>

                                <p v-if="application.message" class="mt-3 text-sm text-muted-foreground">
                                    Message: {{ application.message }}
                                </p>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <Button
                                        v-if="application.status === 'pending' && announcement?.status === 'open'"
                                        size="sm"
                                        @click="acceptApplication(application.id)"
                                    >
                                        Accepter
                                    </Button>
                                    <Button
                                        v-if="application.status === 'pending' && announcement?.status === 'open'"
                                        size="sm"
                                        variant="outline"
                                        @click="rejectApplication(application.id)"
                                    >
                                        Refuser
                                    </Button>
                                    <Button
                                        v-if="application.status === 'accepted' && application.reservation_id"
                                        size="sm"
                                        variant="outline"
                                        as-child
                                    >
                                        <Link :href="route('reservations.show', application.reservation_id)">
                                            Voir la reservation
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="mt-4 text-sm text-muted-foreground">
                            Aucune candidature pour le moment.
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Parent
                        </p>
                        <p class="mt-2 text-sm font-semibold text-foreground">
                            {{ announcement?.parent?.name ?? '-' }}
                        </p>
                        <p v-if="announcement?.parent?.city" class="text-sm text-muted-foreground">
                            {{ announcement.parent.city }}
                        </p>
                    </div>

                    <div class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Infos utiles
                        </p>
                        <div class="mt-2 space-y-1 text-sm text-muted-foreground">
                            <p>Service: {{ announcement?.service ?? '-' }}</p>
                            <p>Statut: {{ statusMeta.label }}</p>
                            <p>Date: {{ scheduleDateLabel }}</p>
                            <p>Heure: {{ scheduleTimeLabel }}</p>
                            <p>Recurrence: {{ scheduleMeta }}</p>
                            <p v-if="announcement?.schedule_type === 'recurring' && announcement?.recurrence_end_date">
                                Fin: {{ formatDate(announcement.recurrence_end_date) }}
                            </p>
                            <p v-if="announcement?.location">Lieu: {{ announcement.location }}</p>
                            <p>Publiee: {{ formatDate(announcement?.created_at) }}</p>
                        </div>
                    </div>

                    <div v-if="isBabysitter" class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Candidature
                        </p>
                        <div class="mt-3 space-y-3">
                            <div v-if="myApplication" class="space-y-2">
                                <Badge :class="['border-transparent', applicationStatusMeta(myApplication.status).className]">
                                    {{ applicationStatusMeta(myApplication.status).label }}
                                </Badge>
                                <p class="text-sm text-muted-foreground">
                                    Envoyee le {{ formatDateTime(myApplication.created_at) }}
                                </p>
                                <p v-if="myApplication.message" class="text-sm text-muted-foreground">
                                    Message: {{ myApplication.message }}
                                </p>
                                <Button v-if="canWithdraw" variant="outline" size="sm" @click="withdrawApplication">
                                    Retirer
                                </Button>
                            </div>
                            <div v-else>
                                <p v-if="announcement?.status !== 'open'" class="text-sm text-muted-foreground">
                                    Cette annonce est pourvue.
                                </p>
                                <div v-else class="space-y-3">
                                    <p class="text-sm text-muted-foreground">
                                        Envoyez votre proposition au parent.
                                    </p>
                                    <Textarea
                                        v-model="applicationMessage"
                                        rows="3"
                                        placeholder="Votre message (optionnel)"
                                    />
                                    <Button :disabled="isSubmitting || !canApply" @click="submitApplication">
                                        Se proposer
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
