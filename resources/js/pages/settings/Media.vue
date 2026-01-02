<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { BreadcrumbItem, MediaItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import MediaUploadForm from '@/components/MediaUploadForm.vue';
import MediaScrollingHorizontal from '@/components/MediaScrollingHorizontal.vue';

defineProps<{
    media: MediaItem[];
}>();

const { t } = useI18n();
const breadcrumbs = computed<BreadcrumbItem[]>(() => ([
    {
        title: t('settings.media.title'),
        href: '/settings/media',
    },
]));
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head :title="$t('settings.media.title')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="$t('settings.media.title')"
                    :description="$t('settings.media.description')"
                />
                <p class="text-sm text-muted-foreground">
                    {{ $t('settings.media.body.line_1') }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ $t('settings.media.body.line_2') }}
                </p>

                <MediaUploadForm />
                <MediaScrollingHorizontal :items="media" />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
