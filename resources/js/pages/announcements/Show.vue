<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
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
const { t } = useI18n();
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
        title: viewerRole.value === 'Babysitter'
            ? t('nav.dashboard')
            : viewerRole.value === 'SuperAdmin' || viewerRole.value === 'Admin'
                ? t('announcements.title.admin')
                : t('announcements.title.user'),
        href: backHref.value,
    },
    {
        title: t('announcements.show.breadcrumb'),
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
    return /^\d+$/.test(raw) ? t('announcements.child.age', { age: raw }) : raw;
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

const serviceLabels = computed(() => {
    const services = (announcement.value?.services ?? [])
        .map((value) => value?.toString().trim())
        .filter((value): value is string => Boolean(value));

    if (services.length) {
        return services;
    }

    const fallback = announcement.value?.service?.toString().trim() ?? '';
    if (!fallback) {
        return [];
    }

    return fallback
        .split(',')
        .map((value) => value.trim())
        .filter(Boolean);
});

const serviceLabelText = computed(() => (serviceLabels.value.length ? serviceLabels.value.join(', ') : '-'));

const statusMeta = computed(() => {
    const key = (announcement.value?.status ?? 'open').toString().toLowerCase();
    if (key === 'closed') {
        return { label: t('announcements.status.closed'), className: 'bg-slate-100 text-slate-600' };
    }
    return { label: t('announcements.status.open'), className: 'bg-emerald-50 text-emerald-700' };
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
        return t('announcements.schedule.single_label');
    }
    const frequency = announcement.value.recurrence_frequency ?? 'weekly';
    const interval = announcement.value.recurrence_interval ?? 1;
    const days = announcement.value.recurrence_days ?? [];
    const dayLabelMap: Record<number, string> = {
        1: t('announcements.weekdays.mon'),
        2: t('announcements.weekdays.tue'),
        3: t('announcements.weekdays.wed'),
        4: t('announcements.weekdays.thu'),
        5: t('announcements.weekdays.fri'),
        6: t('announcements.weekdays.sat'),
        7: t('announcements.weekdays.sun'),
    };
    const dayLabel = days.length ? days.map((day) => dayLabelMap[day] ?? day).join(', ') : t('announcements.schedule.week');
    return t('announcements.schedule.recurrence_label', {
        frequency: t(`announcements.recurrence.${frequency}`),
        interval,
        days: dayLabel,
    });
});

