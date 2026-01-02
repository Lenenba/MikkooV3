<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { type BreadcrumbItem } from '@/types'

type Metrics = {
    users: {
        parents: number
        babysitters: number
        superadmins: number
    }
    reservations: {
        total: number
        pending: number
        confirmed: number
    }
    invoices: {
        issued: number
        paid: number
    }
    announcements: {
        open: number
        total: number
    }
}

const page = usePage()
const metrics = computed<Metrics>(() => (page.props as { metrics?: Metrics }).metrics ?? {
    users: { parents: 0, babysitters: 0, superadmins: 0 },
    reservations: { total: 0, pending: 0, confirmed: 0 },
    invoices: { issued: 0, paid: 0 },
    announcements: { open: 0, total: 0 },
})

const { t, locale } = useI18n()
const resolvedLocale = computed(() => (locale.value === 'fr' ? 'fr-CA' : 'en-US'))
const formatCount = (value: number) => new Intl.NumberFormat(resolvedLocale.value).format(value)

const breadcrumbs = computed<BreadcrumbItem[]>(() => ([
    {
        title: t('superadmin.consultations.title'),
        href: '/superadmin/consultations',
    },
]))
</script>

<template>
    <Head :title="$t('superadmin.consultations.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur">
                <h1 class="text-2xl font-semibold text-foreground">{{ $t('superadmin.consultations.title') }}</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ $t('superadmin.consultations.description') }}
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        {{ $t('superadmin.consultations.cards.users.title') }}
                    </p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">
                        {{ formatCount(metrics.users.parents + metrics.users.babysitters) }}
                    </p>
                    <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                        <p>{{ $t('superadmin.consultations.cards.users.parents', { count: formatCount(metrics.users.parents) }) }}</p>
                        <p>{{ $t('superadmin.consultations.cards.users.babysitters', { count: formatCount(metrics.users.babysitters) }) }}</p>
                        <p>{{ $t('superadmin.consultations.cards.users.superadmins', { count: formatCount(metrics.users.superadmins) }) }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        {{ $t('superadmin.consultations.cards.reservations.title') }}
                    </p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">{{ formatCount(metrics.reservations.total) }}</p>
                    <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                        <p>{{ $t('superadmin.consultations.cards.reservations.pending', { count: formatCount(metrics.reservations.pending) }) }}</p>
                        <p>{{ $t('superadmin.consultations.cards.reservations.confirmed', { count: formatCount(metrics.reservations.confirmed) }) }}</p>
                    </div>
                    <Button as-child size="sm" class="mt-4 w-full">
                        <Link href="/reservations">{{ $t('superadmin.consultations.cards.reservations.action') }}</Link>
                    </Button>
                </div>

                <div class="rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        {{ $t('superadmin.consultations.cards.invoices.title') }}
                    </p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">{{ formatCount(metrics.invoices.issued + metrics.invoices.paid) }}</p>
                    <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                        <p>{{ $t('superadmin.consultations.cards.invoices.issued', { count: formatCount(metrics.invoices.issued) }) }}</p>
                        <p>{{ $t('superadmin.consultations.cards.invoices.paid', { count: formatCount(metrics.invoices.paid) }) }}</p>
                    </div>
                    <Button as-child size="sm" class="mt-4 w-full" variant="outline">
                        <Link href="/invoices">{{ $t('superadmin.consultations.cards.invoices.action') }}</Link>
                    </Button>
                </div>

                <div class="rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        {{ $t('superadmin.consultations.cards.announcements.title') }}
                    </p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">{{ formatCount(metrics.announcements.total) }}</p>
                    <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                        <p>{{ $t('superadmin.consultations.cards.announcements.open', { count: formatCount(metrics.announcements.open) }) }}</p>
                        <p>{{ $t('superadmin.consultations.cards.announcements.total', { count: formatCount(metrics.announcements.total) }) }}</p>
                    </div>
                    <Button as-child size="sm" class="mt-4 w-full" variant="outline">
                        <Link href="/announcements">{{ $t('superadmin.consultations.cards.announcements.action') }}</Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
