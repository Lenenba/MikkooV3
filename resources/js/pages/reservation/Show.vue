<script setup lang="ts">
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import FloatingTextarea from '@/components/FloatingTextarea.vue';
import { Head, usePage, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type SharedData, type BreadcrumbItem, type Reservation, type Details, type Services, type Babysitter, type Address, type RatingsPayload } from '@/types';
import { Star } from 'lucide-vue-next';

const page = usePage<SharedData>();

type ReservationShow = Reservation & {
    number?: string;
    babysitter?: Babysitter | null;
    details?: Details | Details[] | null;
    services?: Services[];
    total_amount?: number;
};

const reservation = computed<ReservationShow | null>(() => page.props.reservation ?? null);
const reservationId = computed(() => reservation.value?.id ?? null);
const ratings = computed<RatingsPayload | null>(() => page.props.ratings ?? null);
const canRate = computed(() => ratings.value?.can_rate ?? false);
const myRating = computed(() => ratings.value?.mine ?? null);
const otherRating = computed(() => ratings.value?.other ?? null);
const ratingTargetName = computed(() => ratings.value?.target_name ?? 'this user');
const details = computed<Details | null>(() => {
    const raw = reservation.value?.details ?? null;
    return Array.isArray(raw) ? raw[0] ?? null : raw;
});
const services = computed<Services[]>(() => reservation.value?.services ?? []);
const babysitter = computed<Babysitter | null>(() => reservation.value?.babysitter ?? null);
const profile = computed(() => babysitter.value?.babysitter_profile ?? null);
const address = computed<Address | null>(() => babysitter.value?.address ?? null);

const reservationNumber = computed(() =>
    reservation.value?.number ?? reservation.value?.ref ?? reservation.value?.id ?? '-'
);

const status = computed(() =>
    (details.value?.status ?? reservation.value?.status ?? 'unknown').toString().toLowerCase()
);

const statusMeta = computed(() => {
    const current = status.value;
    if (current === 'confirmed') {
        return { label: 'Confirmee', className: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
    }
    if (current === 'pending') {
        return { label: 'En attente', className: 'bg-amber-50 text-amber-700 border-amber-200' };
    }
    if (current === 'canceled' || current === 'cancelled') {
        return { label: 'Annulee', className: 'bg-red-50 text-red-700 border-red-200' };
    }
    return { label: 'Inconnu', className: 'bg-muted text-muted-foreground border-border' };
});

const canCancel = computed(() => {
    const current = status.value;
    return current !== 'canceled' && current !== 'cancelled';
});

const currencyFormatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});

const toNumber = (value: number | string | null | undefined) => {
    if (typeof value === 'number' && Number.isFinite(value)) {
        return value;
    }
    if (typeof value === 'string') {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : 0;
    }
    return 0;
};

const formatCurrency = (value: number | string | null | undefined) =>
    currencyFormatter.format(toNumber(value));

const formatDate = (value: string | null | undefined) => {
    if (!value) {
        return '-';
    }
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }
    return new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(parsed);
};

const formatTime = (value: string | null | undefined) => {
    if (!value) {
        return '-';
    }
    const [hours, minutes] = value.split(':');
    if (!hours || !minutes) {
        return value;
    }
    return `${hours}:${minutes}`;
};

const getServiceLabel = (service: Services) =>
    service.description ?? (service as { name?: string }).name ?? 'Service';

const getServicePivot = (service: Services) => {
    const pivot = (service as { pivot?: { price?: number; quantity?: number } | Array<{ price?: number; quantity?: number }> }).pivot;
    if (Array.isArray(pivot)) {
        return pivot[0] ?? {};
    }
    return pivot ?? {};
};

const getServiceUnitPrice = (service: Services) => {
    const pivot = getServicePivot(service);
    return toNumber((service as { price?: number | string }).price ?? pivot.price);
};

const getServiceQuantity = (service: Services) => {
    const pivot = getServicePivot(service);
    return toNumber(pivot.quantity);
};

const getServiceTotal = (service: Services) =>
    getServiceUnitPrice(service) * getServiceQuantity(service);

const caculSubtotal = computed(() =>
    services.value.reduce((total, service) => total + getServiceTotal(service), 0)
);

const calculTax = computed(() => {
    const TAX_RATE = 0.14975;
    return Number((caculSubtotal.value * TAX_RATE).toFixed(2));
});

const totalAmount = computed(() =>
    toNumber(reservation.value?.total_amount ?? caculSubtotal.value + calculTax.value)
);

const defaultProfilePhoto = '/bbsiter.png';
const babysitterPhoto = computed(() => {
    const media = babysitter.value?.media ?? [];
    return media.find(item => item.is_profile_picture)?.file_path ?? media[0]?.file_path ?? defaultProfilePhoto;
});

const babysitterName = computed(() => {
    const fullName = [profile.value?.first_name, profile.value?.last_name].filter(Boolean).join(' ').trim();
    return fullName || babysitter.value?.name || 'Babysitter';
});

