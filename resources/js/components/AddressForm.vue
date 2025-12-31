<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import axios from 'axios'
import FloatingInput from '@/components/FloatingInput.vue'

// Define props and emits
interface Address {
    street?: string
    city?: string
    province?: string
    postal_code?: string
    country?: string
    latitude?: number
    longitude?: number
}

const props = withDefaults(defineProps<{
    /** v-model bound address object */
    modelValue?: Address
}>(), {
    modelValue: {}
})
const emit = defineEmits<{
    (e: 'update:modelValue', value: Address): void
}>()

// Reactive local address state
const address = reactive<Address>({
    ...props.modelValue
})

// Query for autocomplete
const query = ref(address.street || '')
const suggestions = ref<any[]>([])
const showSuggestions = ref(false)

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    (val) => {
        Object.assign(address, val || {})
        query.value = address.street || ''
    }
)

const geoapifyKey = import.meta.env.VITE_GEOAPIFY_KEY

// Watch query to fetch suggestions
watch(
    query,
    async (q) => {
        if (!q || q.length < 3) {
            suggestions.value = []
            showSuggestions.value = false
            return
        }
        if (!geoapifyKey) {
            suggestions.value = []
            showSuggestions.value = false
            return
        }
        try {
            const { data } = await axios.get('https://api.geoapify.com/v1/geocode/autocomplete', {
                params: {
                    text: q,
                    apiKey: geoapifyKey,
                    limit: 6,
                    lang: 'fr',
                }
            })
            suggestions.value = data?.features ?? []
            showSuggestions.value = true
        } catch {
            suggestions.value = []
            showSuggestions.value = false
        }
    }
)

// When a suggestion is selected
function selectSuggestion(item: any) {
    const props = item?.properties ?? {}
    const street = [props.housenumber, props.street].filter(Boolean).join(' ').trim()
    address.street = street || props.address_line1 || props.formatted || ''
    address.city = props.city || props.town || props.village || ''
    address.province = props.state || props.region || ''
    address.postal_code = props.postcode || ''
    address.country = props.country || ''
    address.latitude = typeof props.lat === 'number'
        ? props.lat
        : item?.geometry?.coordinates?.[1]
    address.longitude = typeof props.lon === 'number'
        ? props.lon
        : item?.geometry?.coordinates?.[0]

    query.value = address.street || props.formatted || ''
    suggestions.value = []
    showSuggestions.value = false

    emit('update:modelValue', { ...address })
}
</script>

<template>
    <div class="relative">
        <!-- Autocomplete input -->
        <FloatingInput
            id="address-query"
            label="Address search"
            v-model="query"
            type="text"
            autocomplete="off"
        />
        <ul v-if="showSuggestions"
            class="absolute z-10 mt-1 w-full bg-white border rounded shadow-lg max-h-60 overflow-auto">
            <li v-for="(item, index) in suggestions" :key="index" @click="selectSuggestion(item)"
                class="px-4 py-2 hover:bg-gray-100 cursor-pointer truncate">
                {{ item.display_name }}
            </li>
        </ul>

        <!-- Address fields auto-filled -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <FloatingInput label="Street" v-model="address.street" disabled />
            </div>
            <div>
                <FloatingInput label="City" v-model="address.city" disabled />
            </div>
            <div>
                <FloatingInput label="Province" v-model="address.province" disabled />
            </div>
            <div>
                <FloatingInput label="Postal Code" v-model="address.postal_code" disabled />
            </div>
            <div>
                <FloatingInput label="Country" v-model="address.country" disabled />
            </div>
            <div class="sm:col-span-2">
                <div class="flex space-x-2">
                    <FloatingInput label="Latitude" v-model="address.latitude" disabled />
                    <FloatingInput label="Longitude" v-model="address.longitude" disabled />
                </div>
            </div>
        </div>
    </div>
</template>
