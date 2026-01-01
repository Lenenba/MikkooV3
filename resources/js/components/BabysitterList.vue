<script setup lang="ts">
import { defineProps, withDefaults } from 'vue';
import { Link } from '@inertiajs/vue3';
import { type Babysitter, type Rating } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import MediaScrollingHorizontalV2 from '@/components/MediaScrollingHorizontal_v2.vue';
import { MapPin, Sparkles, Star } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

// Props with a default value for babysitters.
const props = withDefaults(defineProps<{ babysitters: Babysitter[] }>(), {
    babysitters: () => [],
});

const ratingStars = [1, 2, 3, 4, 5];
const reviewDateFormatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });
const currencyFormatter = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

const formatCurrency = (value: number | null | undefined) =>
    currencyFormatter.format(Number(value ?? 0));

const getRatingAverage = (babysitter: Babysitter) => {
    const value = Number(babysitter.rating_avg ?? 0);
    return Number.isFinite(value) ? value : 0;
};

const getRatingCount = (babysitter: Babysitter) => {
    const value = babysitter.rating_count ?? 0;
    return Number.isFinite(Number(value)) ? Number(value) : 0;
};

const formatRating = (value: number) => (Math.round(value * 10) / 10).toFixed(1);

const getProfile = (babysitter: Babysitter) => babysitter.babysitter_profile ?? null;

const getFullName = (babysitter: Babysitter) => {
    const profile = getProfile(babysitter);
    const fullName = [profile?.first_name, profile?.last_name].filter(Boolean).join(' ').trim();
    return fullName || babysitter.name || 'Babysitter';
};

const getInitials = (babysitter: Babysitter) => {
    const name = getFullName(babysitter).trim();
    if (!name) {
        return 'BS';
    }
    const parts = name.split(' ').filter(Boolean);
    const first = parts[0]?.charAt(0) ?? '';
    const second = parts[1]?.charAt(0) ?? '';
    return (first + second).toUpperCase() || 'BS';
};

const getLocation = (babysitter: Babysitter) => {
    const address = babysitter.address ?? null;
    if (!address) {
        return 'Location inconnue';
    }
    return [address.city, address.country].filter(Boolean).join(', ') || 'Location inconnue';
};

const getPrice = (babysitter: Babysitter) => getProfile(babysitter)?.price_per_hour ?? 0;

const getPriceLabel = (babysitter: Babysitter) => `${formatCurrency(getPrice(babysitter))} / h`;

const getBio = (babysitter: Babysitter) =>
    getProfile(babysitter)?.bio ?? 'Aucune description disponible pour le moment.';

const getExperience = (babysitter: Babysitter) =>
    getProfile(babysitter)?.experience ?? 'Experience non renseignee';

const getPaymentFrequency = (babysitter: Babysitter) =>
    getProfile(babysitter)?.payment_frequency ?? 'Flexible';

const getProfilePhoto = (babysitter: Babysitter) => {
    const media = babysitter.media ?? [];
    return (
        media.find((item) => item.is_profile_picture)?.file_path ??
        media[0]?.file_path ??
        ''
    );
};

const getReviews = (babysitter: Babysitter) =>
    (babysitter.received_ratings ?? []).filter((review) => Boolean(review.comment)) as Rating[];

const getReviewCount = (babysitter: Babysitter) => getReviews(babysitter).length;

const getReviewerName = (review: Rating) => review.reviewer?.name ?? 'Parent';

const getReviewerInitials = (review: Rating) => {
    const name = getReviewerName(review);
    const parts = name.split(' ').filter(Boolean);
    const first = parts[0]?.charAt(0) ?? '';
    const second = parts[1]?.charAt(0) ?? '';
    return (first + second).toUpperCase() || 'PR';
};

const formatReviewDate = (value?: string) => {
    if (!value) {
        return '';
    }
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return '';
    }
    return reviewDateFormatter.format(parsed);
};

const isTopRated = (babysitter: Babysitter) =>
    getRatingAverage(babysitter) >= 4.5 && getRatingCount(babysitter) >= 3;

