<script setup lang="ts">
import { defineProps, withDefaults } from 'vue';
import type { Album } from '../data/albums';
import { cn } from '@/lib/utils';
import {
  ContextMenu,
  ContextMenuContent,
  ContextMenuItem,
  ContextMenuSeparator,
  ContextMenuSub,
  ContextMenuSubContent,
  ContextMenuSubTrigger,
  ContextMenuTrigger,
} from '@/components/ui/context-menu'; 
import { playlists } from '../data/playlists';

interface AlbumArtworkProps {
  album: Album;
  aspectRatio?: 'portrait' | 'square';
  width?: number;
  height?: number;
}

withDefaults(defineProps<AlbumArtworkProps>(), {
  aspectRatio: 'portrait',
  width: 300,
  height: 400,
});
</script>

<template>
  <div :class="cn('space-y-3', $attrs.class ?? '')">
    <ContextMenu>
      <ContextMenuTrigger>
        <div class="overflow-hidden rounded-md">
          <img
            :src="album.cover"
            :alt="album.name"
            :width="width"
            :height="height"
            :class="cn(
              'w-full object-cover transition-transform hover:scale-105',
              aspectRatio === 'portrait' ? 'aspect-[3/4]' : 'aspect-square'
            )"
          />
        </div>
      </ContextMenuTrigger>

      <ContextMenuContent class="w-40">
        <ContextMenuItem>Add to Library</ContextMenuItem>
        <ContextMenuSub>
          <ContextMenuSubTrigger>Add to Playlist</ContextMenuSubTrigger>
          <ContextMenuSubContent class="w-48">
            <ContextMenuItem> 
              New Playlist
            </ContextMenuItem>
            <ContextMenuSeparator />
            <ContextMenuItem
              v-for="item in playlists"
              :key="item.id || item"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="mr-2 h-4 w-4"
                viewBox="0 0 24 24"
              >
                <path d="M21 15V6M18.5 18a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM12 12H3M16 6H3M12 18H3" />
              </svg>
              {{ item.name ?? item }}
            </ContextMenuItem>
          </ContextMenuSubContent>
        </ContextMenuSub>
        <ContextMenuSeparator />
        <ContextMenuItem>Play Next</ContextMenuItem>
        <ContextMenuItem>Play Later</ContextMenuItem>
        <ContextMenuItem>Create Station</ContextMenuItem>
        <ContextMenuSeparator />
        <ContextMenuItem>Like</ContextMenuItem>
        <ContextMenuItem>Share</ContextMenuItem>
      </ContextMenuContent>
    </ContextMenu>

    <div class="space-y-1 text-sm">
      <h3 class="font-medium leading-none">{{ album.name }}</h3>
      <p class="text-xs text-muted-foreground">{{ album.artist }}</p>
    </div>
  </div>
</template>