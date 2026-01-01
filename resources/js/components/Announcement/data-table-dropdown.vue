<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
    announcement: {
        id: number | string
        status?: string | null
    }
}>()

const status = computed(() => (props.announcement.status ?? '').toString().toLowerCase())
const canClose = computed(() => status.value !== 'closed')

const closeAnnouncement = () => {
    if (!canClose.value) {
        return
    }
    router.patch(
        route('announcements.update', { announcement: props.announcement.id }),
        { status: 'closed' },
        { preserveScroll: true },
    )
}

const deleteAnnouncement = () => {
    if (!confirm('Supprimer cette annonce ?')) {
        return
    }
    router.delete(
        route('announcements.destroy', { announcement: props.announcement.id }),
        { preserveScroll: true },
    )
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem v-if="canClose" @click="closeAnnouncement">
                Fermer l'annonce
            </DropdownMenuItem>
            <DropdownMenuSeparator v-if="canClose" />
            <DropdownMenuItem @click="deleteAnnouncement">
                Supprimer l'annonce
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
