<script setup lang="ts">
import { defineProps, withDefaults } from 'vue';
import { Link } from '@inertiajs/vue3';
import { type Babysitter } from '@/types';
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button';
import MediaScrollingHorizontalV2 from '@/components/MediaScrollingHorizontal_v2.vue';
import { SquareActivity } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog'
// Props avec une valeur par défaut pour babysitters
const props = withDefaults(defineProps<{ babysitters: Babysitter[] }>(), {
    babysitters: () => [],
});

</script>

<template>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 ">
        <div v-if="props.babysitters.length > 0"
            class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-y-10 gap-x-3 sm:gap-x-5">
            <div v-for="babysitter in props.babysitters" :key="babysitter.id"
                class="group relative flex flex-col h-full">

                <Dialog>
                    <DialogTrigger as-child>
                        <!-- Category Card -->
                        <div
                            class="flex flex-col items-center bg-gray-50 border border-gray-200 hover:border-gray-300 hover:shadow-2xs rounded-xl focus:outline-hidden focus:border-gray-300 focus:shadow-2xs dark:bg-neutral-900 dark:border-neutral-700 dark:hover:border-neutral-600 dark:focus:border-neutral-600">
                            <div class="p-2 w-full text-center">
                                <div class="relative shrink-0 h-72 sm:h-72 w-full">
                                    <img class="size-full absolute inset-0 object-cover object-center bg-gray-100 rounded-lg dark:bg-neutral-800"
                                        :src="babysitter.media.find(p => p.is_profile_picture)?.file_path"
                                        :alt="babysitter.name" />
                                </div>
                                <div class="pt-3">
                                    <h4 class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                                        {{ babysitter.babysitter_profile.first_name }} {{
                                            babysitter.babysitter_profile.last_name }}
                                    </h4>
                                    <div class="mt-1 flex flex-wrap items-center gap-1">
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-500">
                                            {{ babysitter.babysitter_profile.price_per_hour }}<span class="text-sm">
                                                USD</span>
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-neutral-500">
                                            <s>$399</s>
                                        </span>
                                        <span class="ms-auto text-sm text-gray-500 dark:text-neutral-500">
                                            <Badge class="bg-emerald-500 dark:bg-emerald-600">
                                                <SquareActivity class="h-4 w-4 animate-pulse" />
                                            </Badge>
                                        </span>
                                    </div>

                                    <!-- Review -->
                                    <div class="mt-2 flex items-center gap-x-0.5">
                                        <svg class="shrink-0 size-3 text-gray-800 dark:text-neutral-200"
                                            xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" viewBox="0 0 16 16">
                                            <path
                                                d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z">
                                            </path>
                                        </svg>
                                        <svg class="shrink-0 size-3 text-gray-800 dark:text-neutral-200"
                                            xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" viewBox="0 0 16 16">
                                            <path
                                                d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z">
                                            </path>
                                        </svg>
                                        <svg class="shrink-0 size-3 text-gray-800 dark:text-neutral-200"
                                            xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" viewBox="0 0 16 16">
                                            <path
                                                d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z">
                                            </path>
                                        </svg>
                                        <svg class="shrink-0 size-3 text-gray-800 dark:text-neutral-200"
                                            xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" viewBox="0 0 16 16">
                                            <path
                                                d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z">
                                            </path>
                                        </svg>
                                        <svg class="shrink-0 size-3 text-gray-800 dark:text-neutral-200"
                                            xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" viewBox="0 0 16 16">
                                            <path
                                                d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z">
                                            </path>
                                        </svg>
                                        <span class="ms-1 text-xs text-gray-800 dark:text-neutral-200">
                                            (4k+)
                                        </span>
                                    </div>
                                    <!-- End Review -->
                                </div>
                            </div>
                        </div>
                        <!-- End Category Card -->
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-3/4">
                        <DialogHeader>
                            <DialogTitle>Book me</DialogTitle>
                            <DialogDescription>
                                Make changes to your profile here. Click save when you're done.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-4">
                            <div class="relative w-full" style="padding-top: 75%;">
                                <img :src="babysitter.media.find(p => p.is_profile_picture)?.file_path"
                                    :alt="babysitter.name"
                                    class="absolute inset-0 w-full h-full object-cover rounded-md" />
                            </div>
                            <div class="flex flex-col w-full">
                                <div class="flex flex-row justify-between align-baseline mt-4 mr-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-2 gap-x-2">
                                            <h1 class="text-xl font-semibold"> {{
                                                babysitter.babysitter_profile.first_name
                                                }} {{
                                                    babysitter.babysitter_profile.last_name }}</h1>
                                            <Badge class="bg-emerald-500 dark:bg-emerald-600 ">
                                                {{ babysitter.babysitter_profile.price_per_hour }} $
                                            </Badge>
                                        </div>
                                        <p class="text-xs text-gray-400 dark:text-neutral-400">
                                            {{ babysitter.babysitter_profile.bio }}
                                        </p>
                                    </div>

                                </div>
                                <div v-if="babysitter.address" class="mt-1">
                                    <span class="text-sm text-gray-400 dark:text-neutral-400">
                                        {{ babysitter.address.street }} - {{ babysitter.address.country }} {{
                                            babysitter.address.city }} - {{ babysitter.address.postal_code }}</span>
                                </div>
                                <MediaScrollingHorizontalV2 :items="babysitter.media" class="mt-4" />
                                <div class="mt-4">
                                    <Link>
                                    <Button class="w-full mt-auto">
                                        Book me
                                    </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
        <div v-else class="text-center py-10">
            <p class="text-gray-500 dark:text-neutral-400">Aucune baby-sitter disponible pour le moment.</p>
        </div>
    </div>
</template>
