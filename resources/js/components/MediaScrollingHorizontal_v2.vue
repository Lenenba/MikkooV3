<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import type { MediaItem } from '@/types';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area'

const props = defineProps<{
    items: MediaItem[];
}>();
const { t } = useI18n();

</script>

<template>
    <div v-if="!items || items.length === 0">
        <p class="p-4 text-sm text-muted-foreground">{{ t('media.empty') }}</p>
    </div>
    <ScrollArea v-else class="w-full whitespace-nowrap rounded-md border">
        <div class="flex w-max space-x-4 p-4">
            <figure v-for="mediaItem in items" :key="mediaItem.id" class="shrink-0 w-24">
                <div class="overflow-hidden rounded-md">
                    <img :src="mediaItem.file_path" :alt="`${mediaItem.collection_name} #${mediaItem.id}`"
                        class="aspect-[3/4] h-auto w-full object-cover" loading="lazy" />
                </div>
                <figcaption class="pt-2 text-xs text-muted-foreground">
                    <span class="font-semibold text-foreground">
                        {{
                            mediaItem.is_profile
                                ? t('media.collection.profile')
                                : mediaItem.collection_name === 'garde' // Exemple de logique de nom d'affichage
                                    ? t('media.collection.guard')
                                    : mediaItem.collection_name
                        }}
                    </span>
                </figcaption>
            </figure>
        </div>
        <ScrollBar orientation="horizontal" />
    </ScrollArea>
</template>
