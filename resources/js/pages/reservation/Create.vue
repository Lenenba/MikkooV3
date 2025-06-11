<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Star, Trash } from 'lucide-vue-next';
const page = usePage();
// Shared page props
const Babysitter = computed(
    () => page.props.babysitter
);
const Numero = computed(
    () => page.props.numero
);
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'New reservation',
        href: route('reservations.create', { id: Babysitter.value.id }),
    },
];


const form = useForm({
    babysitter_id: Babysitter.value.id,
    service: [
        { id: null, name: '', quantity: 1, price: 0, total: 0 },
    ],
    start_date: '',
    end_date: '',
    note: '',
    status: 'draft',
});

const subTotal = computed(() =>
    form.service.reduce(
        (sum, item) => sum + Number(item.quantity) * Number(item.price),
        0,
    ),
);
const tps = computed(() => subTotal.value * 0.05);
const tvq = computed(() => subTotal.value * 0.09975);
const totalTaxes = computed(() => tps.value + tvq.value);
const totalAmount = computed(() => subTotal.value + totalTaxes.value);

// Ajouter une nouvelle ligne de produit
const addNewLine = () => {
    form.service.push({ id: null, name: '', quantity: 1, price: 0, total: 0 });
};

// Supprimer une ligne de produit
const removeLine = index => {
    if (form.service.length > 1) {
        form.service.splice(index, 1);
    }
};
</script>

