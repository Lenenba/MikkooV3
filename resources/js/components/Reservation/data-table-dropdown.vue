<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps<{
    reservation: {
        id: number | string
        status?: string
    }
}>()

const page = usePage()
const role = computed(() => (page.props.auth as { role?: string }).role ?? 'Parent')
const canRate = computed(() => role.value === 'Babysitter' || role.value === 'Parent')
const rateLabel = computed(() => (role.value === 'Babysitter' ? 'Noter le job' : 'Noter la reservation'))

/**
 * Trigger a POST request via Inertia to accept the reservation.
 * @param id Reservation ID
 */
function actionReservation(id: number | string, event: string) {
    if (event === 'accept') {
        router.post(`/reservations/${id}/accept`)
    }
    if (event === 'cancel') {
        router.post(`/reservations/${id}/cancel`)
    }
    if (event === 'view') {
        router.get(`/reservations/${id}/show`)
    }
    if (event === 'rate') {
        router.get(`/reservations/${id}/show`, { rate: 1 })
    }
}

</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem @click="actionReservation(props.reservation.id, 'accept')">
                Confirme reservation
            </DropdownMenuItem>
            <DropdownMenuItem @click="actionReservation(props.reservation.id, 'cancel')">
                Cancel reservation
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="actionReservation(props.reservation.id, 'view')">
                View reservation details
            </DropdownMenuItem>
            <DropdownMenuItem v-if="canRate" @click="actionReservation(props.reservation.id, 'rate')">
                {{ rateLabel }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
