<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps<{
    invoice: {
        id: number | string
        status?: string
    }
}>()

const page = usePage()
const role = computed(() => (page.props.auth as { role?: string }).role ?? 'Parent')
const status = computed(() => (props.invoice.status ?? '').toString().toLowerCase())
const isBabysitter = computed(() => role.value === 'Babysitter')
const canEdit = computed(() => isBabysitter.value && status.value === 'draft')
const viewLabel = computed(() => (canEdit.value ? 'Voir / Modifier' : 'Voir'))

function viewInvoice(id: number | string) {
    router.get(`/invoices/${id}`)
}

function downloadInvoice(id: number | string) {
    window.location.href = `/invoices/${id}/download`
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
            <DropdownMenuItem @click="viewInvoice(props.invoice.id)">
                {{ viewLabel }}
            </DropdownMenuItem>
            <DropdownMenuItem @click="downloadInvoice(props.invoice.id)">
                Telecharger PDF
            </DropdownMenuItem>
            <DropdownMenuSeparator />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