const truncate = (value: string, max = 120) => {
    if (!value) {
        return '';
    }
    if (value.length <= max) {
        return value;
    }
    return `${value.slice(0, max).trim()}...`;
};
</script>

<template>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div v-if="props.babysitters.length > 0"
            class="grid items-start grid-cols-1 gap-7 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            <div v-for="babysitter in props.babysitters" :key="babysitter.id">
                <Dialog>
                    <DialogTrigger as-child>
                        <button type="button" class="w-full text-left">
                            <div
                                class="group flex flex-col overflow-hidden rounded-sm border border-border bg-card shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900">
                                <div class="relative aspect-[3/4] w-full overflow-hidden bg-muted">
                                    <img v-if="getProfilePhoto(babysitter)"
                                        class="h-full w-full object-cover transition duration-300 ease-out group-hover:scale-[1.03]"
                                        :src="getProfilePhoto(babysitter)" :alt="getFullName(babysitter)" />
                                    <div v-else
                                        class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-100 to-sky-100 text-3xl font-semibold text-emerald-700">
                                        {{ getInitials(babysitter) }}
                                    </div>
                                    <Badge v-if="isTopRated(babysitter)"
                                        class="absolute left-3 top-3 bg-amber-100 text-amber-700 shadow-sm backdrop-blur dark:bg-amber-500/20 dark:text-amber-200">
                                        <Sparkles class="mr-1 h-3.5 w-3.5" />
                                        Top rated
                                    </Badge>
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent" />
                                    <div class="absolute bottom-3 left-3 right-3 text-white">
                                        <div
                                            class="rounded-sm bg-black/45 px-3 py-2 backdrop-blur-sm shadow-sm">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-semibold leading-tight text-white truncate">
                                                        {{ getFullName(babysitter) }}
                                                    </p>
                                                    <p
                                                        class="mt-0.5 flex items-center gap-1 text-[10px] text-white/80 truncate">
                                                        <MapPin class="h-3 w-3" />
                                                        {{ getLocation(babysitter) }}
                                                    </p>
                                                </div>
                                                <span
                                                    class="rounded-full bg-card/15 px-2 py-1 text-[9px] font-semibold uppercase tracking-wide text-white/90 opacity-0 transition group-hover:opacity-100">
                                                    Voir profil
                                                </span>
                                            </div>
                                            <div class="mt-2 flex items-center gap-2 text-[9px]">
                                                <span
                                                    class="rounded-full bg-card/90 px-2.5 py-1 font-semibold text-foreground">
                                                    {{ getPriceLabel(babysitter) }}
                                                </span>
                                                <span
                                                    class="flex items-center gap-1 rounded-full bg-card/90 px-2.5 py-1 font-semibold text-foreground">
                                                    <Star class="h-3 w-3 text-amber-500" />
                                                    {{ formatRating(getRatingAverage(babysitter)) }}
                                                    <span class="text-muted-foreground">({{ getRatingCount(babysitter) }})</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </DialogTrigger>
                    <DialogContent
                        class="max-h-[92vh] w-[88vw] !max-w-[1200px] gap-6 overflow-y-auto !rounded-sm p-5 sm:w-[86vw] sm:p-6 lg:w-[82vw] lg:p-8 xl:w-[78vw] xl:!max-w-[1280px] 2xl:w-[72vw] 2xl:!max-w-[1320px]">
                        <DialogHeader class="border-b border-border pb-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <DialogTitle class="text-2xl">
                                        {{ getFullName(babysitter) }}
                                    </DialogTitle>
                                    <DialogDescription class="mt-1 flex items-center gap-2 text-sm">
                                        <MapPin class="h-4 w-4" />
                                        {{ getLocation(babysitter) }}
                                    </DialogDescription>
                                </div>
                                <Badge class="bg-emerald-500 text-white">
                                    {{ getPriceLabel(babysitter) }}
                                </Badge>
                            </div>
                        </DialogHeader>

                        <div class="grid gap-6 lg:grid-cols-[1.15fr_1fr] xl:grid-cols-[1.2fr_1fr] xl:gap-10">
                            <div class="space-y-4">
                                <div
                                    class="relative mx-auto w-full max-w-[480px] overflow-hidden rounded-sm bg-muted shadow-sm ring-1 ring-black/5 sm:max-w-[520px] lg:max-w-[560px]">
                                    <div class="aspect-[16/10] w-full">
                                        <img v-if="getProfilePhoto(babysitter)" :src="getProfilePhoto(babysitter)"
                                            :alt="getFullName(babysitter)" class="h-full w-full object-cover" />
                                        <div v-else
                                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-100 to-sky-100 text-4xl font-semibold text-emerald-700">
                                            {{ getInitials(babysitter) }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="rounded-sm border border-border bg-card p-3 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                                    <MediaScrollingHorizontalV2 :items="babysitter.media ?? []" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-5">
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="flex items-center gap-1">
                                        <Star v-for="value in ratingStars" :key="value" class="h-4 w-4" :class="getRatingAverage(babysitter) >= value
                                            ? 'text-amber-500'
                                            : 'text-muted-foreground/30'" />
                                    </div>
                                    <span class="font-semibold text-foreground">
                                        {{ formatRating(getRatingAverage(babysitter)) }}
                                    </span>
                                    <span class="text-muted-foreground">
                                        ({{ getRatingCount(babysitter) }} notes)
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                    <div class="rounded-sm border border-border bg-card p-3">
                                        <p class="text-xs text-muted-foreground/70">Experience</p>
                                        <p class="mt-1 font-medium text-foreground">
                                            {{ getExperience(babysitter) }}
                                        </p>
                                    </div>
                                    <div class="rounded-sm border border-border bg-card p-3">
                                        <p class="text-xs text-muted-foreground/70">Paiement</p>
                                        <p class="mt-1 font-medium text-foreground">
                                            {{ getPaymentFrequency(babysitter) }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-semibold text-foreground">A propos</h4>
                                    <p class="mt-2 text-sm text-muted-foreground">
                                        {{ getBio(babysitter) }}
                                    </p>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-foreground">Avis des parents</h4>
                                        <span class="text-xs text-muted-foreground">
                                            {{ getReviewCount(babysitter) }} avis
                                        </span>
                                    </div>
                                    <div class="mt-3 space-y-3">
                                        <div v-for="review in getReviews(babysitter)" :key="review.id"
                                            class="rounded-sm border border-border bg-muted/60 p-4">
                                            <div class="flex items-start gap-3">
                                                <div
                                                    class="flex h-9 w-9 items-center justify-center rounded-sm bg-card text-xs font-semibold text-muted-foreground">
                                                    {{ getReviewerInitials(review) }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <p class="text-sm font-semibold text-foreground">
                                                            {{ getReviewerName(review) }}
                                                        </p>
                                                        <span class="text-xs text-muted-foreground/70">
                                                            {{ formatReviewDate(review.created_at) }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-1 flex items-center gap-1">
                                                        <Star v-for="value in ratingStars" :key="value"
                                                            class="h-3.5 w-3.5" :class="review.rating >= value
                                                                ? 'text-amber-500'
                                                                : 'text-muted-foreground/30'" />
                                                    </div>
                                                    <p class="mt-2 text-xs text-muted-foreground">
                                                        {{ review.comment }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="getReviewCount(babysitter) === 0" class="text-xs text-muted-foreground">
                                            Pas encore d'avis pour cette babysitter.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <DialogFooter class="mt-6">
                            <Link key="book" :href="route('reservations.create', { id: babysitter.id })" class="w-full">
                                <Button class="w-full">
                                    Book me
                                </Button>
                            </Link>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
        <div v-else class="text-center py-10">
            <p class="text-muted-foreground dark:text-neutral-400">Aucune babysitter disponible pour le moment.</p>
        </div>
    </div>
</template>
