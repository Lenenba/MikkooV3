<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, onBeforeUnmount } from 'vue';
import { type BreadcrumbItem, type Babysitter, type SharedData } from '@/types';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import BabysitterList from '@/components/BabysitterList.vue';
import FloatingInput from '@/components/FloatingInput.vue';
import FloatingSelect from '@/components/FloatingSelect.vue';
import { Button } from '@/components/ui/button';
import { Search } from 'lucide-vue-next';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Search Babysitter',
        href: '/search',
    },
];

const page = usePage<SharedData>();
const babysitters = computed<Babysitter[]>(() => page.props.babysitters?.data ?? []);

const filterForm = useForm({
    name: page.props.filters?.name ?? '',
    city: page.props.filters?.city ?? '',
    country: page.props.filters?.country ?? '',
    min_price: page.props.filters?.min_price ?? '',
    max_price: page.props.filters?.max_price ?? '',
    min_rating: page.props.filters?.min_rating ?? '',
    payment_frequency: page.props.filters?.payment_frequency ?? '',
    sort: page.props.filters?.sort ?? '',
});

let filterTimeout: ReturnType<typeof setTimeout> | null = null;
const autoFilter = (routeName: string) => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }
    filterTimeout = setTimeout(() => {
        filterForm.get(route(routeName), {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300); // Delay 300ms to avoid excessive calls.
};

const applyFilters = () => autoFilter('search.babysitter');

const resetFilters = () => {
    filterForm.name = '';
    filterForm.city = '';
    filterForm.country = '';
    filterForm.min_price = '';
    filterForm.max_price = '';
    filterForm.min_rating = '';
    filterForm.payment_frequency = '';
    filterForm.sort = '';
    applyFilters();
};

const sortOptions = [
    { value: 'default', label: 'Recommande' },
    { value: 'distance', label: 'Proximite' },
    { value: 'rating', label: 'Mieux notes' },
    { value: 'price_low', label: 'Prix: bas a haut' },
    { value: 'price_high', label: 'Prix: haut a bas' },
    { value: 'recent', label: 'Plus recentes' },
];

const ratingOptions = [
    { value: 'all', label: 'Toutes notes' },
    { value: '3', label: '3+ etoiles' },
    { value: '4', label: '4+ etoiles' },
    { value: '4.5', label: '4.5+ etoiles' },
];

const paymentOptions = [
    { value: 'all', label: 'Tous paiements' },
    { value: 'per_task', label: 'Par mission' },
    { value: 'daily', label: 'Par jour' },
    { value: 'weekly', label: 'Par semaine' },
    { value: 'biweekly', label: 'Toutes les 2 semaines' },
    { value: 'monthly', label: 'Par mois' },
];

onBeforeUnmount(() => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }
});

</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 my-8">
            <div class="grid gap-6 lg:grid-cols-[260px_1fr] xl:grid-cols-[280px_1fr]">
                <aside class="order-2 space-y-4 lg:order-1 lg:sticky lg:top-24">
                    <div
                        class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-neutral-100">Recherche</h3>
                        <div class="relative mt-3">
                            <FloatingInput
                                id="search"
                                label="Recherche"
                                label-class="pl-9"
                                type="text"
                                class="w-full pl-9"
                                v-model="filterForm.name"
                                @input="applyFilters"
                            />
                            <span class="absolute inset-y-0 left-0 flex items-center px-3">
                                <Search class="h-4 w-4 text-muted-foreground" />
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-neutral-400">
                            Nom, ville, ou pays.
                        </p>
                    </div>

                    <div
                        class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-neutral-100">Filtres</h3>
                            <Button variant="outline" size="sm" class="h-7 px-2 text-xs" @click="resetFilters">
                                Reset
                            </Button>
                        </div>
                        <div class="mt-4 space-y-4">
                            <div class="space-y-2">
                                <FloatingInput
                                    id="filter-city"
                                    label="Ville"
                                    type="text"
                                    v-model="filterForm.city"
                                    @input="applyFilters"
                                />
                            </div>
                            <div class="space-y-2">
                                <FloatingInput
                                    id="filter-country"
                                    label="Pays"
                                    type="text"
                                    v-model="filterForm.country"
                                    @input="applyFilters"
                                />
                            </div>
                            <div class="space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <FloatingInput
                                        id="filter-min-price"
                                        label="Prix min"
                                        type="number"
                                        min="0"
                                        step="1"
                                        v-model="filterForm.min_price"
                                        @input="applyFilters"
                                    />
                                    <FloatingInput
                                        id="filter-max-price"
                                        label="Prix max"
                                        type="number"
                                        min="0"
                                        step="1"
                                        v-model="filterForm.max_price"
                                        @input="applyFilters"
                                    />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <FloatingSelect
                                    id="filter-min-rating"
                                    label="Note minimale"
                                    :options="ratingOptions"
                                    :model-value="filterForm.min_rating ? `${filterForm.min_rating}` : 'all'"
                                    @update:model-value="value => { filterForm.min_rating = value === 'all' ? '' : value; applyFilters(); }"
                                />
                            </div>
                            <div class="space-y-2">
                                <FloatingSelect
                                    id="filter-payment"
                                    label="Paiement"
                                    :options="paymentOptions"
                                    :model-value="filterForm.payment_frequency ? `${filterForm.payment_frequency}` : 'all'"
                                    @update:model-value="value => { filterForm.payment_frequency = value === 'all' ? '' : value; applyFilters(); }"
                                />
                            </div>
                            <div class="space-y-2">
                                <FloatingSelect
                                    id="filter-sort"
                                    label="Trier par"
                                    :options="sortOptions"
                                    :model-value="filterForm.sort ? `${filterForm.sort}` : 'default'"
                                    @update:model-value="value => { filterForm.sort = value === 'default' ? '' : value; applyFilters(); }"
                                />
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="order-1 lg:order-2">
                    <BabysitterList :babysitters="babysitters" />
                </section>
            </div>
        </div>
    </AppLayout>
</template>

