<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
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
const { t } = useI18n()
const role = computed(() => (page.props.auth as { role?: string }).role ?? 'Parent')
const status = computed(() => (props.reservation.status ?? '').toString().toLowerCase())
const isBabysitter = computed(() => role.value === 'Babysitter')
const canAccept = computed(() => isBabysitter.value && status.value === 'pending')
const canComplete = computed(() => isBabysitter.value && status.value === 'confirmed')
const canCancel = computed(() => isBabysitter.value && !['canceled', 'completed'].includes(status.value))
const canRate = computed(() => role.value === 'Babysitter' || role.value === 'Parent')
const rateLabel = computed(() =>
    role.value === 'Babysitter' ? t('reservations.actions.rate_job') : t('reservations.actions.rate_reservation')
)

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
    if (event === 'complete') {
        router.post(`/reservations/${id}/complete`)
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
                <span class="sr-only">{{ $t('common.labels.open_menu') }}</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>{{ $t('common.table.actions') }}</DropdownMenuLabel>
            <DropdownMenuItem v-if="canAccept" @click="actionReservation(props.reservation.id, 'accept')">
                {{ $t('reservations.actions.confirm') }}
            </DropdownMenuItem>
            <DropdownMenuItem v-if="canComplete" @click="actionReservation(props.reservation.id, 'complete')">
                {{ $t('reservations.actions.complete') }}
            </DropdownMenuItem>
            <DropdownMenuItem v-if="canCancel" @click="actionReservation(props.reservation.id, 'cancel')">
                {{ $t('reservations.actions.cancel') }}
            </DropdownMenuItem>
            <DropdownMenuSeparator v-if="canAccept || canComplete || canCancel" />
            <DropdownMenuItem @click="actionReservation(props.reservation.id, 'view')">
                {{ $t('reservations.actions.view') }}
            </DropdownMenuItem>
            <DropdownMenuItem v-if="canRate" @click="actionReservation(props.reservation.id, 'rate')">
                {{ rateLabel }}
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
