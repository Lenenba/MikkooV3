<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Reservation, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import DataTable from '@/components/Reservation/data-table.vue';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mes reservations',
        href: '/reservations',
    },
];

// Shared page props
const page = usePage<SharedData>();

// Raw reservations list
const reservations = computed<Reservation[]>(
    () => page.props.reservations as Reservation[]
);
</script>

<template>

    <Head title="Mes reservations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="container py-10 mx-auto">
                <DataTable :data="reservations" />
            </div>
        </div>
    </AppLayout>
</template>
