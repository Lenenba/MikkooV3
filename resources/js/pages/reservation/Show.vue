<script setup lang="ts">
import { ref, computed } from 'vue';
import { Label } from '@/components/ui/label'
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type SharedData, type BreadcrumbItem } from '@/types';
import { Star } from 'lucide-vue-next';
import { ca } from 'date-fns/locale';

const page = usePage<SharedData>();
const reservation = computed<any>(
    () => page.props.reservation
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mes Reservations',
        href: '/reservations',
    },
    {
        title: 'Ma reservation',
        href: '/reservation/' + reservation.value.id + '/show',
    },
];

const caculSubtotal = computed(() => {
    return reservation.value.services.reduce((total: number, service: any) => {
        return parseFloat((total + (service.price * service.pivot.quantity)).toFixed(2));
    }, 0);
});

const calculTax = computed(() => {
    // Calculate taxes, total, and deposit
    const TAX_RATE = 0.14975;
    return parseFloat((caculSubtotal.value * TAX_RATE).toFixed(2));
});
</script>

<template>

    <Head title="reservation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full lg:w-3/4">
            <div
                class="p-5 space-y-4 flex flex-col lg:flex-row bg-gray-100 border border-gray-100 rounded-sm shadow-sm xl:shadow-none dark:bg-green-800 dark:border-green-700">
                <!-- Left: profile image -->
                <div class="lg:w-1/4 mb-4 lg:mb-0">
                    <img :src="reservation.babysitter.media.find(p => p.is_profile_picture)?.file_path"
                        alt="Profile picture" class="w-full" />
                </div>

                <!-- Right: content -->
                <div class="flex-1 space-y-6 ml-4">
                    <!-- Header -->
                    <div class="flex justify-between items-center">
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-green-100">
                            Reservation For
                            {{ reservation.babysitter.babysitter_profile.first_name }}
                            {{ reservation.babysitter.babysitter_profile.last_name }}
                        </h1>
                    </div>

                    <!-- Main grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">
                        <!-- Left side (2/3) -->
                        <div class="col-span-3 space-y-4">
                            <div class="">
                                <!-- Notes -->
                                <div class="mb-8">
                                    <Label for="note" class="font-semibold text-gray-700 mb-2">Notes pour la
                                        reservation :</Label>
                                    <p>{{ reservation.notes }}</p>
                                </div>
                                <!-- Address & contact -->
                                <div class="flex flex-col lg:flex-row lg:space-x-6">
                                    <!-- Property address -->
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-700">Property address</p>
                                        <p class="text-xs text-gray-600">
                                            {{ reservation.babysitter.address.street }} {{
                                                reservation.babysitter.address.province }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            {{ reservation.babysitter.address.city }} {{
                                                reservation.babysitter.address.postal_code }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            {{ reservation.babysitter.address.country }}
                                        </p>
                                    </div>
                                    <!-- Contact details -->
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-700">Contact details</p>
                                        <p class="text-xs text-gray-600">{{ reservation.babysitter.email }}</p>
                                        <p class="text-xs text-gray-600">
                                            {{ reservation.babysitter.babysitter_profile.birthdate }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            {{ reservation.babysitter.babysitter_profile.phone }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right side (1/3) -->
                        <div class="bg-white p-4 rounded col-span-2">
                            <p class="font-semibold text-gray-700 mb-2">Reservation details</p>
                            <div class="text-xs text-gray-600 flex justify-between">
                                <span>Numero :</span>
                                <span>{{ reservation.number }}</span>
                            </div>

                            <div class="my-2 flex flex-row justify-between">
                                <p class="text-xs text-gray-600 mb-1">Reservation date</p>
                                <p class="text-xs text-gray-600 mb-1">{{ reservation.details.date }}</p>
                            </div>
                            <div class="my-2 flex flex-row justify-between">
                                <p class="text-xs text-gray-600 mb-1">Reservation heure de debut</p>
                                <p class="text-xs text-gray-600 mb-1">{{ reservation.details.start_time }}</p>
                            </div>
                            <div class="my-2 flex flex-row justify-between">
                                <p class="text-xs text-gray-600 mb-1">Reservation heure de fin</p>
                                <p class="text-xs text-gray-600 mb-1">{{ reservation.details.end_time }}</p>
                            </div>
                            <div class="text-xs text-gray-600 flex justify-between mt-2">
                                <span>Rate :</span>
                                <span class="flex space-x-1">
                                    <Star class="h-4 w-4 text-yellow-400" />
                                    <Star class="h-4 w-4 text-yellow-400" />
                                    <Star class="h-4 w-4 text-yellow-400" />
                                    <Star class="h-4 w-4 text-yellow-400" />
                                    <Star class="h-4 w-4 text-yellow-400" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="p-5 space-y-3 flex flex-col bg-white border border-gray-100 rounded-sm shadow-sm xl:shadow-none dark:bg-green-800 dark:border-green-700">
                <!-- Table Section -->
                <div
                    class="overflow-x-auto [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                    <div class="min-w-full inline-block align-middle min-h-[300px]">
                        <!-- Table -->
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead>
                                <tr>
                                    <th scope="col" class="min-w-[430px] ">
                                        <div
                                            class="px-4 py-3 text-start flex items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Services
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Nbre d'enfant (s)
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Unit price
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Total
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <!-- Ligne de service -->
                                <tr v-for="service in reservation.services" :key="service.id">
                                    <td class="size-px whitespace-nowrap px-4 py-3 ">
                                        {{ service.name }}
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        {{ service.pivot.quantity }}
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        {{ service.price }}
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        {{ service.price * service.pivot.quantity }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End Table -->
                    </div>
                </div>
            </div>
            <div
                class="p-5 grid grid-cols-2 gap-4 justify-between bg-white border border-gray-100 rounded-sm shadow-sm xl:shadow-none dark:bg-green-800 dark:border-green-700">

                <div>

                </div>
                <div class="border-l border-gray-200 dark:border-neutral-700 rounded-sm p-4">
                    <!-- List Item -->
                    <div class="py-4 grid grid-cols-2 gap-x-4  dark:border-neutral-700">
                        <div class="col-span-1">
                            <p class="text-sm text-gray-500 dark:text-neutral-500">
                                Subtotal:
                            </p>
                        </div>
                        <div class="col-span-1 flex justify-end">
                            <p class="text-sm text-green-600 decoration-2 hover:underline font-medium focus:outline-none focus:underline dark:text-green-400 dark:hover:text-green-500"
                                href="#">
                                $ {{ caculSubtotal }}
                            </p>
                        </div>
                    </div>
                    <!-- Section des détails des taxes (affichée ou masquée) -->
                    <div class="space-y-2 py-4 border-t border-gray-200 dark:border-neutral-700">
                        <div class="flex justify-between font-bold">
                            <p class="text-sm text-gray-800 dark:text-neutral-200">Total taxes :</p>
                            <p class="text-sm text-gray-800 dark:text-neutral-200"> $ {{ calculTax }}</p>
                        </div>
                    </div>
                    <!-- End List Item -->

                    <!-- List Item -->
                    <div class="py-4 grid grid-cols-2 gap-x-4 border-t border-gray-200 dark:border-neutral-700">
                        <div class="col-span-1">
                            <p class="text-sm text-gray-800 font-bold dark:text-neutral-500">
                                Total amount:
                            </p>
                        </div>
                        <div class="flex justify-end">
                            <p class="text-sm text-gray-800 font-bold dark:text-neutral-200">
                                $ {{ reservation.total_amount }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
