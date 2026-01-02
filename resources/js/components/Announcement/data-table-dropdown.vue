<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
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
    readOnly?: boolean
}>()

const { t } = useI18n()
const status = computed(() => (props.announcement.status ?? '').toString().toLowerCase())
const isReadOnly = computed(() => props.readOnly ?? false)
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
    if (!confirm(t('announcements.actions.confirm_delete'))) {
        return
    }
    router.delete(
        route('announcements.destroy', { announcement: props.announcement.id }),
        { preserveScroll: true },
    )
}

const viewAnnouncement = () => {
    router.get(route('announcements.show', { announcement: props.announcement.id }))
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 p-0">
                <span class="sr-only">{{ $t('common.labels.open_menu') }}</span>
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>{{ $t('common.table.actions') }}</DropdownMenuLabel>
            <DropdownMenuItem @click="viewAnnouncement">
                {{ isReadOnly ? $t('announcements.actions.view_details') : hasApplications ? $t('announcements.actions.view_applications') : $t('announcements.actions.view_details') }}
            </DropdownMenuItem>
            <DropdownMenuSeparator v-if="!isReadOnly && hasApplications" />
            <DropdownMenuItem v-if="!isReadOnly && canClose" @click="closeAnnouncement">
                {{ $t('announcements.actions.mark_filled') }}
            </DropdownMenuItem>
            <DropdownMenuSeparator v-if="!isReadOnly && canClose" />
            <DropdownMenuItem v-if="!isReadOnly" @click="deleteAnnouncement">
                {{ $t('announcements.actions.delete') }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
