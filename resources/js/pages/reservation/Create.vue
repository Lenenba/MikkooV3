<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { computed, watch, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Star, Trash } from 'lucide-vue-next';

import { Label } from '@/components/ui/label'
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/components/ui/number-field'

import axios from 'axios';

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
        href: '/reservations/create',
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
    subtotal: 0,
    discount: 0,
    tax: 0,
    total: 0,
    deposit: 0,
    payment_method: '',
});

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
// Gestion de la recherche de produits
const searchResults = ref([]);
const searchServices = async (query, index) => {
    if (query.length > 0) {
        try {
            const response = await axios.get(route('service.search'), { params: { query } });
            searchResults.value[index] = response.data;
        } catch (error) {
            console.error('Error fetching Services:', error);
        }
    } else {
        searchResults.value[index] = [];
    }
};

// Sélectionner un produit
const selectService = (service: any, index: number) => {
    form.service[index] = {
        id: service.id,
        name: service.name,
        quantity: 1,
        price: service.price,
        total: service.price,
    };
    searchResults.value[index] = [];
};

// Watch pour recalculer les totaux
watch(
    () => form.service,
    (newServices: Array<{ id: number | null; name: string; quantity: number; price: number; total: number }>) => {
        // Mise à jour des totaux par produit
        newServices.forEach(service => {
            service.total = service.quantity * service.price;
        });

        // Calcul du sous-total
        form.subtotal = newServices.reduce((acc, service) => acc + service.total, 0);
    },
    { deep: true }
);
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
                            <Input type="date" placeholder="Job title"
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
                                            Unit price
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
                                <!-- Ligne de service -->
                                <tr v-for="(service, index) in form.service" :key="index">
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        <span class="text-sm text-gray-600 dark:text-neutral-400">
                                            <div class="relative">
                                                <Input autofocus v-model="form.service[index].name" label="Name"
                                                    @input="searchServices(form.service[index].name, index)" />
                                            </div>
                                            <div class="relative w-full">
                                                <ul v-if="searchResults[index]?.length"
                                                    class="absolute left-0 top-full z-50 w-full max-h-60 overflow-y-auto bg-white border border-gray-200 rounded-md shadow-lg dark:bg-neutral-800 dark:border-neutral-700">
                                                    <li v-for="result in searchResults[index]" :key="result.id"
                                                        @click="selectService(result, index)"
                                                        class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                                        {{ result.name }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </span>
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        <NumberField id="quantity" :min="1" :max="10"
                                            v-model="form.service[index].quantity">
                                            <NumberFieldContent>
                                                <NumberFieldDecrement />
                                                <NumberFieldInput />
                                                <NumberFieldIncrement />
                                            </NumberFieldContent>
                                        </NumberField>
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        <NumberField id="Unit Price" :min="1" :max="10"
                                            v-model="form.service[index].price">
                                            <NumberFieldContent>
                                                <NumberFieldDecrement />
                                                <NumberFieldInput />
                                                <NumberFieldIncrement />
                                            </NumberFieldContent>
                                        </NumberField>
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        <NumberField id="Total" :min="1" :max="10" v-model="form.service[index].total">
                                            <NumberFieldContent>
                                                <NumberFieldDecrement />
                                                <NumberFieldInput />
                                                <NumberFieldIncrement />
                                            </NumberFieldContent>
                                        </NumberField>
                                    </td>
                                    <td>
                                        <Button variant="outline" size="icon" v-if="form.service.length > 1"
                                            @click="removeLine(index)"
                                            class="px-4 py-4 inline-flex items-center gap-x-2 text-sm font-medium text-red-800  hover:text-red-600 disabled:opacity-50 disabled:pointer-events-none focus:outline-none   dark:text-red-300 ">
                                            <Trash class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End Table -->
                    </div>
                </div>
                <!-- End Table Section -->
                <div class="text-xs text-gray-600 flex justify-between mt-5">
                    <Button @click="addNewLine">
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
                            <p class="text-sm text-green-600 decoration-2 hover:underline font-medium focus:outline-none focus:underline dark:text-green-400 dark:hover:text-green-500"
                                href="#">
                                $ {{ form.subtotal }}
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
                            <p class="text-sm text-gray-800 dark:text-neutral-200"> </p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-500 dark:text-neutral-500">TVQ (9.975%) :</p>
                            <p class="text-sm text-gray-800 dark:text-neutral-200"> </p>
                        </div>
                        <div class="flex justify-between font-bold">
                            <p class="text-sm text-gray-800 dark:text-neutral-200">Total taxes :</p>
                            <p class="text-sm text-gray-800 dark:text-neutral-200"> </p>
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
        </div>
    </AppLayout>
</template>
