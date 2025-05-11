<script setup lang="ts">
import { ref, computed, defineProps, defineEmits, withDefaults } from 'vue'
import { format as formatDateFns } from 'date-fns'
import { CalendarIcon } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'

interface DatePickerProps {
    modelValue?: Date
    placeholder?: string
    displayFormat?: string
    buttonClassName?: string
    popoverClassName?: string
}

// On définit les props et leurs valeurs par défaut
const props = withDefaults(defineProps<DatePickerProps>(), {
    placeholder: 'Pick a date',
    displayFormat: 'PPP',
})

// On émettra à la fois l’événement v-model et un événement change
const emit = defineEmits(['update:modelValue', 'change'])

// Contrôle l’ouverture du Popover
const open = ref(false)

// Date formatée ou placeholder
const formattedDisplayDate = computed(() => {
    if (props.modelValue) {
        try {
            return formatDateFns(props.modelValue, props.displayFormat!)
        } catch {
            return 'Invalid date'
        }
    }
    return props.placeholder!
})

// Quand l’utilisateur choisit une date
function handleDateSelect(selectedDate: Date | undefined) {
    if (selectedDate) {
        emit('update:modelValue', selectedDate)
        emit('change', selectedDate)
    }
    open.value = false
}

// Classes dynamiques pour le bouton de déclenchement
const triggerButtonClasses = computed(() => [
    'w-full justify-start text-left font-normal',
    !props.modelValue && 'text-muted-foreground',
    props.buttonClassName,
])

// Classes dynamiques pour le contenu du popover
const popoverContentClasses = computed(() => [
    'w-auto p-0',
    props.popoverClassName,
])
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button variant="outline" :class="triggerButtonClasses">
                <CalendarIcon class="mr-2 h-4 w-4" />
                <span>{{ formattedDisplayDate }}</span>
            </Button>
        </PopoverTrigger>

        <PopoverContent :class="popoverContentClasses">
            <Calendar mode="single" :selected="props.modelValue" @select="handleDateSelect" initial-focus />
        </PopoverContent>
    </Popover>
</template>
