<script setup lang="ts">
import { ref, computed } from 'vue';
import { columns } from '@/components/Reservation/columns';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Reservation, type SharedData, type Stats, type Babysitter } from '@/types';
import { Head, usePage, Link } from '@inertiajs/vue3';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import DataTable from '@/components/Reservation/data-table.vue';
import { DollarSign, User, CreditCard, Activity } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

// Shared page props
const page = usePage<SharedData>();

// Raw reservations list
const reservations = computed<Reservation[]>(
    () => page.props.reservations.data as Reservation[]
);

// Raw stats
const statistique = computed<Stats>(
    () => page.props.stats as Stats
);

// Babysitters list from the backend
const babysitters = computed<Babysitter[]>(
    () => page.props.babysitters as Babysitter[]
);

// --- Nouveau : état pour la sélection ---
const selectedBabysitterId = ref<number | null>(null);

// Computed qui renvoie l'objet babysitter sélectionné (ou null)
const selectedBabysitter = computed(() =>
    babysitters.value.find(b => b.id === selectedBabysitterId.value) ?? null
);

// Define stats cards data
const stats = [
    {
        title: 'Total Revenue',
        value: '$' + statistique.value.total_revenue,
        change: '+20.1%',
        changeText: ' from last month',
        icon: DollarSign,
    },
    {
        title: 'Current month Revenue',
        value: '$' + statistique.value.current_month_revenue,
        change: statistique.value.revenue_change_pct ?? 0 + '%',
        changeText: ' from last month',
        icon: User,
    },
    {
        title: 'Current Month Reservations',
        value: statistique.value.current_month_count,
        change: statistique.value.count_change_pct ?? 0 + '%',
        changeText: ' from last month',
        icon: CreditCard,
    },
    {
        title: 'Canceled Reservations',
        value: statistique.value.total_canceled_count,
        change: '',
        changeText: 'au total',
        icon: Activity,
    },
];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mes reservations',
        href: '/reservations',
    },
];

</script>

<template>

    <Head title="Mes reservations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Stats cards container -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div v-for="(stat, index) in stats" :key="index"
                    class="bg-white border border-gray-200 rounded-sm p-4 shadow-xs">
                    <div class="flex justify-between items-center">
                        <h2 class="text-sm font-medium text-gray-500">{{ stat.title }}</h2>
                        <component :is="stat.icon" class="w-5 h-5 text-gray-400" />
                    </div>
                    <div class="mt-2">
                        <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
                        <p class="mt-1 text-sm">
                            <span class="text-green-500">{{ stat.change }}</span>
                            <span class="text-gray-400"> {{ stat.changeText }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="border border-gray-200 rounded-sm shadow-xs">
                <div class="mx-5 my-10">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-2">
                            <h1 class="text-2xl font-bold text-gray-900">Mes reservations</h1>
                            <p class="text-sm text-gray-500">
                                Vous avez {{ reservations.length }} reservation(s)
                            </p>
                        </div>
                        <div class="flex items-center justify-end">
                            <Dialog>
                                <DialogTrigger as-child>
                                    <Button>New reservation</Button>
                                </DialogTrigger>
                                <DialogContent class="sm:max-w-[425px]">
                                    <DialogHeader>
                                        <DialogTitle>Select babysitter</DialogTitle>
                                        <DialogDescription>
                                            Choisissez un(e) babysitter pour cette réservation
                                        </DialogDescription>
                                    </DialogHeader>

                                    <!-- Select babysitter -->
                                    <div class="grid gap-4 py-4">
                                        <label for="babysitter-select" class="block text-sm font-medium text-gray-700">
                                            Babysitter
                                        </label>
                                        <Select id="babysitter-select" v-model.number="selectedBabysitterId">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Sélectionnez un(e) babysitter" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectLabel>Sélectionnez un(e) babysitter</SelectLabel>
                                                    <SelectItem v-for="b in babysitters" :key="b.id" :value="b.id">
                                                        {{ b.name }}
                                                    </SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                        <!-- Afficher les détails du babysitter sélectionné -->
                                        <div v-if="selectedBabysitter" class="mt-4 flex items-center gap-4">
                                            <img :src="selectedBabysitter.profile_picture" alt="Photo de profil"
                                                class="w-42 h-42 rounded-xs" />
                                            <div>
                                                <p class="font-medium text-gray-900">{{ selectedBabysitter.name }}</p>
                                                <p class="text-sm text-gray-500">{{ selectedBabysitter.email }}</p>
                                                <p class="text-lg semi-bold mt-4 text-indigo-500">
                                                    $ {{ selectedBabysitter.price_per_hour }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <DialogFooter>
                                        <Link v-if="selectedBabysitter"
                                            :href="route('reservations.create', { id: selectedBabysitter.id })"
                                            class="block w-full">
                                        <Button class="w-full">
                                            Book me
                                        </Button>
                                        </Link>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </div>

                    <DataTable :columns="columns" :data="reservations" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