const isBabysitter = computed(() => viewerRole.value === 'Babysitter');
const isParent = computed(() => viewerRole.value === 'Parent');
const isAdmin = computed(() => viewerRole.value === 'SuperAdmin' || viewerRole.value === 'Admin');
const canReviewApplications = computed(() => isParent.value || isAdmin.value);
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
        pending: { label: t('announcements.applications.status.pending'), className: 'bg-amber-50 text-amber-700' },
        accepted: { label: t('announcements.applications.status.accepted'), className: 'bg-emerald-50 text-emerald-700' },
        rejected: { label: t('announcements.applications.status.rejected'), className: 'bg-red-50 text-red-700' },
        expired: { label: t('announcements.applications.status.expired'), className: 'bg-slate-100 text-slate-600' },
        withdrawn: { label: t('announcements.applications.status.withdrawn'), className: 'bg-slate-100 text-slate-600' },
    };
    return map[key] ?? { label: key || t('common.misc.unknown'), className: 'bg-muted text-foreground' };
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
    <Head :title="$t('announcements.show.head_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="rounded-sm border border-border bg-card p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold text-foreground">
                            {{ announcement?.title ?? $t('announcements.show.title_fallback') }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                            <div class="flex flex-wrap items-center gap-2">
                                <Badge
                                    v-for="service in serviceLabels"
                                    :key="service"
                                    class="border-transparent bg-sky-100 text-sky-700"
                                >
                                    {{ service }}
                                </Badge>
                                <Badge v-if="!serviceLabels.length" class="border-transparent bg-sky-100 text-sky-700">
                                    -
                                </Badge>
                            </div>
                            <Badge :class="['border-transparent', statusMeta.className]">
                                {{ statusMeta.label }}
                            </Badge>
                            <span>{{ $t('announcements.show.published_at', { date: formatDate(announcement?.created_at) }) }}</span>
                        </div>
                    </div>
                    <Button variant="outline" as-child>
                        <Link :href="backHref">{{ $t('common.actions.back') }}</Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {{ $t('announcements.show.child_section') }}
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
                                        :alt="child.name ?? $t('announcements.child.default_name')"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-foreground">
                                        {{ child.name || $t('announcements.child.default_name') }}
                                    </p>
                                    <p v-if="formatChildAge(child.age)" class="text-xs text-muted-foreground">
                                        {{ formatChildAge(child.age) }}
                                    </p>
                                    <p v-if="child.allergies" class="text-xs text-muted-foreground">
                                        {{ $t('common.labels.allergies') }}: {{ child.allergies }}
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
                            {{ $t('announcements.show.details_section') }}
                        </p>
                        <p class="mt-2 text-sm text-foreground">
                            {{ announcement?.description || $t('announcements.show.no_details') }}
                        </p>
                    </div>

                    <div v-if="canReviewApplications" class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                {{ $t('announcements.show.applications_section') }}
                            </p>
                            <span class="text-xs text-muted-foreground">{{ $t('announcements.show.applications_total', { count: applications.length }) }}</span>
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
                                                :alt="application.babysitter?.name ?? $t('common.roles.babysitter')"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-sm font-semibold text-foreground">
                                                {{ application.babysitter?.name ?? $t('common.roles.babysitter') }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ application.babysitter?.city ?? '-' }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ $t('announcements.show.rating') }}: {{ application.babysitter?.rating_avg ?? '-' }}
                                                <span v-if="application.babysitter?.rating_count">
                                                    ({{ application.babysitter.rating_count }})
                                                </span>
                                            </p>
                                            <p v-if="application.babysitter?.price_per_hour" class="text-xs text-muted-foreground">
                                                {{ $t('announcements.show.rate') }}: {{ application.babysitter.price_per_hour }} / h
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2 text-xs text-muted-foreground sm:items-end">
                                        <Badge :class="['border-transparent', applicationStatusMeta(application.status).className]">
                                            {{ applicationStatusMeta(application.status).label }}
                                        </Badge>
                                        <span>{{ $t('announcements.show.sent_at', { date: formatDateTime(application.created_at) }) }}</span>
                                        <span v-if="application.expires_at && application.status === 'pending'">
                                            {{ $t('announcements.show.expires_at', { date: formatDateTime(application.expires_at) }) }}
                                        </span>
                                    </div>
                                </div>

                                <p v-if="application.message" class="mt-3 text-sm text-muted-foreground">
                                    {{ $t('announcements.show.message') }}: {{ application.message }}
                                </p>

                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <Button
                                        v-if="isParent && application.status === 'pending' && announcement?.status === 'open'"
                                        size="sm"
                                        @click="acceptApplication(application.id)"
                                    >
                                        {{ $t('announcements.actions.accept') }}
                                    </Button>
                                    <Button
                                        v-if="isParent && application.status === 'pending' && announcement?.status === 'open'"
                                        size="sm"
                                        variant="outline"
                                        @click="rejectApplication(application.id)"
                                    >
                                        {{ $t('announcements.actions.reject') }}
                                    </Button>
                                    <Button
                                        v-if="application.status === 'accepted' && application.reservation_id"
                                        size="sm"
                                        variant="outline"
                                        as-child
                                    >
                                        <Link :href="route('reservations.show', application.reservation_id)">
                                            {{ $t('announcements.actions.view_reservation') }}
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="mt-4 text-sm text-muted-foreground">
                            {{ $t('announcements.show.no_applications') }}
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {{ $t('common.roles.parent') }}
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
                            {{ $t('announcements.show.info_section') }}
                        </p>
                        <div class="mt-2 space-y-1 text-sm text-muted-foreground">
                            <p>{{ $t('common.labels.service') }}: {{ serviceLabelText }}</p>
                            <p>{{ $t('common.labels.status') }}: {{ statusMeta.label }}</p>
                            <p>{{ $t('common.labels.date') }}: {{ scheduleDateLabel }}</p>
                            <p>{{ $t('common.labels.time') }}: {{ scheduleTimeLabel }}</p>
                            <p>{{ $t('announcements.show.recurrence') }}: {{ scheduleMeta }}</p>
                            <p v-if="announcement?.schedule_type === 'recurring' && announcement?.recurrence_end_date">
                                {{ $t('announcements.show.recurrence_end') }}: {{ formatDate(announcement.recurrence_end_date) }}
                            </p>
                            <p v-if="announcement?.location">{{ $t('common.labels.location') }}: {{ announcement.location }}</p>
                            <p>{{ $t('announcements.show.published') }}: {{ formatDate(announcement?.created_at) }}</p>
                        </div>
                    </div>

                    <div v-if="isBabysitter" class="rounded-sm border border-border bg-card p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {{ $t('announcements.show.application_section') }}
                        </p>
                        <div class="mt-3 space-y-3">
                            <div v-if="myApplication" class="space-y-2">
                                <Badge :class="['border-transparent', applicationStatusMeta(myApplication.status).className]">
                                    {{ applicationStatusMeta(myApplication.status).label }}
                                </Badge>
                                <p class="text-sm text-muted-foreground">
                                    {{ $t('announcements.show.sent_at', { date: formatDateTime(myApplication.created_at) }) }}
                                </p>
                                <p v-if="myApplication.message" class="text-sm text-muted-foreground">
                                    {{ $t('announcements.show.message') }}: {{ myApplication.message }}
                                </p>
                                <Button v-if="canWithdraw" variant="outline" size="sm" @click="withdrawApplication">
                                    {{ $t('announcements.actions.withdraw') }}
                                </Button>
                            </div>
                            <div v-else>
                                <p v-if="announcement?.status !== 'open'" class="text-sm text-muted-foreground">
                                    {{ $t('announcements.show.filled_notice') }}
                                </p>
                                <div v-else class="space-y-3">
                                    <p class="text-sm text-muted-foreground">
                                        {{ $t('announcements.show.apply_hint') }}
                                    </p>
                                    <Textarea
                                        v-model="applicationMessage"
                                        rows="3"
                                        :placeholder="$t('announcements.show.message_placeholder')"
                                    />
                                    <Button :disabled="isSubmitting || !canApply" @click="submitApplication">
                                        {{ $t('announcements.actions.apply') }}
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
