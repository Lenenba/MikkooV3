<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, computed } from 'vue';
import { type BreadcrumbItem, type Babysitter, type SharedData, } from '@/types';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import BabysitterList from '@/components/BabysitterList.vue'
import { Input } from '@/components/ui/input'
import { Search } from 'lucide-vue-next'
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Search Babysitter',
        href: '/search',
    },
];

const page = usePage<SharedData>();
const babysitters = computed(() => page.props.babysitters.data as Babysitter);

const filterForm = useForm({
    name: page.props.filters.name ?? "",
});

let filterTimeout: NodeJS.Timeout | null = null;
const autoFilter = (routeName: string) => {
    if (filterTimeout) {
        clearTimeout(filterTimeout);
    }
    filterTimeout = setTimeout(() => {
        filterForm.get(route(routeName), {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300); // Délai de 300ms pour éviter les appels excessifs
};

// Réinitialiser le formulaire lorsque la recherche est vide
watch(() => filterForm.name, (newValue: string) => {
    if (!newValue) {
        filterForm.name = "";
        autoFilter('search.babysitter');
    }
});

</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 my-8">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 ">
                <div class="relative flex-1">
                    <Input id="search" type="text" placeholder="Search by name or address " class="w-full h-12 pl-10"
                        v-model="filterForm.name"
                        @input="filterForm.name.length >= 1 ? autoFilter('search.babysitter') : null" />
                    <span class="absolute inset-y-0 left-0 flex items-center px-3">
                        <Search class="size-6 text-muted-foreground" />
                    </span>
                </div>
            </div>
            <BabysitterList :babysitters="babysitters" />
        </div>
    </AppLayout>
</template>
