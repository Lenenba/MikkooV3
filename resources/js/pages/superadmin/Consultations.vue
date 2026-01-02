<script setup lang="ts">
import { computed } from 'vue'
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

const numberFormatter = new Intl.NumberFormat('en-US')
const formatCount = (value: number) => numberFormatter.format(value)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Consultations',
        href: '/superadmin/consultations',
    },
]
</script>

<template>
    <Head title="Consultations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur">
                <h1 class="text-2xl font-semibold text-foreground">Consultations</h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Vue globale de la plateforme pour le super admin.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Utilisateurs</p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">
                        {{ formatCount(metrics.users.parents + metrics.users.babysitters) }}
                    </p>
                    <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                        <p>Parents: {{ formatCount(metrics.users.parents) }}</p>
                        <p>Babysitters: {{ formatCount(metrics.users.babysitters) }}</p>
                        <p>Super admins: {{ formatCount(metrics.users.superadmins) }}</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Reservations</p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">{{ formatCount(metrics.reservations.total) }}</p>
                    <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                        <p>En attente: {{ formatCount(metrics.reservations.pending) }}</p>
                        <p>Confirmees: {{ formatCount(metrics.reservations.confirmed) }}</p>
                    </div>
                    <Button as-child size="sm" class="mt-4 w-full">
                        <Link href="/reservations">Voir toutes les reservations</Link>
                    </Button>
                </div>

                <div class="rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Factures</p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">{{ formatCount(metrics.invoices.issued + metrics.invoices.paid) }}</p>
                    <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                        <p>Emises: {{ formatCount(metrics.invoices.issued) }}</p>
                        <p>Payees: {{ formatCount(metrics.invoices.paid) }}</p>
                    </div>
                    <Button as-child size="sm" class="mt-4 w-full" variant="outline">
                        <Link href="/invoices">Consulter les factures</Link>
                    </Button>
                </div>

                <div class="rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Annonces</p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">{{ formatCount(metrics.announcements.total) }}</p>
                    <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                        <p>Ouvertes: {{ formatCount(metrics.announcements.open) }}</p>
                        <p>Au total: {{ formatCount(metrics.announcements.total) }}</p>
                    </div>
                    <Button as-child size="sm" class="mt-4 w-full" variant="outline">
                        <Link href="/announcements">Voir les annonces</Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
