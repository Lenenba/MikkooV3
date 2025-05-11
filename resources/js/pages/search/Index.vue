<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import { PlusCircledIcon } from 'lucide-vue-next'
import AlbumArtwork from './components/AlbumArtwork.vue'
import PodcastEmptyPlaceholder from './components/PodcastEmptyPlaceholder.vue'
import { listenNowAlbums, madeForYouAlbums } from './data/albums'
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Search Babysitter',
        href: '/search',
    },
];
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="col-span-3 lg:col-span-4 lg:border-l">
            <div class="h-full px-4 py-6 lg:px-8">
                <Tabs default-value="music" class="h-full space-y-6">
                    <div class="space-between flex items-center">
                        <TabsList>
                            <TabsTrigger value="music" class="relative">
                                Music
                            </TabsTrigger>
                            <TabsTrigger value="podcasts">
                                Podcasts
                            </TabsTrigger>
                            <TabsTrigger value="live" disabled>
                                Live
                            </TabsTrigger>
                        </TabsList>
                        <div class="ml-auto mr-4">
                            <Button>
                                Add music
                            </Button>
                        </div>
                    </div>
                    <TabsContent value="music" class="border-none p-0 outline-none">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <h2 class="text-2xl font-semibold tracking-tight">
                                    Listen Now
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Top picks for you. Updated daily.
                                </p>
                            </div>
                        </div>
                        <Separator class="my-4" />
                        <div class="relative">
                            <ScrollArea>
                                <div class="flex space-x-4 pb-4">
                                    <AlbumArtwork v-for="album in listenNowAlbums" :key="album.name" :album="album"
                                        class="w-[30vh]" aspect-ratio="portrait" :width="250" :height="330" />
                                </div>
                                <ScrollBar orientation="horizontal" />
                            </ScrollArea>
                        </div>
                        <div class="mt-6 space-y-1">
                            <h2 class="text-2xl font-semibold tracking-tight">
                                Made for You
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                Your personal playlists. Updated daily.
                            </p>
                        </div>
                        <Separator class="my-4" />
                        <div class="relative">
                            <ScrollArea>
                                <div class="flex space-x-4 pb-4">
                                    <AlbumArtwork v-for="album in madeForYouAlbums" :key="album.name" :album="album"
                                        class="w-[20vh]" aspect-ratio="square" :width="150" :height="150" />
                                </div>
                                <ScrollBar orientation="horizontal" />
                            </ScrollArea>
                        </div>
                    </TabsContent>
                    <TabsContent value="podcasts" class="h-full flex-col border-none p-0 data-[state=active]:flex">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <h2 class="text-2xl font-semibold tracking-tight">
                                    New Episodes
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Your favorite podcasts. Updated daily.
                                </p>
                            </div>
                        </div>
                        <Separator class="my-4" />
                        <PodcastEmptyPlaceholder />
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    </AppLayout>
</template>
