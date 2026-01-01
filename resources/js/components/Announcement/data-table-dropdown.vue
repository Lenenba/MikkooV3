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
        applications_count?: number | null
        pending_applications_count?: number | null
    }
}>()

const status = computed(() => (props.announcement.status ?? '').toString().toLowerCase())
const canClose = computed(() => status.value !== 'closed')
const hasApplications = computed(() => {
    const pending = Number(props.announcement.pending_applications_count ?? 0)
    const total = Number(props.announcement.applications_count ?? 0)
    return pending > 0 || total > 0
})

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

const viewApplications = () => {
    router.get(route('announcements.show', { announcement: props.announcement.id }))
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
            <DropdownMenuItem v-if="hasApplications" @click="viewApplications">
                Voir candidatures
            </DropdownMenuItem>
            <DropdownMenuSeparator v-if="hasApplications" />
            <DropdownMenuItem v-if="canClose" @click="closeAnnouncement">
                Marquer comme pourvue
            </DropdownMenuItem>
            <DropdownMenuSeparator v-if="canClose" />
            <DropdownMenuItem @click="deleteAnnouncement">
                Supprimer l'annonce
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