<template>

    <Head title="Create new reservation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Container centered, 100% wide on mobile and 75% on large screens -->
        <div class="mx-auto w-full lg:w-3/4">
            <div
                class="p-5 space-y-4 flex flex-col lg:flex-row bg-gray-100 border border-gray-100 rounded-sm shadow-sm xl:shadow-none dark:bg-green-800 dark:border-green-700">
                <!-- Left: profile image -->
                <div class="lg:w-1/4 mb-4 lg:mb-0">
                    <img :src="Babysitter.media.find(p => p.is_profile_picture)?.file_path" alt="Profile picture"
                        class="w-full" />
                </div>

                <!-- Right: content -->
                <div class="flex-1 space-y-6 ml-4">
                    <!-- Header -->
                    <div class="flex justify-between items-center">
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-green-100">
                            Reservation For
                            {{ Babysitter.babysitter_profile.first_name }}
                            {{ Babysitter.babysitter_profile.last_name }}
                        </h1>
                    </div>

                    <!-- Main grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left side (2/3) -->
                        <div class="col-span-2 space-y-4">
                            <!-- Job title input -->
                            <input type="text" placeholder="Job title"
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500" />

                            <!-- Address & contact -->
                            <div class="flex flex-col lg:flex-row lg:space-x-6">
                                <!-- Property address -->
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-700">Property address</p>
                                    <p class="text-xs text-gray-600">
                                        {{ Babysitter.address.street }} {{ Babysitter.address.province }}
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        {{ Babysitter.address.city }} {{ Babysitter.address.postal_code }}
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        {{ Babysitter.address.country }}
                                    </p>
                                </div>
                                <!-- Contact details -->
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-700">Contact details</p>
                                    <p class="text-xs text-gray-600">{{ Babysitter.email }}</p>
                                    <p class="text-xs text-gray-600">
                                        {{ Babysitter.babysitter_profile.birthdate }}
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        {{ Babysitter.babysitter_profile.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right side (1/3) -->
                        <div class="bg-white p-4 rounded">
                            <p class="font-semibold text-gray-700 mb-2">Reservation details</p>
                            <div class="text-xs text-gray-600 flex justify-between">
                                <span>Numero :</span>
                                <span>{{ Numero }}</span>
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
                            <button type="button" disabled
                                class="mt-5 w-full py-2 px-3 text-sm font-medium rounded border border-green-200 bg-white text-green-800 shadow-sm hover:bg-green-50 disabled:opacity-50">
                                Add custom fields
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <form @submit.prevent="form.post(route('reservations.store'))">
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
                                            class="pe-4 py-3 text-start flex items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Services
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start flex items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Nbre d'enfant (s)
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start flex items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Unit cost
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start flex items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Total
                                        </div>
                                    </th>
                                    <th scope="col" class="size-px">
                                        <div
                                            class="px-4 py-3 text-start flex items-center gap-x-1 text-sm font-medium text-gray-800 dark:text-neutral-200">
                                            Actions
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <tr v-for="(item, index) in form.service" :key="index">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <input
                                            class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500"
                                            v-model="item.name"
                                            type="text"
                                        />
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <input
                                            type="number"
                                            class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500"
                                            v-model.number="item.quantity"
                                            min="1"
                                        />
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <input
                                            type="number"
                                            class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500"
                                            v-model.number="item.price"
                                            min="0"
                                            step="0.01"
                                        />
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        {{ Number(item.quantity) * Number(item.price) }}
                                    </td>
                                    <td>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            class="px-4 py-4 inline-flex items-center gap-x-2 text-sm font-medium text-red-800 hover:text-red-600 disabled:opacity-50 disabled:pointer-events-none focus:outline-none dark:text-red-300"
                                            @click="removeLine(index)"
                                        >
                                            <Trash class="h-4 w-4" />
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End Table -->
                    </div>
                </div>
                <!-- End Table Section -->
                <div class="text-xs text-gray-600 flex justify-between mt-5">
                    <Button type="button" @click="addNewLine">
                        Add new service line
                    </Button>
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
                            <p class="text-sm text-gray-800 dark:text-neutral-200">
                                {{ subTotal }}
                            </p>
                        </div>
                    </div>
                    <!-- End List Item -->

                    <!-- List Item -->
                    <div class="py-4 grid grid-cols-2 gap-x-4 border-t border-gray-200 dark:border-neutral-700">
                        <div class="col-span-1">
                            <p class="text-sm text-gray-500 dark:text-neutral-500">
                                Discount (%):
                            </p>
                        </div>
                        <div class="flex justify-end">
                            <p class="text-sm text-gray-800 dark:text-neutral-200">
                                Add discount
                            </p>
                        </div>
                    </div>
                    <!-- End List Item -->

                    <!-- List Item -->
                    <div class="py-4 grid grid-cols-2 gap-x-4 border-t border-gray-200 dark:border-neutral-700">
                        <!-- Label pour la ligne des taxes -->
                        <div class="col-span-1">
                            <p class="text-sm text-gray-500 dark:text-neutral-500">
                                Tax:
                            </p>
                        </div>
                        <div class="flex justify-end">
                            <div class="flex items-center gap-x-2">
                                <Button variant="outline" size="icon"
                                    class="py-1.5 ps-1.5 pe-2.5 inline-flex items-center gap-x-1 text-xs font-medium border border-green-500 text-green-800 rounded-sm dark:bg-green-500/10 dark:text-green-500">

                                </Button>
                            </div>
                        </div>
                    </div>
                    <!-- Section des détails des taxes (affichée ou masquée) -->
                    <div class="space-y-2 py-4 border-t border-gray-200 dark:border-neutral-700">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-500 dark:text-neutral-500">TPS (5%) :</p>
                            <p class="text-sm text-gray-800 dark:text-neutral-200">{{ tps.toFixed(2) }}</p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-500 dark:text-neutral-500">TVQ (9.975%) :</p>
                            <p class="text-sm text-gray-800 dark:text-neutral-200">{{ tvq.toFixed(2) }}</p>
                        </div>
                        <div class="flex justify-between font-bold">
                            <p class="text-sm text-gray-800 dark:text-neutral-200">Total taxes :</p>
                            <p class="text-sm text-gray-800 dark:text-neutral-200">{{ totalTaxes.toFixed(2) }}</p>
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
                                {{ totalAmount.toFixed(2) }}
                            </p>
                        </div>
                    </div>


                    <!-- End List Item -->

                    <!-- List Item -->
                    <div
                        class="py-4 grid grid-cols-2 items-center gap-x-4 border-t border-gray-600 dark:border-neutral-700">
                        <!-- Label -->
                        <div class="col-span-1">
                            <p class="text-sm text-gray-500 dark:text-neutral-500">Required deposit:</p>
                        </div>

                    </div>
                    <!-- End List Item -->
                </div>
            </div>
            <div
                class="p-5 grid grid-cols-1 gap-4 justify-between bg-white border border-gray-100 rounded-sm shadow-sm xl:shadow-none dark:bg-green-800 dark:border-green-700">


                <div class="flex justify-between">
                    <Button variant="outline">
                        Cancel
                    </Button>
                    <div class="flex gap-x-2">
                        <Button>
                            Save and create another
                        </Button>
                        <Button type="submit">
                            Save quote
                        </Button>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </AppLayout>
</template>
