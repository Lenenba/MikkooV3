<script setup lang="ts">
import { computed } from 'vue';
import { columns } from '@/components/Reservation/columns';
import FloatingInput from '@/components/FloatingInput.vue';
import FloatingSelect from '@/components/FloatingSelect.vue';
import { Button } from '@/components/ui/button';
import DataTableViewOptions from '@/components/columnToggle.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Reservation, type SharedData, type Stats } from '@/types';
import { Head, usePage, Link } from '@inertiajs/vue3';
import DataTable from '@/components/Reservation/data-table.vue';
import {
    CalendarCheck,
    ClipboardList,
    DollarSign,
    Plus,
    Search,
    TrendingDown,
    TrendingUp,
    Wallet,
    XCircle,
} from 'lucide-vue-next';

// Shared page props
const page = usePage<SharedData>();

const emptyStats: Stats = {
    current_month_revenue: 0,
    previous_month_revenue: 0,
    revenue_change_pct: null,
    current_month_count: 0,
    previous_month_count: 0,
    count_change_pct: null,
    avg_revenue_per_booking: null,
    total_revenue: 0,
    total_count: 0,
    total_canceled_count: 0,
};

const currencyFormatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});
const numberFormatter = new Intl.NumberFormat('en-US');
const percentFormatter = new Intl.NumberFormat('en-US', { maximumFractionDigits: 1 });

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

const formatCount = (value: number | string | null | undefined) =>
    numberFormatter.format(toNumber(value));

const formatPercent = (value: number | string | null | undefined) => {
    const safeValue = toNumber(value);
    const sign = safeValue > 0 ? '+' : '';
    return `${sign}${percentFormatter.format(safeValue)}%`;
};

const getTrendMeta = (value: number | string | null | undefined) => {
    const safeValue = toNumber(value);
    if (safeValue > 0) {
        return { icon: TrendingUp, className: 'text-emerald-600', show: true };
    }
    if (safeValue < 0) {
        return { icon: TrendingDown, className: 'text-red-600', show: true };
    }
    return { icon: TrendingUp, className: 'text-gray-400', show: false };
};

const sparklinePoints = (values: number[]) => {
    if (!values.length) {
        return '';
    }
    const width = 120;
    const height = 40;
    const max = Math.max(...values);
    const min = Math.min(...values);
    const range = max - min || 1;
    const step = values.length > 1 ? width / (values.length - 1) : width;

    return values
        .map((value, index) => {
            const x = Number((index * step).toFixed(2));
            const y = Number((height - ((value - min) / range) * height).toFixed(2));
            return `${x},${y}`;
        })
        .join(' ');
};

const buildTrendSeries = (previous: number | string | null | undefined, current: number | string | null | undefined) => {
    const start = toNumber(previous);
    const end = toNumber(current);
    const length = 10;
    const step = (end - start) / Math.max(length - 1, 1);
    const variance = Math.abs(end - start) * 0.08;

    return Array.from({ length }, (_, index) => {
        const base = start + step * index;
        const wave = Math.sin(index * 0.9) * variance;
        return Math.max(0, Number((base + wave).toFixed(2)));
    });
};

// Raw reservations list
const reservations = computed<Reservation[]>(
    () => page.props.reservations?.data ?? []
);

// Raw stats
const statistique = computed<Stats>(
    () => page.props.stats ?? emptyStats
);

const reservationCountLabel = computed(() =>
    reservations.value.length === 1 ? 'reservation' : 'reservations'
);
const totalReservations = computed(() => {
    const fromStats = statistique.value.total_count;
    if (typeof fromStats === 'number' && Number.isFinite(fromStats)) {
        return fromStats;
    }
    return reservations.value.length;
});

const statusOptions = [
    { value: 'all', label: 'Tous les statuts' },
    { value: 'pending', label: 'En attente' },
    { value: 'confirmed', label: 'Confirmee' },
    { value: 'canceled', label: 'Annulee' },
];

