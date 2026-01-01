<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';
import FloatingInput from '@/components/FloatingInput.vue'
import FloatingTextarea from '@/components/FloatingTextarea.vue'
import FloatingSelect from '@/components/FloatingSelect.vue'
import { computed, watch, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Star, Trash } from 'lucide-vue-next';
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
    () => page.props.numero as string | number
);
const defaultProfilePhoto = '/bbsiter.png';
const babysitterPhoto = computed(() => {
    const media = Babysitter.value?.media ?? [];
    return (
        media.find((item) => item.is_profile_picture)?.file_path ??
        media[0]?.file_path ??
        defaultProfilePhoto
    );
});
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mes Reservations',
        href: '/reservations',
    },
    {
        title: 'New reservation',
        href: '/reservations/create',
    },
];


const form = useForm({
    babysitter_id: Babysitter.value.id,
    number: Numero.value,
    services: [
        { id: null, name: '', quantity: 1, price: 0, total: 0 },
    ],
    schedule_type: 'single',
    start_date: '',
    start_time: '',
    end_time: '',
    recurrence_frequency: 'weekly',
    recurrence_interval: 1,
    recurrence_days: [] as number[],
    recurrence_end_date: '',
    notes: '',
    status: 'draft',
    subtotal: 0,
    discount: 0,
    tax: 0,
    total_amount: 0,
    deposit: 0,
    payment_method: '',
});

const recurrenceOptions = [
    { value: 'daily', label: 'Chaque jour' },
    { value: 'weekly', label: 'Chaque semaine' },
    { value: 'monthly', label: 'Chaque mois' },
];

const weekdayOptions = [
    { value: 1, label: 'Lun' },
    { value: 2, label: 'Mar' },
    { value: 3, label: 'Mer' },
    { value: 4, label: 'Jeu' },
    { value: 5, label: 'Ven' },
    { value: 6, label: 'Sam' },
    { value: 7, label: 'Dim' },
];

const getIsoWeekday = (value: string) => {
    if (!value) return null;
    const parsed = new Date(`${value}T00:00:00`);
    if (Number.isNaN(parsed.getTime())) return null;
    const day = parsed.getDay();
    return day === 0 ? 7 : day;
};

watch(
    () => [form.schedule_type, form.recurrence_frequency, form.start_date],
    () => {
        if (form.schedule_type !== 'recurring') {
            return;
        }
        if (!form.recurrence_interval || Number(form.recurrence_interval) < 1) {
            form.recurrence_interval = 1;
        }
        if (form.recurrence_frequency === 'weekly' && form.recurrence_days.length === 0) {
            const defaultDay = getIsoWeekday(form.start_date);
            if (defaultDay) {
                form.recurrence_days = [defaultDay];
            }
        }
    }
);

// Ajouter une nouvelle ligne de produit
const addNewLine = () => {
    form.services.push({ id: null, name: '', quantity: 1, price: 0, total: 0 });
};

