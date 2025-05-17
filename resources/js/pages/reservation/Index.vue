<script setup lang="ts">
import { columns } from '@/components/Reservation/columns'
import { Button } from '@/components/ui/button';
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
            <div class="border border-gray-100 rounded-sm shadow-xs">
                <div class="mx-5 my-10">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-2">
                            <h1 class="text-2xl font-bold text-gray-900">Mes reservations</h1>
                            <p class="text-sm text-gray-500">Vous avez {{ reservations.length }} reservation(s)</p>
                        </div>
                        <div class="flex items  justify-end">
                            <Button>
                                Nouvelle reservation
                            </Button>
                        </div>
                    </div>
                    <DataTable :columns="columns" :data="reservations" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
