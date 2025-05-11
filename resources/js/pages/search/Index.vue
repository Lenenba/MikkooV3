<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { type BreadcrumbItem, type Babysitter, type SharedData, } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import BabysitterList from '@/components/BabysitterList.vue'
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Search Babysitter',
        href: '/search',
    },
];

const page = usePage<SharedData>();
const babysitters = page.props.babysitters.data as Babysitter[];

console.log(babysitters);
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 my-8">
            <BabysitterList :babysitters="babysitters" />
        </div>
    </AppLayout>
</template>
