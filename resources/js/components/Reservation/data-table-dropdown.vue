<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
defineProps<{
    reservation: {
        id: string
    }
}>()

/**
 * Trigger a POST request via Inertia to accept the reservation.
 * @param id Reservation ID
 */
function actionReservation(id: string, event: string) {
    if (event === 'accept') {
        router.post(`/reservations/${id}/accept`)
    }
    if (event === 'cancel') {
        router.post(`/reservations/${id}/cancel`)
    }
    if (event === 'view') {
        router.get(`/reservations/${id}/show`)
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
            <DropdownMenuItem @click="actionReservation(reservation.id, 'accept')">
                Confirme reservation
            </DropdownMenuItem>
            <DropdownMenuItem @click="actionReservation(reservation.id, 'cancel')">
                Cancel reservation
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="actionReservation(reservation.id, 'view')">
                View reservation details
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