// Supprimer une ligne de produit
const removeLine = index => {
    if (form.services.length > 1) {
        form.services.splice(index, 1);
    }
};
// Gestion de la recherche de produits
const searchResults = ref([]);
const searchServices = async (query, index) => {
    if (query.length > 0) {
        try {
            const response = await axios.get(route('service.search'), {
                params: { query, babysitter_id: form.babysitter_id },
            });
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
    form.services[index] = {
        id: service.id,
        name: service.name,
        quantity: 1,
        price: Number(service.price),
        total: Number(service.price),
    };
    searchResults.value[index] = [];
};

// Watch pour recalculer les totaux
watch(
    () => form.services,
    (newServices: Array<{ id: number | null; name: string; quantity: number; price: number; total: number }>) => {
        // Mise à jour des totaux par produit
        newServices.forEach(service => {
            service.total = service.quantity * service.price;
        });

        // Calcul du sous-total
        form.subtotal = newServices.reduce((acc, service) => acc + service.total, 0);

        // Calculate taxes, total, and deposit
        const TAX_RATE = 0.14975;   // TPS + TVQ in Quebec (14.975%)
        const DEPOSIT_RATE = 0.25;  // 25% deposit

        form.tax = parseFloat((form.subtotal * TAX_RATE).toFixed(2));
        form.total_amount = parseFloat((form.subtotal + form.tax - form.discount).toFixed(2));
        form.deposit = parseFloat((form.total_amount * DEPOSIT_RATE).toFixed(2));

    },
    { deep: true }
);

const createReservation = () => {
    form.transform((data) => {
        if (data.schedule_type !== 'recurring') {
            return {
                ...data,
                recurrence_frequency: null,
                recurrence_interval: null,
                recurrence_days: [],
                recurrence_end_date: null,
            };
        }
        return data;
    }).post(route('reservations.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset the form after successful submission
            form.reset();
            form.services = [{ id: null, name: '', quantity: 1, price: 0, total: 0 }];
        },
        onError: (errors) => {
            console.error('Error creating reservation:', errors);
        },
    });
};
</script>

<template>

    <Head title="Create new reservation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form @submit.prevent="createReservation" class="space-y-6">
            <div class="mx-auto w-full lg:w-4/5 space-y-6">
                <div class="grid gap-6 lg:grid-cols-[1.25fr_0.75fr]">
                    <div class="space-y-6">
                        <div class="rounded-sm border border-border bg-card p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                            <div class="flex flex-col gap-4 sm:flex-row">
                                <div class="w-full sm:w-40">
                                    <div class="aspect-[4/5] w-full overflow-hidden rounded-sm bg-muted">
                                        <img :src="babysitterPhoto"
                                            alt="Profile picture" class="h-full w-full object-cover" />
                                    </div>
                                </div>
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                            Babysitter
                                        </p>
                                        <h1 class="text-lg font-semibold text-foreground dark:text-neutral-100">
                                            Reservation pour
                                            {{ Babysitter.babysitter_profile.first_name }}
                                            {{ Babysitter.babysitter_profile.last_name }}
                                        </h1>
                                    </div>
                                    <div class="grid gap-4 text-xs text-muted-foreground dark:text-neutral-400 sm:grid-cols-2">
                                        <div>
                                            <p class="font-semibold text-foreground dark:text-neutral-200">Adresse</p>
                                            <p>
                                                {{ Babysitter.address.street }} {{ Babysitter.address.province }}
                                            </p>
                                            <p>
                                                {{ Babysitter.address.city }} {{ Babysitter.address.postal_code }}
                                            </p>
                                            <p>
                                                {{ Babysitter.address.country }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-foreground dark:text-neutral-200">Contact</p>
                                            <p>{{ Babysitter.email }}</p>
                                            <p>{{ Babysitter.babysitter_profile.birthdate }}</p>
                                            <p>{{ Babysitter.babysitter_profile.phone }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-sm border border-border bg-card p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                            <p class="text-sm font-semibold text-foreground dark:text-neutral-100">Notes</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Ajoute les details importants pour la reservation.
                            </p>
                            <div class="mt-3">
                                <FloatingTextarea
                                    id="note"
                                    label="Notes pour la reservation"
                                    rows="4"
                                    v-model="form.notes"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-sm border border-border bg-card p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                            <p class="text-sm font-semibold text-foreground dark:text-neutral-100">Details reservation</p>
                            <div class="mt-3 space-y-2 text-xs text-muted-foreground dark:text-neutral-400">
                                <div class="flex items-center justify-between">
                                    <span>Numero</span>
                                    <span class="font-medium text-foreground dark:text-neutral-100">{{ Numero }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Note</span>
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

                        <div class="rounded-sm border border-border bg-card p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                            <p class="text-sm font-semibold text-foreground dark:text-neutral-100">Date et heure</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Choisis une journee unique ou une recurrence.
                            </p>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <p class="text-xs font-medium text-muted-foreground mb-2">Type de tache</p>
                                    <div class="inline-flex rounded-sm border border-border bg-muted/60 p-1">
                                        <button
                                            type="button"
                                            class="px-3 py-1 text-xs font-medium rounded-sm transition"
                                            :class="form.schedule_type === 'single'
                                                ? 'bg-card text-foreground shadow-sm'
                                                : 'text-muted-foreground hover:text-foreground'"
                                            @click="form.schedule_type = 'single'"
                                        >
                                            Journee unique
                                        </button>
                                        <button
                                            type="button"
                                            class="px-3 py-1 text-xs font-medium rounded-sm transition"
                                            :class="form.schedule_type === 'recurring'
                                                ? 'bg-card text-foreground shadow-sm'
                                                : 'text-muted-foreground hover:text-foreground'"
                                            @click="form.schedule_type = 'recurring'"
                                        >
                                            Recurrence
                                        </button>
                                    </div>
                                </div>
                                <FloatingInput
                                    id="reservation-date"
                                    :label="form.schedule_type === 'recurring' ? 'Date de debut' : 'Date'"
                                    type="date"
                                    name="reservation-date"
                                    v-model="form.start_date"
                                />
                                <div class="grid grid-cols-2 gap-4">
                                    <FloatingInput
                                        id="reservation-start-time"
                                        label="Heure debut"
                                        type="time"
                                        name="reservation-start-time"
                                        v-model="form.start_time"
                                    />
                                    <FloatingInput
                                        id="reservation-end-time"
                                        label="Heure fin"
                                        type="time"
                                        name="reservation-end-time"
                                        v-model="form.end_time"
                                    />
                                </div>
                                <div v-if="form.schedule_type === 'recurring'" class="space-y-3">
                                    <FloatingSelect
                                        id="recurrence-frequency"
                                        label="Frequence"
                                        :options="recurrenceOptions"
                                        :required="form.schedule_type === 'recurring'"
                                        v-model="form.recurrence_frequency"
                                    />
                                    <FloatingInput
                                        id="recurrence-interval"
                                        label="Intervalle (ex: 1 = chaque)"
                                        type="number"
                                        min="1"
                                        v-model="form.recurrence_interval"
                                    />
                                    <div v-if="form.recurrence_frequency === 'weekly'" class="space-y-2">
                                        <p class="text-xs font-medium text-muted-foreground">Jours</p>
                                        <div class="grid grid-cols-7 gap-2 text-xs text-muted-foreground">
                                            <label v-for="day in weekdayOptions" :key="day.value"
                                                class="flex items-center gap-1">
                                                <input
                                                    type="checkbox"
                                                    class="h-3 w-3 rounded border-input accent-primary focus:ring-ring/30"
                                                    :value="day.value"
                                                    v-model="form.recurrence_days"
                                                />
                                                <span>{{ day.label }}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <FloatingInput
                                        id="recurrence-end-date"
                                        label="Fin de recurrence"
                                        type="date"
                                        :required="form.schedule_type === 'recurring'"
                                        v-model="form.recurrence_end_date"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-sm border border-border bg-card p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-foreground dark:text-neutral-100">Services</p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Ajoute les services et la quantite pour la reservation.
                            </p>
                        </div>
                        <Button variant="outline" size="sm" @click="addNewLine">
                            Ajouter un service
                        </Button>
                    </div>

                    <div
                        class="mt-4 overflow-x-auto [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-muted [&::-webkit-scrollbar-thumb]:bg-muted-foreground/30 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                        <div class="min-w-full inline-block align-middle min-h-[300px]">
                            <!-- Table -->
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead>
                                    <tr>
                                        <th scope="col" class="min-w-[430px] ">
                                            <div
                                                class="pe-4 py-3 text-start flex items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                                Services
                                            </div>
                                        </th>

                                        <th scope="col">
                                            <div
                                                class="px-4 py-3 items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                                Nbre d'enfant (s)
                                            </div>
                                        </th>

                                        <th scope="col">
                                            <div
                                                class="px-4 py-3 items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                                Unit price
                                            </div>
                                        </th>

                                        <th scope="col">
                                            <div
                                                class="px-4 py-3 items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                                Total
                                            </div>
                                        </th>
                                        <th scope="col" class="size-px whitespace-nowrap text-right">
                                            <div
                                                class="px-4 py-3 items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                                Actions
                                            </div>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                    <!-- Ligne de service -->
                                    <tr v-for="(service, index) in form.services" :key="index">
                                        <td class="size-px whitespace-nowrap px-4 py-3 text-left">
                                            <span class="text-sm text-muted-foreground dark:text-neutral-400">
                                                <div class="relative">
                                                    <FloatingInput
                                                        :id="`service-name-${index}`"
                                                        label="Service"
                                                        autofocus
                                                        autocomplete="off"
                                                        v-model="form.services[index].name"
                                                        @input="searchServices(form.services[index].name, index)"
                                                    />
                                                </div>
                                                <div class="relative w-full">
                                                    <ul v-if="searchResults[index]?.length"
                                                        class="absolute left-0 top-full z-50 w-full max-h-60 overflow-y-auto bg-card border border-border rounded-md shadow-lg dark:bg-neutral-800 dark:border-neutral-700">
                                                        <li v-for="result in searchResults[index]" :key="result.id"
                                                            @click="selectService(result, index)"
                                                            class="px-3 py-2 cursor-pointer hover:bg-muted dark:hover:bg-neutral-700 text-foreground dark:text-neutral-200">
                                                            {{ result.name }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </span>
                                        </td>
                                        <td class="size-px whitespace-nowrap px-4 py-3">
                                            <NumberField id="quantity" :min="1" :max="10"
                                                v-model="form.services[index].quantity">
                                                <NumberFieldContent>
                                                    <NumberFieldDecrement />
                                                    <NumberFieldInput />
                                                    <NumberFieldIncrement />
                                                </NumberFieldContent>
                                            </NumberField>
                                        </td>
                                        <td class="size-px whitespace-nowrap px-4 py-3">
                                            <NumberField id="Unit Price" :min="1" :max="10"
                                                v-model="form.services[index].price">
                                                <NumberFieldContent>
                                                    <!-- <NumberFieldDecrement /> -->
                                                    <NumberFieldInput />
                                                    <!-- <NumberFieldIncrement /> -->
                                                </NumberFieldContent>
                                            </NumberField>
                                        </td>
                                        <td class="size-px whitespace-nowrap px-4 py-3">
                                            <NumberField id="Total" :min="1" :max="10"
                                                v-model="form.services[index].total">
                                                <NumberFieldContent>
                                                    <!-- <NumberFieldDecrement /> -->
                                                    <NumberFieldInput />
                                                    <!-- <NumberFieldIncrement /> -->
                                                </NumberFieldContent>
                                            </NumberField>
                                        </td>
                                        <td class="size-px whitespace-nowrap px-4 py-3 text-right">
                                            <Button variant="outline" size="icon" v-if="form.services.length > 1"
                                                @click="removeLine(index)"
                                                class="px-4 py-4 inline-flex items-center gap-x-2 text-sm font-medium text-red-800  hover:text-red-600 disabled:opacity-50 disabled:pointer-events-none focus:outline-none dark:text-red-300 ">
                                                <Trash class="h-4 w-4" />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- End Table -->
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <div
                        class="w-full lg:max-w-md rounded-sm border border-border bg-card p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <p class="text-sm font-semibold text-foreground dark:text-neutral-100">Resume</p>
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between text-muted-foreground dark:text-neutral-400">
                                <span>Sous-total</span>
                                <span class="font-medium text-foreground dark:text-neutral-100">$ {{ form.subtotal }}</span>
                            </div>
                            <div class="flex justify-between text-muted-foreground dark:text-neutral-400">
                                <span>Taxes</span>
                                <span class="font-medium text-foreground dark:text-neutral-100">$ {{ form.tax }}</span>
                            </div>
                            <div
                                class="flex justify-between border-t border-border pt-3 font-semibold text-foreground dark:border-neutral-800 dark:text-neutral-100">
                                <span>Total</span>
                                <span>$ {{ form.total_amount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-sm border border-border bg-card p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Button variant="outline">
                            Cancel
                        </Button>
                        <div class="flex flex-wrap gap-2">
                            <Button variant="outline">
                                Save and create another
                            </Button>
                            <Button type="submit">
                                Save reservation
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
