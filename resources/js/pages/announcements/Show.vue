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

const childLabel = computed(() => {
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
            <div class="rounded-sm border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-2xl font-semibold text-gray-900">
                            {{ announcement?.title ?? 'Annonce' }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
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
                    <div class="rounded-sm border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Enfant concerne
                        </p>
                        <p class="mt-2 text-lg font-semibold text-gray-900">
                            {{ childLabel || '-' }}
                        </p>
                        <p v-if="announcement?.child_notes" class="mt-2 text-sm text-gray-600">
                            {{ announcement.child_notes }}
                        </p>
                    </div>

                    <div class="rounded-sm border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Details de la demande
                        </p>
                        <p class="mt-2 text-sm text-gray-700">
                            {{ announcement?.description || 'Aucun detail fourni pour le moment.' }}
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-sm border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Parent
                        </p>
                        <p class="mt-2 text-sm font-semibold text-gray-900">
                            {{ announcement?.parent?.name ?? '-' }}
                        </p>
                        <p v-if="announcement?.parent?.city" class="text-sm text-gray-600">
                            {{ announcement.parent.city }}
                        </p>
                    </div>

                    <div class="rounded-sm border border-gray-100 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Infos utiles
                        </p>
                        <div class="mt-2 space-y-1 text-sm text-gray-600">
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
