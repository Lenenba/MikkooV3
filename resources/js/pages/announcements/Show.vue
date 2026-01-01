<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { type Announcement, type BreadcrumbItem } from '@/types';

interface AnnouncementPageProps {
    announcement?: Announcement | null;
    viewerRole?: string;
}

const page = usePage();
const props = computed(() => page.props as AnnouncementPageProps);
const announcement = computed(() => props.value.announcement ?? null);
const viewerRole = computed(() => props.value.viewerRole ?? 'Parent');

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
        return { label: 'Fermee', className: 'bg-slate-100 text-slate-600' };
    }
    return { label: 'Ouverte', className: 'bg-emerald-50 text-emerald-700' };
});
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
                                        v-if="child.photo"
                                        :src="child.photo"
                                        :alt="child.name ?? 'Enfant'"
                                        class="h-full w-full object-cover"
                                    />
                                    <span v-else>{{ child.name?.charAt(0) ?? '?' }}</span>
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
                            <p>Date: {{ formatDate(announcement?.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
