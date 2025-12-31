<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { type Address, type SharedData } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface AddressResult {
    display_name: string;
    street?: string | null;
    city?: string | null;
    province?: string | null;
    postal_code?: string | null;
    country?: string | null;
    latitude?: number | null;
    longitude?: number | null;
}

interface AddressPageProps extends SharedData {
    address?: Address | null;
}

const page = usePage<AddressPageProps>();
const existingAddress = computed(() => page.props.address ?? null);

const query = ref('');
const results = ref<AddressResult[]>([]);
const isSearching = ref(false);

const form = useForm({
    street: existingAddress.value?.street ?? '',
    city: existingAddress.value?.city ?? '',
    province: existingAddress.value?.province ?? '',
    postal_code: existingAddress.value?.postal_code ?? '',
    country: existingAddress.value?.country ?? '',
    latitude: existingAddress.value?.latitude ?? '',
    longitude: existingAddress.value?.longitude ?? '',
});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const fetchResults = async () => {
    const value = query.value.trim();
    if (value.length < 3) {
        results.value = [];
        return;
    }

    isSearching.value = true;
    try {
        const response = await fetch(route('onboarding.address.search', { query: value }), {
            headers: {
                Accept: 'application/json',
            },
        });
        if (!response.ok) {
            results.value = [];
            return;
        }
        const payload = await response.json();
        results.value = payload?.results ?? [];
    } catch {
        results.value = [];
    } finally {
        isSearching.value = false;
    }
};

watch(
    () => query.value,
    () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(fetchResults, 350);
    }
);

const selectResult = (result: AddressResult) => {
    form.street = result.street ?? '';
    form.city = result.city ?? '';
    form.province = result.province ?? '';
    form.postal_code = result.postal_code ?? '';
    form.country = result.country ?? '';
    form.latitude = result.latitude ?? '';
    form.longitude = result.longitude ?? '';
    query.value = result.display_name ?? '';
    results.value = [];
};

onBeforeUnmount(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});
</script>

<template>
    <Head title="Adresse" />

    <AppLayout>
        <div class="container mx-auto px-4 py-10 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl space-y-6">
                <div class="rounded-sm border border-gray-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">
                        Completer votre adresse
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                        Cherchez votre adresse pour remplir automatiquement les champs.
                    </p>

                    <div class="mt-4 space-y-3">
                        <label class="text-xs font-medium text-gray-500" for="address-search">
                            Recherche
                        </label>
                        <Input
                            id="address-search"
                            v-model="query"
                            type="text"
                            placeholder="Ex: 123 Rue Sainte-Catherine, Montreal"
                        />
                        <div v-if="isSearching" class="text-xs text-gray-400">
                            Recherche en cours...
                        </div>
                        <div v-if="results.length > 0" class="rounded-sm border border-gray-100 bg-white shadow-sm">
                            <button
                                v-for="result in results"
                                :key="result.display_name"
                                type="button"
                                class="flex w-full flex-col gap-1 border-b border-gray-100 px-4 py-3 text-left text-sm hover:bg-gray-50 last:border-b-0"
                                @click="selectResult(result)"
                            >
                                <span class="font-medium text-gray-900">{{ result.display_name }}</span>
                                <span class="text-xs text-gray-500">
                                    {{ result.city ?? 'Ville inconnue' }} · {{ result.country ?? 'Pays inconnu' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="rounded-sm border border-gray-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-neutral-100">Details</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-xs font-medium text-gray-500" for="street">
                                Rue
                            </label>
                            <Input id="street" v-model="form.street" type="text" placeholder="Rue et numero" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-500" for="city">
                                Ville
                            </label>
                            <Input id="city" v-model="form.city" type="text" placeholder="Ville" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-500" for="province">
                                Province
                            </label>
                            <Input id="province" v-model="form.province" type="text" placeholder="Province" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-500" for="postal_code">
                                Code postal
                            </label>
                            <Input id="postal_code" v-model="form.postal_code" type="text" placeholder="Code postal" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-500" for="country">
                                Pays
                            </label>
                            <Input id="country" v-model="form.country" type="text" placeholder="Pays" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-500" for="latitude">
                                Latitude
                            </label>
                            <Input id="latitude" v-model="form.latitude" type="text" placeholder="Latitude" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-500" for="longitude">
                                Longitude
                            </label>
                            <Input id="longitude" v-model="form.longitude" type="text" placeholder="Longitude" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end">
                        <Button :disabled="form.processing" @click="form.post(route('onboarding.address.store'))">
                            Enregistrer l'adresse
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