const addressState = computed(() => {
    const fallback = address.value as { province?: string } | null;
    return address.value?.state ?? fallback?.province ?? '';
});

const addressLine1 = computed(() =>
    [address.value?.street, addressState.value].filter(Boolean).join(' ')
);

const addressLine2 = computed(() =>
    [address.value?.city, address.value?.postal_code].filter(Boolean).join(' ')
);

const addressLine3 = computed(() => address.value?.country ?? '');

const contactEmail = computed(() => babysitter.value?.email ?? '');
const contactPhone = computed(() => profile.value?.phone ?? '');
const birthdateLabel = computed(() => formatDate(profile.value?.birthdate));

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mes Reservations',
        href: '/reservations',
    },
    {
        title: 'Ma reservation',
        href: `/reservations/${reservation.value?.id ?? ''}/show`,
    },
];

const ratingStars = [1, 2, 3, 4, 5];
const ratingHover = ref<number | null>(null);
const ratingForm = useForm({
    rating: myRating.value?.rating ?? 0,
    comment: myRating.value?.comment ?? '',
});

const displayRating = computed(() => ratingHover.value ?? ratingForm.rating);
const isStarActive = (value: number) => displayRating.value >= value;
const submitRating = () => {
    if (!reservationId.value) {
        return;
    }

    ratingForm.post(route('reservations.ratings.store', { reservation: reservationId.value }), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="reservation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="reservation" class="mx-auto w-full lg:w-3/4">
            <div
                class="p-5 space-y-4 flex flex-col lg:flex-row bg-muted border border-border rounded-sm shadow-sm xl:shadow-none dark:bg-green-800 dark:border-green-700">
                <!-- Left: profile image -->
                <div class="lg:w-1/4 mb-4 lg:mb-0">
                    <div class="w-full overflow-hidden rounded-sm bg-muted">
                        <img v-if="babysitterPhoto" :src="babysitterPhoto" :alt="babysitterName"
                            class="w-full object-cover" />
                        <div v-else class="flex h-40 w-full items-center justify-center text-xs text-muted-foreground/70">
                            Aucune photo
                        </div>
                    </div>
                </div>

                <!-- Right: content -->
                <div class="flex-1 space-y-6 ml-4">
                    <!-- Header -->
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h1 class="text-xl font-semibold text-foreground dark:text-green-100">
                                Reservation pour {{ babysitterName }}
                            </h1>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <Badge variant="outline" :class="statusMeta.className">
                                    {{ statusMeta.label }}
                                </Badge>
                                <span class="text-xs text-muted-foreground">Ref: {{ reservationNumber }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Button asChild variant="outline" size="sm">
                                <Link :href="route('reservations.index')">Retour</Link>
                            </Button>
                            <Button v-if="canCancel && reservationId" asChild variant="destructive" size="sm">
                                <Link :href="route('reservations.cancel', { reservationId })" method="post">
                                    Annuler
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <!-- Main grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">
                        <!-- Left side (2/3) -->
                        <div class="col-span-3 space-y-4">
                            <div class="">
                                <!-- Notes -->
                                <div class="mb-8">
                                    <Label for="note" class="font-semibold text-foreground mb-2">Notes pour la
                                        reservation :</Label>
                                    <p>{{ reservation.notes ?? '-' }}</p>
                                </div>
                                <!-- Address & contact -->
                                <div class="flex flex-col lg:flex-row lg:space-x-6">
                                    <!-- Property address -->
                                    <div class="flex-1">
                                        <p class="font-semibold text-foreground">Property address</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ addressLine1 || '-' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ addressLine2 || '-' }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ addressLine3 || '-' }}
                                        </p>
                                    </div>
                                    <!-- Contact details -->
                                    <div class="flex-1">
                                        <p class="font-semibold text-foreground">Contact details</p>
                                        <p class="text-xs text-muted-foreground">{{ contactEmail || '-' }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ birthdateLabel }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ contactPhone || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right side (1/3) -->
                        <div class="bg-card p-4 rounded col-span-2">
                            <p class="font-semibold text-foreground mb-2">Reservation details</p>
                            <div class="text-xs text-muted-foreground flex justify-between">
                                <span>Numero :</span>
                                <span>{{ reservationNumber }}</span>
                            </div>

                            <div class="my-2 flex flex-row justify-between">
                                <p class="text-xs text-muted-foreground mb-1">Reservation date</p>
                                <p class="text-xs text-muted-foreground mb-1">{{ formatDate(details?.date) }}</p>
                            </div>
                            <div class="my-2 flex flex-row justify-between">
                                <p class="text-xs text-muted-foreground mb-1">Reservation heure de debut</p>
                                <p class="text-xs text-muted-foreground mb-1">{{ formatTime(details?.start_time) }}</p>
                            </div>
                            <div class="my-2 flex flex-row justify-between">
                                <p class="text-xs text-muted-foreground mb-1">Reservation heure de fin</p>
                                <p class="text-xs text-muted-foreground mb-1">{{ formatTime(details?.end_time) }}</p>
                            </div>
                            <div v-if="canRate || myRating || otherRating" class="mt-3 border-t border-border pt-3">
                                <div class="flex items-center justify-between text-xs text-muted-foreground">
                                    <span>Rate {{ ratingTargetName }}:</span>
                                    <div class="flex items-center gap-1">
                                        <button
                                            v-for="value in ratingStars"
                                            :key="value"
                                            type="button"
                                            class="transition"
                                            :disabled="!canRate || ratingForm.processing"
                                            @mouseenter="ratingHover = value"
                                            @mouseleave="ratingHover = null"
                                            @click="ratingForm.rating = value"
                                        >
                                            <Star
                                                class="h-4 w-4"
                                                :class="isStarActive(value) ? 'text-amber-400' : 'text-muted-foreground/30'"
                                            />
                                        </button>
                                    </div>
                                </div>

                                <div v-if="canRate" class="mt-3 space-y-2">
                                    <FloatingTextarea
                                        v-model="ratingForm.comment"
                                        rows="2"
                                        label="Note"
                                    />
                                    <div class="flex items-center justify-between">
                                        <span v-if="ratingForm.errors.rating" class="text-xs text-red-600">
                                            {{ ratingForm.errors.rating }}
                                        </span>
                                        <Button
                                            size="sm"
                                            class="ml-auto"
                                            :disabled="ratingForm.processing || ratingForm.rating === 0"
                                            @click="submitRating"
                                        >
                                            Submit rating
                                        </Button>
                                    </div>
                                </div>

                                <div v-if="otherRating" class="mt-3 text-xs text-muted-foreground">
                                    They rated you: {{ otherRating.rating }}/5
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="p-5 space-y-3 flex flex-col bg-card border border-border rounded-sm shadow-sm xl:shadow-none dark:bg-green-800 dark:border-green-700">
                <!-- Table Section -->
                <div
                    class="overflow-x-auto [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-muted [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                    <div class="min-w-full inline-block align-middle min-h-[300px]">
                        <!-- Table -->
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead>
                                <tr>
                                    <th scope="col" class="min-w-[430px] ">
                                        <div
                                            class="px-4 py-3 text-start flex items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                            Services
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                            Nbre d'enfant (s)
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                            Unit price
                                        </div>
                                    </th>

                                    <th scope="col">
                                        <div
                                            class="px-4 py-3 text-start items-center gap-x-1 text-sm font-medium text-foreground dark:text-neutral-200">
                                            Total
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                <!-- Ligne de service -->
                                <tr v-for="service in services" :key="service.id">
                                    <td class="size-px whitespace-nowrap px-4 py-3 ">
                                        {{ getServiceLabel(service) }}
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        {{ getServiceQuantity(service) }}
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        {{ formatCurrency(getServiceUnitPrice(service)) }}
                                    </td>
                                    <td class="size-px whitespace-nowrap px-4 py-3">
                                        {{ formatCurrency(getServiceTotal(service)) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End Table -->
                    </div>
                </div>
            </div>
            <div
                class="p-5 grid grid-cols-2 gap-4 justify-between bg-card border border-border rounded-sm shadow-sm xl:shadow-none dark:bg-green-800 dark:border-green-700">

                <div>

                </div>
                <div class="border-l border-border dark:border-neutral-700 rounded-sm p-4">
                    <!-- List Item -->
                    <div class="py-4 grid grid-cols-2 gap-x-4  dark:border-neutral-700">
                        <div class="col-span-1">
                            <p class="text-sm text-muted-foreground dark:text-neutral-500">
                                Subtotal:
                            </p>
                        </div>
                        <div class="col-span-1 flex justify-end">
                            <p class="text-sm text-green-600 decoration-2 hover:underline font-medium focus:outline-none focus:underline dark:text-green-400 dark:hover:text-green-500"
                                href="#">
                                {{ formatCurrency(caculSubtotal) }}
                            </p>
                        </div>
                    </div>
                    <!-- Section des détails des taxes (affichée ou masquée) -->
                    <div class="space-y-2 py-4 border-t border-border dark:border-neutral-700">
                        <div class="flex justify-between font-bold">
                            <p class="text-sm text-foreground dark:text-neutral-200">Total taxes :</p>
                             <p class="text-sm text-foreground dark:text-neutral-200">
                                 {{ formatCurrency(calculTax) }}
                             </p>
                        </div>
                    </div>
                    <!-- End List Item -->

                    <!-- List Item -->
                    <div class="py-4 grid grid-cols-2 gap-x-4 border-t border-border dark:border-neutral-700">
                        <div class="col-span-1">
                            <p class="text-sm text-foreground font-bold dark:text-neutral-500">
                                Total amount:
                            </p>
                        </div>
                        <div class="flex justify-end">
                            <p class="text-sm text-foreground font-bold dark:text-neutral-200">
                                {{ formatCurrency(totalAmount) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="mx-auto w-full lg:w-3/4 rounded-sm border border-border bg-card p-6 text-sm text-muted-foreground">
            Reservation introuvable.
        </div>
    </AppLayout>
</template>
