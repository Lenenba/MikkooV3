<script setup lang="ts">
import { ref, reactive, watch, toRefs } from 'vue'
import axios from 'axios'

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

// Watch query to fetch suggestions
watch(
    query,
    async (q) => {
        if (!q || q.length < 3) {
            suggestions.value = []
            showSuggestions.value = false
            return
        }
        try {
            const { data } = await axios.get('https://api.locationiq.com/v1/autocomplete.php', {
                params: {
                    key: import.meta.env.VITE_LOCATIONIQ_KEY,
                    q,
                    format: 'json',
                    addressdetails: 1
                }
            })
            suggestions.value = data
            showSuggestions.value = true
        } catch {
            suggestions.value = []
            showSuggestions.value = false
        }
    }
)

// When a suggestion is selected
function selectSuggestion(item: any) {
    const addr = item.address || {}
    address.street = addr.road || addr.house_number
        ? `${addr.house_number || ''} ${addr.road || ''}`.trim()
        : item.display_name
    address.city = addr.city || addr.town || addr.village || ''
    address.province = addr.state || ''
    address.postal_code = addr.postcode || ''
    address.country = addr.country || ''
    address.latitude = parseFloat(item.lat)
    address.longitude = parseFloat(item.lon)

    query.value = address.street || item.display_name
    suggestions.value = []
    showSuggestions.value = false

    emit('update:modelValue', { ...address })
}
</script>

<template>
    <div class="relative">
        <!-- Autocomplete input -->
        <input v-model="query" type="text" placeholder="Start typing address..." class="w-full border rounded p-2" />
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
                <label class="block text-sm font-medium mb-1">Street</label>
                <input v-model="address.street" disabled class="w-full border rounded p-2 bg-gray-100" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">City</label>
                <input v-model="address.city" disabled class="w-full border rounded p-2 bg-gray-100" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Province</label>
                <input v-model="address.province" disabled class="w-full border rounded p-2 bg-gray-100" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Postal Code</label>
                <input v-model="address.postal_code" disabled class="w-full border rounded p-2 bg-gray-100" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Country</label>
                <input v-model="address.country" disabled class="w-full border rounded p-2 bg-gray-100" />
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1">Latitude, Longitude</label>
                <div class="flex space-x-2">
                    <input v-model="address.latitude" disabled class="w-1/2 border rounded p-2 bg-gray-100" />
                    <input v-model="address.longitude" disabled class="w-1/2 border rounded p-2 bg-gray-100" />
                </div>
            </div>
        </div>
    </div>
</template>
