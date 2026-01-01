<script setup lang="ts">
import { computed } from 'vue';
import { getReservationColumns } from '@/components/Reservation/columns';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Reservation, type SharedData, type Stats } from '@/types';
import { Head, usePage, Link } from '@inertiajs/vue3';
import DataTable from '@/components/Reservation/data-table.vue';
import {
    CalendarCheck,
    ClipboardList,
    DollarSign,
    Plus,
    TrendingDown,
    TrendingUp,
    Users,
    Wallet,
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

const role = computed(() => (page.props.auth?.role ?? 'Parent').toString().toLowerCase());
const isBabysitter = computed(() => role.value === 'babysitter');
const tableColumns = computed(() => getReservationColumns(role.value));

const statusOptions = [
    { value: 'all', label: 'Tous les statuts' },
    { value: 'pending', label: 'En attente' },
    { value: 'confirmed', label: 'Confirmee' },
    { value: 'canceled', label: 'Annulee' },
];

const statCards = computed(() => {
    const currentMonthRevenue = toNumber(statistique.value.current_month_revenue);
    const totalRevenue = toNumber(statistique.value.total_revenue);
    const pendingCount = toNumber(statistique.value.pending_count);
    const confirmedCount = toNumber(statistique.value.confirmed_count);
    const upcomingCount = toNumber(statistique.value.upcoming_count);
    const uniqueBabysitters = toNumber(statistique.value.unique_babysitters_count);
    const uniqueParents = toNumber(statistique.value.unique_parents_count);
    const revenueChange = statistique.value.revenue_change_pct;
    const revenueTrend = getTrendMeta(revenueChange);
    const revenueChangeLabel = revenueChange === null ? '' : formatPercent(revenueChange);

    if (isBabysitter.value) {
        return [
            {
                title: 'Reservations confirmees',
                value: formatCount(confirmedCount),
                change: '',
                changeText: 'au total',
                trendIcon: TrendingUp,
                trendClass: 'text-gray-400',
                showTrend: false,
                icon: CalendarCheck,
                iconClass: 'bg-amber-100 text-amber-600',
                sparkline: buildTrendSeries(Math.max(0, confirmedCount - 1), confirmedCount),
                sparklineClass: 'stroke-amber-400',
            },
            {
                title: 'En attente',
                value: formatCount(pendingCount),
                change: '',
                changeText: 'en cours',
                trendIcon: TrendingUp,
                trendClass: 'text-gray-400',
                showTrend: false,
                icon: ClipboardList,
                iconClass: 'bg-violet-100 text-violet-600',
                sparkline: buildTrendSeries(Math.max(0, pendingCount - 1), pendingCount),
                sparklineClass: 'stroke-violet-400',
            },
            {
                title: 'Revenu du mois',
                value: formatCurrency(currentMonthRevenue),
                change: revenueChangeLabel,
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
                title: 'Parents differents',
                value: formatCount(uniqueParents),
                change: '',
                changeText: 'au total',
                trendIcon: TrendingUp,
                trendClass: 'text-gray-400',
                showTrend: false,
                icon: Users,
                iconClass: 'bg-sky-100 text-sky-600',
                sparkline: buildTrendSeries(Math.max(0, uniqueParents - 1), uniqueParents),
                sparklineClass: 'stroke-sky-400',
            },
        ];
    }

    return [
        {
            title: 'Reservations a venir',
            value: formatCount(upcomingCount),
            change: '',
            changeText: 'planifiees',
            trendIcon: TrendingUp,
            trendClass: 'text-gray-400',
            showTrend: false,
            icon: CalendarCheck,
            iconClass: 'bg-amber-100 text-amber-600',
            sparkline: buildTrendSeries(Math.max(0, upcomingCount - 1), upcomingCount),
            sparklineClass: 'stroke-amber-400',
        },
        {
            title: 'En attente',
            value: formatCount(pendingCount),
            change: '',
            changeText: 'en cours',
            trendIcon: TrendingUp,
            trendClass: 'text-gray-400',
            showTrend: false,
            icon: ClipboardList,
            iconClass: 'bg-violet-100 text-violet-600',
            sparkline: buildTrendSeries(Math.max(0, pendingCount - 1), pendingCount),
            sparklineClass: 'stroke-violet-400',
        },
        {
            title: 'Depense du mois',
            value: formatCurrency(currentMonthRevenue),
            change: revenueChangeLabel,
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
            title: 'Depense totale',
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
            title: 'Babysitters differents',
            value: formatCount(uniqueBabysitters),
            change: '',
            changeText: 'au total',
            trendIcon: TrendingUp,
            trendClass: 'text-gray-400',
            showTrend: false,
            icon: Users,
            iconClass: 'bg-sky-100 text-sky-600',
            sparkline: buildTrendSeries(Math.max(0, uniqueBabysitters - 1), uniqueBabysitters),
            sparklineClass: 'stroke-sky-400',
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

            <DataTable :columns="tableColumns" :data="reservations" search-placeholder="Rechercher une reservation..."
                empty-message="Aucune reservation pour le moment.">
                <template #toolbar-filters="{ table }">
                    <Select
                        :model-value="(table.getColumn('status')?.getFilterValue() as string) ?? 'all'"
                        @update:model-value="value => table.getColumn('status')?.setFilterValue(value === 'all' ? undefined : value)"
                    >
                        <SelectTrigger class="h-9 w-full sm:w-48">
                            <SelectValue placeholder="Tous les statuts" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </template>
                <template #toolbar-actions="{ table }">
                    <Button variant="outline" class="h-9 w-full sm:w-auto"
                        @click="table.resetColumnFilters()">
                        Effacer
                    </Button>
                    <Button asChild size="sm" class="h-9 w-full bg-emerald-500 text-white hover:bg-emerald-600 sm:w-auto">
                        <Link :href="route('search.babysitter')">
                            <Plus class="mr-2 h-4 w-4" />
                            Nouvelle reservation
                        </Link>
                    </Button>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>


