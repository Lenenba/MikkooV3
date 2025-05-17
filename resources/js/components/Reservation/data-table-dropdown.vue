<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'

defineProps<{
    reservation: {
        id: string
    }
}>()

function reservationSee(id: string) {
    console.log('See reservation', id)
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
            <DropdownMenuItem @click="reservationSee(reservation.id)">View reservation</DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem>View payment details</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