const statCards = computed(() => {
    const revenueTrend = getTrendMeta(statistique.value.revenue_change_pct);
    const countTrend = getTrendMeta(statistique.value.count_change_pct);
    const currentMonthCount = toNumber(statistique.value.current_month_count);
    const currentMonthRevenue = toNumber(statistique.value.current_month_revenue);
    const totalRevenue = toNumber(statistique.value.total_revenue);
    const totalCanceled = toNumber(statistique.value.total_canceled_count);
    const totalCount = totalReservations.value;

    return [
        {
            title: 'Total reservations',
            value: formatCount(totalCount),
            change: '',
            changeText: 'au total',
            trendIcon: TrendingUp,
            trendClass: 'text-gray-400',
            showTrend: false,
            icon: ClipboardList,
            iconClass: 'bg-violet-100 text-violet-600',
            sparkline: buildTrendSeries(Math.max(0, totalCount - currentMonthCount), totalCount),
            sparklineClass: 'stroke-violet-400',
        },
        {
            title: 'Revenu total',
            value: formatCurrency(totalRevenue),
            change: '',
            changeText: 'au total',
            trendIcon: TrendingUp,
            trendClass: 'text-gray-400',
            showTrend: false,
            icon: DollarSign,
            iconClass: 'bg-emerald-100 text-emerald-600',
            sparkline: buildTrendSeries(Math.max(0, totalRevenue - currentMonthRevenue), totalRevenue),
            sparklineClass: 'stroke-emerald-400',
        },
        {
            title: 'Revenu du mois',
            value: formatCurrency(currentMonthRevenue),
            change: formatPercent(statistique.value.revenue_change_pct),
            changeText: 'vs mois dernier',
            trendIcon: revenueTrend.icon,
            trendClass: revenueTrend.className,
            showTrend: revenueTrend.show,
            icon: Wallet,
            iconClass: 'bg-blue-100 text-blue-600',
            sparkline: buildTrendSeries(statistique.value.previous_month_revenue, currentMonthRevenue),
            sparklineClass: 'stroke-blue-400',
        },
        {
            title: 'Reservations du mois',
            value: formatCount(currentMonthCount),
            change: formatPercent(statistique.value.count_change_pct),
            changeText: 'vs mois dernier',
            trendIcon: countTrend.icon,
            trendClass: countTrend.className,
            showTrend: countTrend.show,
            icon: CalendarCheck,
            iconClass: 'bg-amber-100 text-amber-600',
            sparkline: buildTrendSeries(statistique.value.previous_month_count, currentMonthCount),
            sparklineClass: 'stroke-amber-400',
        },
        {
            title: 'Reservations annulees',
            value: formatCount(totalCanceled),
            change: '',
            changeText: 'au total',
            trendIcon: TrendingDown,
            trendClass: 'text-gray-400',
            showTrend: false,
            icon: XCircle,
            iconClass: 'bg-rose-100 text-rose-600',
            sparkline: buildTrendSeries(Math.max(0, totalCanceled - 1), totalCanceled),
            sparklineClass: 'stroke-rose-400',
        },
    ];
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mes reservations',
        href: '/reservations',
    },
];

</script>

<template>

    <Head title="Mes reservations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Stats cards container -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div v-for="stat in statCards" :key="stat.title"
                    class="rounded-sm border border-gray-100 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div :class="['flex h-10 w-10 items-center justify-center rounded-full', stat.iconClass]">
                                <component :is="stat.icon" class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {{ stat.title }}
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
                            </div>
                        </div>
                        <svg class="h-10 w-28" viewBox="0 0 120 40" fill="none">
                            <polyline :points="sparklinePoints(stat.sparkline)"
                                class="fill-none stroke-2" :class="stat.sparklineClass" stroke-linecap="round"
                                stroke-linejoin="round" stroke-dasharray="2 3" />
                        </svg>
                    </div>
                    <div class="mt-3 flex items-center gap-1 text-xs">
                        <component v-if="stat.showTrend" :is="stat.trendIcon"
                            :class="['h-3 w-3', stat.trendClass]" />
                        <span v-if="stat.change" :class="stat.trendClass">{{ stat.change }}</span>
                        <span class="text-gray-400">{{ stat.changeText }}</span>
                    </div>
                </div>
            </div>

            <DataTable :columns="columns" :data="reservations" search-placeholder="Rechercher une reservation..."
                empty-message="Aucune reservation pour le moment.">
                <template #toolbar="{ table }">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-col gap-1">
                            <h1 class="text-lg font-semibold text-gray-900">Liste des reservations</h1>
                            <p class="text-sm text-gray-500">
                                Vous avez {{ reservations.length }} {{ reservationCountLabel }}
                            </p>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-end lg:w-auto">
                            <div class="relative w-full sm:w-72">
                                <Search
                                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                <FloatingInput
                                    id="reservation-search"
                                    label="Recherche"
                                    label-class="pl-9"
                                    class="w-full pl-9"
                                    :model-value="table.getColumn('ref')?.getFilterValue() as string"
                                    @update:model-value="table.getColumn('ref')?.setFilterValue($event)"
                                />
                            </div>
                            <div class="w-full sm:w-56">
                                <FloatingSelect
                                    id="reservation-status"
                                    label="Statut"
                                    placeholder="Tous les statuts"
                                    :options="statusOptions"
                                    :model-value="(table.getColumn('status')?.getFilterValue() as string) ?? 'all'"
                                    @update:model-value="value => table.getColumn('status')?.setFilterValue(value === 'all' ? undefined : value)"
                                />
                            </div>
                            <DataTableViewOptions :table="table" label="Filtrer" menu-label="Colonnes"
                                button-class="w-full sm:w-auto" />
                            <Button asChild class="w-full sm:w-auto" size="sm">
                                <Link :href="route('search.babysitter')">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Nouvelle reservation
                                </Link>
                            </Button>
                        </div>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>


