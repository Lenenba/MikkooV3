<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem, MediaItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import MediaUploadForm from '@/components/MediaUploadForm.vue';
import MediaScrollingHorizontal from '@/components/MediaScrollingHorizontal.vue';

defineProps<{
    media: MediaItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Media settings',
        href: '/settings/media',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Media settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Media settings" description="Manage your uploaded media here" />
                <p class="text-sm text-muted-foreground">
                    You can upload images, videos, and other media files here. You can also set a profile photo.
                </p>
                <p class="text-sm text-muted-foreground">
                    You can upload up to 5 images at a time. The maximum file size is 5MB per image.
                </p>

                <MediaUploadForm />
                <MediaScrollingHorizontal :items="media" />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
