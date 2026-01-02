<script setup lang="ts">
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
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

interface AddressSuggestion extends Address {
    display_name?: string
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
const { t } = useI18n()

// Reactive local address state
const address = reactive<Address>({
    ...props.modelValue
})

const MIN_QUERY_LENGTH = 3
const DEBOUNCE_MS = 300

// Query for autocomplete
const query = ref(address.street || '')
const suggestions = ref<AddressSuggestion[]>([])
const isLoading = ref(false)
const hasSearched = ref(false)
const hasError = ref(false)

let debounceTimer: ReturnType<typeof setTimeout> | null = null
let activeController: AbortController | null = null
let activeRequestId = 0
let suppressNextQuery = false

const showSuggestions = computed(() => {
    const trimmed = query.value.trim()
    if (trimmed.length < MIN_QUERY_LENGTH) {
        return false
    }
    return isLoading.value || hasSearched.value || suggestions.value.length > 0
})

const abortActiveRequest = () => {
    if (activeController) {
        activeController.abort()
        activeController = null
    }
}

const resetSuggestions = () => {
    suggestions.value = []
    hasSearched.value = false
    hasError.value = false
    isLoading.value = false
}

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
        const trimmed = q?.trim() ?? ''

        if (debounceTimer) {
            clearTimeout(debounceTimer)
            debounceTimer = null
        }

        if (suppressNextQuery) {
            suppressNextQuery = false
            return
        }

        if (trimmed.length < MIN_QUERY_LENGTH) {
            abortActiveRequest()
            resetSuggestions()
            return
        }

        debounceTimer = setTimeout(async () => {
            const requestId = ++activeRequestId
            hasError.value = false
            hasSearched.value = false
            isLoading.value = true

            abortActiveRequest()
            const controller = new AbortController()
            activeController = controller

            try {
                const { data } = await axios.get(route('onboarding.address.search'), {
                    params: {
                        query: trimmed,
                    },
                    signal: controller.signal,
                })
                if (requestId !== activeRequestId) {
                    return
                }
                suggestions.value = data?.results ?? []
            } catch (error: any) {
                if (error?.code === 'ERR_CANCELED' || error?.name === 'CanceledError' || error?.name === 'AbortError') {
                    return
                }
                if (requestId !== activeRequestId) {
                    return
                }
                suggestions.value = []
                hasError.value = true
            } finally {
                if (requestId === activeRequestId) {
                    isLoading.value = false
                    hasSearched.value = true
                }
            }
        }, DEBOUNCE_MS)
    }
)

// When a suggestion is selected
function selectSuggestion(item: AddressSuggestion) {
    suppressNextQuery = true
    abortActiveRequest()
    resetSuggestions()

    address.street = item.street ?? ''
    address.city = item.city ?? ''
    address.province = item.province ?? ''
    address.postal_code = item.postal_code ?? ''
    address.country = item.country ?? ''
    address.latitude = typeof item.latitude === 'number' ? item.latitude : undefined
    address.longitude = typeof item.longitude === 'number' ? item.longitude : undefined

    query.value = item.display_name || address.street || ''
    emit('update:modelValue', { ...address })
}

onBeforeUnmount(() => {
    if (debounceTimer) {
        clearTimeout(debounceTimer)
    }
    abortActiveRequest()
})
</script>

<template>
    <div class="relative">
        <!-- Autocomplete input -->
        <FloatingInput
            id="address-query"
            :label="t('common.labels.address_search')"
            v-model="query"
            type="text"
            autocomplete="off"
        />
        <ul
            v-if="showSuggestions"
            class="absolute z-10 mt-1 w-full rounded border border-border bg-popover text-foreground shadow-lg max-h-60 overflow-auto"
        >
            <li v-if="isLoading" class="px-4 py-2 text-sm text-muted-foreground">
                {{ t('address.search.loading') }}
            </li>
            <li v-else-if="hasError" class="px-4 py-2 text-sm text-destructive">
                {{ t('address.search.error') }}
            </li>
            <li v-else-if="!suggestions.length" class="px-4 py-2 text-sm text-muted-foreground">
                {{ t('address.search.empty') }}
            </li>
            <template v-else>
                <li
                    v-for="(item, index) in suggestions"
                    :key="index"
                    @click="selectSuggestion(item)"
                    class="px-4 py-2 hover:bg-muted cursor-pointer truncate"
                >
                    {{
                        item.display_name
                            || [item.street, item.city, item.country].filter(Boolean).join(', ')
                            || t('common.labels.address')
                    }}
                </li>
            </template>
        </ul>

        <!-- Address fields auto-filled -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <FloatingInput :label="t('common.labels.street')" v-model="address.street" disabled />
            </div>
            <div>
                <FloatingInput :label="t('common.labels.city')" v-model="address.city" disabled />
            </div>
            <div>
                <FloatingInput :label="t('common.labels.province')" v-model="address.province" disabled />
            </div>
            <div>
                <FloatingInput :label="t('common.labels.postal_code')" v-model="address.postal_code" disabled />
            </div>
            <div>
                <FloatingInput :label="t('common.labels.country')" v-model="address.country" disabled />
            </div>
            <div class="sm:col-span-2">
                <div class="flex space-x-2">
                    <FloatingInput :label="t('common.labels.latitude')" v-model="address.latitude" disabled />
                    <FloatingInput :label="t('common.labels.longitude')" v-model="address.longitude" disabled />
                </div>
            </div>
        </div>
    </div>
</template>
