<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { type Announcement, type BreadcrumbItem, type Stats, type User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tableau de bord',
        href: '/dashboard',
    },
];

type KpiFormat = 'currency' | 'number';

interface DashboardKpi {
    key: string;
    label: string;
    value: number;
    format: KpiFormat;
    change_pct: number | null;
    period: string;
}

interface DashboardPayload {
    role: string;
    stats: Stats;
    kpis: DashboardKpi[];
}

interface AnnouncementsPayload {
    items: Announcement[];
    suggestions: string[];
}

interface AuthPayload {
    user?: User | null;
}

const page = usePage();
const dashboard = computed(() => page.props.dashboard as DashboardPayload | undefined);
const auth = computed(() => page.props.auth as AuthPayload | undefined);
const announcementsPayload = computed(() => page.props.announcements as AnnouncementsPayload | undefined);

const role = computed(() => dashboard.value?.role ?? 'Parent');
const userName = computed(() => {
    const name = auth.value?.user?.name ?? 'vous';
    const first = name.trim().split(' ')[0];
    return first || 'vous';
});

const stats = computed(() => dashboard.value?.stats);
const isBabysitter = computed(() => role.value === 'Babysitter');
const isAdmin = computed(() => role.value === 'Admin');
const announcementItems = computed(() => announcementsPayload.value?.items ?? []);

const formatAnnouncementDate = (value?: string | null) => {
    if (!value) {
        return '';
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '';
    }
    return new Intl.DateTimeFormat('fr-CA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};

const formatChildLabel = (announcement: Announcement) => {
    const children = (announcement.children ?? []).map((child) => (child?.name ?? '').toString().trim()).filter(Boolean);
    if (children.length) {
        return children.join(', ');
    }
    const parts = [announcement.child_name, announcement.child_age]
        .map((value) => (value ?? '').toString().trim())
        .filter(Boolean);
    return parts.join(' · ');
};

const childInitial = (name?: string | null) => {
    const value = (name ?? '').toString().trim();
    return value ? value.charAt(0).toUpperCase() : '?';
};

const currencyFormatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
});
const numberFormatter = new Intl.NumberFormat('en-US');
const percentFormatter = new Intl.NumberFormat('en-US', { maximumFractionDigits: 1, signDisplay: 'exceptZero' });

const formatCurrency = (value: number | null | undefined) => currencyFormatter.format(Number(value ?? 0));
const formatNumber = (value: number | null | undefined) => numberFormatter.format(Number(value ?? 0));
const formatPercent = (value: number | null | undefined) =>
    value === null || value === undefined ? '' : `${percentFormatter.format(value)}%`;

const welcomeTitle = computed(() => (role.value === 'Admin' ? 'Tableau de bord' : 'Bon retour'));
const welcomeSummary = computed(() => {
    if (role.value === 'Babysitter') {
        return 'Suivez vos missions, vos gains et les annonces qui vous correspondent.';
    }
    if (role.value === 'Admin') {
        return "Vue d'ensemble de l'activite des reservations, revenus et utilisateurs.";
    }
    return 'Suivez vos reservations et vos depenses.';
});

const monthlyLabel = computed(() => {
    if (role.value === 'Babysitter') {
        return 'Gains du mois';
    }
    if (role.value === 'Admin') {
        return 'Revenu du mois';
    }
    return 'Depenses du mois';
});

const monthlyValue = computed(() => formatCurrency(stats.value?.current_month_revenue ?? 0));
const monthlyChange = computed(() => stats.value?.revenue_change_pct ?? null);
const monthlyChangeLabel = computed(() =>
    monthlyChange.value === null ? null : formatPercent(monthlyChange.value)
);
const monthlyTrendUp = computed(() => (monthlyChange.value ?? 0) >= 0);
const monthlyCount = computed(() => formatNumber(stats.value?.current_month_count ?? 0));
const monthlyCountChange = computed(() => stats.value?.count_change_pct ?? null);
const monthlyCountChangeLabel = computed(() =>
    monthlyCountChange.value === null ? null : formatPercent(monthlyCountChange.value)
);
const monthlyCountTrendUp = computed(() => (monthlyCountChange.value ?? 0) >= 0);

const monthlyBars = [
    { label: 'Jan', value: 12 },
    { label: 'Fev', value: 16 },
    { label: 'Mar', value: 20 },
    { label: 'Avr', value: 24 },
    { label: 'Mai', value: 18 },
    { label: 'Juin', value: 15 },
    { label: 'Juil', value: 17 },
    { label: 'Aou', value: 14 },
    { label: 'Sep', value: 11 },
    { label: 'Oct', value: 13 },
    { label: 'Nov', value: 9 },
    { label: 'Dec', value: 7 },
];

const maxMonthly = Math.max(...monthlyBars.map((bar) => bar.value));

const kpiToneClasses = {
    emerald: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
    sky: 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
    amber: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
    rose: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
    lime: 'bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-200',
    cyan: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-200',
    slate: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
};

const kpiMeta: Record<string, { icon: string; tone: keyof typeof kpiToneClasses }> = {
    total_revenue: { icon: 'TR', tone: 'emerald' },
    total_reservations: { icon: 'RS', tone: 'sky' },
    total_spend: { icon: 'SP', tone: 'emerald' },
    total_jobs: { icon: 'JB', tone: 'sky' },
    total_earnings: { icon: 'ER', tone: 'emerald' },
    upcoming_reservations: { icon: 'UP', tone: 'amber' },
    upcoming_jobs: { icon: 'UP', tone: 'amber' },
    canceled_reservations: { icon: 'CN', tone: 'rose' },
    canceled_jobs: { icon: 'CN', tone: 'rose' },
    active_babysitters: { icon: 'BS', tone: 'lime' },
    active_parents: { icon: 'PR', tone: 'cyan' },
};

const kpiLabelMap: Record<string, string> = {
    total_revenue: 'Revenu total',
    total_reservations: 'Reservations totales',
    total_spend: 'Depenses totales',
    total_jobs: 'Missions totales',
    total_earnings: 'Gains totaux',
    upcoming_reservations: 'Reservations a venir',
    upcoming_jobs: 'Missions a venir',
    canceled_reservations: 'Reservations annulees',
    canceled_jobs: 'Missions annulees',
    active_babysitters: 'Babysitters actifs',
    active_parents: 'Parents actifs',
};

const kpiPeriodMap: Record<string, string> = {
    'vs last month': 'vs mois dernier',
    scheduled: 'planifiees',
    'all time': 'au total',
};

const fallbackKpis = computed<DashboardKpi[]>(() => {
    const totalCount = stats.value?.total_count ?? 0;
    const totalRevenue = stats.value?.total_revenue ?? 0;
    const countChange = stats.value?.count_change_pct ?? null;
    const revenueChange = stats.value?.revenue_change_pct ?? null;
    const canceledCount = stats.value?.total_canceled_count ?? 0;

    if (role.value === 'Admin') {
        return [
            {
                key: 'total_revenue',
                label: 'Total Revenue',
                value: totalRevenue,
                format: 'currency',
                change_pct: revenueChange,
                period: 'vs last month',
            },
            {
                key: 'total_reservations',
                label: 'Total Reservations',
                value: totalCount,
                format: 'number',
                change_pct: countChange,
                period: 'vs last month',
            },
            {
                key: 'active_babysitters',
                label: 'Active Babysitters',
                value: 0,
                format: 'number',
                change_pct: null,
                period: 'all time',
            },
            {
                key: 'active_parents',
                label: 'Active Parents',
                value: 0,
                format: 'number',
                change_pct: null,
                period: 'all time',
            },
        ];
    }

    if (role.value === 'Babysitter') {
        return [
            {
                key: 'total_jobs',
                label: 'Total Jobs',
                value: totalCount,
                format: 'number',
                change_pct: countChange,
                period: 'vs last month',
            },
            {
                key: 'total_earnings',
                label: 'Total Earnings',
                value: totalRevenue,
                format: 'currency',
                change_pct: revenueChange,
                period: 'vs last month',
            },
            {
                key: 'upcoming_jobs',
                label: 'Upcoming Jobs',
                value: 0,
                format: 'number',
                change_pct: null,
                period: 'scheduled',
            },
            {
                key: 'canceled_jobs',
                label: 'Canceled Jobs',
                value: canceledCount,
                format: 'number',
                change_pct: null,
                period: 'all time',
            },
        ];
    }

    return [
        {
            key: 'total_reservations',
            label: 'Total Reservations',
            value: totalCount,
            format: 'number',
            change_pct: countChange,
            period: 'vs last month',
        },
        {
            key: 'total_spend',
            label: 'Total Spend',
            value: totalRevenue,
            format: 'currency',
            change_pct: revenueChange,
            period: 'vs last month',
        },
        {
            key: 'upcoming_reservations',
            label: 'Upcoming',
            value: 0,
            format: 'number',
            change_pct: null,
            period: 'scheduled',
        },
        {
            key: 'canceled_reservations',
            label: 'Canceled',
            value: canceledCount,
            format: 'number',
            change_pct: null,
            period: 'all time',
        },
    ];
});

const kpiCards = computed(() => {
    const source = dashboard.value?.kpis;
    const resolved = source && source.length ? source : fallbackKpis.value;

    return resolved.map((kpi, index) => {
        const meta = kpiMeta[kpi.key] ?? { icon: 'KP', tone: 'slate' };
        const changeLabel = kpi.change_pct === null || kpi.change_pct === undefined
            ? null
            : formatPercent(kpi.change_pct);
        const trend = kpi.change_pct === null || kpi.change_pct === undefined
            ? 'neutral'
            : kpi.change_pct >= 0
                ? 'up'
                : 'down';
        const label = kpiLabelMap[kpi.key] ?? kpi.label;
        const period = kpi.period ? (kpiPeriodMap[kpi.period] ?? kpi.period) : '';

        return {
            ...kpi,
            label,
            period,
            icon: meta.icon,
            iconClass: kpiToneClasses[meta.tone] ?? kpiToneClasses.slate,
            changeLabel,
            trend,
            displayValue: kpi.format === 'currency' ? formatCurrency(kpi.value) : formatNumber(kpi.value),
            delay: 200 + index * 70,
        };
    });
});

const platforms = [
    {
        name: 'Garde du soir',
        orders: '18 demandes',
        progress: 52,
        badge: 'Populaire',
        badgeClass: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
        barClass: 'bg-emerald-500',
        iconClass: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
        icon: 'GS',
    },
    {
        name: "Sortie d'ecole",
        orders: '12 demandes',
        progress: 38,
        badge: 'Regulier',
        badgeClass: 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
        barClass: 'bg-sky-500',
        iconClass: 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
        icon: 'SE',
    },
    {
        name: 'Garde de nuit',
        orders: '9 demandes',
        progress: 28,
        badge: 'Urgent',
        badgeClass: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
        barClass: 'bg-amber-500',
        iconClass: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
        icon: 'GN',
    },
    {
        name: 'Garde weekend',
        orders: '14 demandes',
        progress: 34,
        badge: 'En hausse',
        badgeClass: 'bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-200',
        barClass: 'bg-lime-500',
        iconClass: 'bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-200',
        icon: 'GW',
    },
    {
        name: 'Aide aux devoirs',
        orders: '8 demandes',
        progress: 22,
        badge: 'Nouveau',
        badgeClass: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
        barClass: 'bg-rose-500',
        iconClass: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
        icon: 'AD',
    },
];

const countries = [
    { name: 'Montreal', share: 68, code: 'MT', barClass: 'bg-sky-500' },
    { name: 'Laval', share: 44, code: 'LV', barClass: 'bg-amber-500' },
    { name: 'Longueuil', share: 36, code: 'LG', barClass: 'bg-rose-500' },
    { name: 'Brossard', share: 29, code: 'BR', barClass: 'bg-cyan-500' },
    { name: 'Terrebonne', share: 22, code: 'TB', barClass: 'bg-emerald-500' },
];

const products = [
    {
        name: 'Garde du soir',
        category: 'Duree moyenne',
        units: '4h',
        revenue: '$72',
        badge: 'GS',
        badgeClass: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    },
    {
        name: "Sortie d'ecole",
        category: 'Duree moyenne',
        units: '2h',
        revenue: '$36',
        badge: 'SE',
        badgeClass: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
    },
    {
        name: 'Garde de nuit',
        category: 'Duree moyenne',
        units: '8h',
        revenue: '$160',
        badge: 'GN',
        badgeClass: 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
    },
    {
        name: 'Week-end',
        category: 'Duree moyenne',
        units: '5h',
        revenue: '$90',
        badge: 'WE',
        badgeClass: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
    },
];

const customers = [
    {
        name: 'Camille Morel',
        type: 'Parent regulier',
        orders: '12 reservations',
        spend: '$320',
        badge: 'CM',
        badgeClass: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
    },
    {
        name: 'Lucas Perrin',
        type: 'Parent nouveau',
        orders: '6 reservations',
        spend: '$180',
        badge: 'LP',
        badgeClass: 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
    },
    {
        name: 'Sarah Leblanc',
        type: 'Parent fidele',
        orders: '9 reservations',
        spend: '$240',
        badge: 'SL',
        badgeClass: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
    },
    {
        name: 'Nadia Roy',
        type: 'Parent actif',
        orders: '7 reservations',
        spend: '$210',
        badge: 'NR',
        badgeClass: 'bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-200',
    },
];

const quickTeam = [
    { initials: 'MP', class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' },
    { initials: 'RM', class: 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200' },
    { initials: 'SA', class: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200' },
    { initials: 'KT', class: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200' },
];

const orderTrend = computed(() => stats.value?.current_month_trend ?? []);
const chartSize = { width: 120, height: 56, padding: 6 };

const orderTrendPoints = computed(() => {
    const values = orderTrend.value.length ? orderTrend.value : Array.from({ length: 10 }, () => 0);
    const maxValue = Math.max(...values);
    const minValue = Math.min(...values);
    const range = maxValue - minValue || 1;

    return values.map((value, index) => {
        const x = (index / Math.max(values.length - 1, 1)) * (chartSize.width - chartSize.padding * 2) + chartSize.padding;
        const y =
            chartSize.height -
            chartSize.padding -
            ((value - minValue) / range) * (chartSize.height - chartSize.padding * 2);

        return { x, y };
    });
});

const orderTrendLine = computed(() => orderTrendPoints.value.map((point) => `${point.x},${point.y}`).join(' '));

const orderTrendArea = computed(() => {
    const points = orderTrendPoints.value;
    if (!points.length) {
        return '';
    }

    const first = points[0];
    const last = points[points.length - 1];
    const path = points.map((point) => `${point.x},${point.y}`).join(' ');

    return `M ${first.x},${chartSize.height} L ${path} L ${last.x},${chartSize.height} Z`;
});
</script>

<template>
    <Head title="Tableau de bord" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0">
                <div
                    class="absolute -left-20 -top-24 h-72 w-72 rounded-full bg-amber-200/40 blur-3xl dark:bg-amber-500/15"
                />
                <div
                    class="absolute -bottom-24 right-0 h-80 w-80 rounded-full bg-sky-200/40 blur-3xl dark:bg-sky-500/15"
                />
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.8),_transparent_55%)] dark:bg-[radial-gradient(circle_at_top,_rgba(15,23,42,0.6),_transparent_55%)]"
                />
            </div>

            <div class="relative z-10 flex flex-col gap-6 p-4 lg:p-6">
                <section class="grid gap-6 lg:grid-cols-12">
                    <div
                        class="dashboard-card relative flex flex-col gap-5 rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur lg:col-span-4"
                        style="animation-delay: 0ms"
                    >
                        <div class="flex flex-col gap-2">
                            <p class="text-sm font-medium text-muted-foreground">{{ welcomeTitle }}</p>
                            <h1 class="text-2xl font-semibold tracking-tight">
                                {{ userName }}!
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                {{ welcomeSummary }}
                            </p>
                        </div>

                        <div class="mt-2 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-3xl font-semibold">{{ monthlyValue }}</p>
                                <div class="mt-1 flex items-center gap-2 text-sm text-muted-foreground">
                                    <span>{{ monthlyLabel }}</span>
                                    <span
                                        v-if="monthlyChangeLabel"
                                        class="inline-flex items-center gap-1"
                                        :class="monthlyTrendUp ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300'"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            :class="monthlyTrendUp ? '' : 'rotate-180'"
                                        >
                                            <path
                                                d="M5 10a1 1 0 0 1 1-1h6.586l-2.293-2.293a1 1 0 1 1 1.414-1.414l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 1 1-1.414-1.414L12.586 11H6a1 1 0 0 1-1-1Z"
                                            />
                                        </svg>
                                        {{ monthlyChangeLabel }}
                                    </span>
                                </div>
                            </div>
                            <button
                                class="inline-flex items-center justify-center rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition hover:bg-primary/90"
                                type="button"
                            >
                                Voir les reservations
                            </button>
                        </div>

                        <div
                            class="pointer-events-none absolute -bottom-6 right-4 hidden w-40 opacity-90 lg:block"
                        >
                            <svg viewBox="0 0 160 120" fill="none">
                                <circle cx="24" cy="34" r="16" fill="#38BDF8" opacity="0.2" />
                                <circle cx="52" cy="28" r="12" fill="#F59E0B" opacity="0.25" />
                                <circle cx="92" cy="36" r="10" fill="#FB7185" opacity="0.25" />
                                <rect x="18" y="56" width="28" height="40" rx="12" fill="#0F172A" opacity="0.05" />
                                <rect x="50" y="54" width="30" height="44" rx="12" fill="#0F172A" opacity="0.06" />
                                <rect x="86" y="58" width="26" height="38" rx="12" fill="#0F172A" opacity="0.04" />
                                <circle cx="32" cy="60" r="10" fill="#FBBF24" opacity="0.4" />
                                <circle cx="64" cy="58" r="10" fill="#38BDF8" opacity="0.4" />
                                <circle cx="98" cy="62" r="10" fill="#FB7185" opacity="0.4" />
                            </svg>
                        </div>
                    </div>

                    <div
                        class="dashboard-card rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur lg:col-span-5"
                        style="animation-delay: 80ms"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">Resume des reservations</h2>
                                <p class="text-sm text-muted-foreground">Evolution mensuelle des reservations</p>
                            </div>
                            <button
                                class="inline-flex items-center gap-2 rounded-full border border-border/60 bg-background px-3 py-1.5 text-sm font-medium text-muted-foreground transition hover:text-foreground"
                                type="button"
                            >
                                Ce mois
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M5.5 7.5a1 1 0 0 1 1.5-1.34l3 2.6 3-2.6a1 1 0 1 1 1.32 1.5l-3.66 3.2a1 1 0 0 1-1.32 0l-3.84-3.36Z"
                                    />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-6 grid grid-cols-12 gap-3">
                            <div
                                v-for="bar in monthlyBars"
                                :key="bar.label"
                                class="group flex flex-col items-center gap-2 text-xs text-muted-foreground"
                            >
                                <span class="font-medium text-foreground/80">{{ bar.value }} res</span>
                                <div class="flex h-28 w-full items-end justify-center">
                                    <div
                                        class="w-3 rounded-full bg-muted/60"
                                    >
                                        <div
                                            class="w-3 rounded-full bg-gradient-to-t from-emerald-500 to-sky-400 shadow-sm transition-all duration-300 group-hover:scale-110"
                                            :style="{ height: `${Math.max(12, (bar.value / maxMonthly) * 100)}%` }"
                                        />
                                    </div>
                                </div>
                                <span>{{ bar.label }}</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="dashboard-card rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur lg:col-span-3"
                        style="animation-delay: 140ms"
                    >
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Services demandes</h2>
                            <span class="text-xs text-muted-foreground">30 derniers jours</span>
                        </div>

                        <div class="mt-5 flex flex-col gap-4">
                            <div
                                v-for="platform in platforms"
                                :key="platform.name"
                                class="flex items-center gap-3"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-semibold"
                                    :class="platform.iconClass"
                                >
                                    {{ platform.icon }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold">{{ platform.name }}</p>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="platform.badgeClass">
                                            {{ platform.badge }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground">{{ platform.orders }}</p>
                                    <div class="mt-2 h-1.5 w-full rounded-full bg-muted">
                                        <div
                                            class="h-1.5 rounded-full"
                                            :class="platform.barClass"
                                            :style="{ width: `${platform.progress}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="kpi in kpiCards"
                        :key="kpi.key"
                        class="dashboard-card flex flex-col gap-4 rounded-2xl border border-border/60 bg-card/80 p-5 shadow-sm backdrop-blur"
                        :style="{ animationDelay: `${kpi.delay}ms` }"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">{{ kpi.label }}</p>
                                <p class="mt-3 text-2xl font-semibold">{{ kpi.displayValue }}</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl text-xs font-semibold" :class="kpi.iconClass">
                                {{ kpi.icon }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span
                                v-if="kpi.changeLabel"
                                class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium"
                                :class="kpi.trend === 'up'
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200'
                                    : 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200'"
                            >
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    :class="kpi.trend === 'down' ? 'rotate-180' : ''"
                                >
                                    <path
                                        d="M5 10a1 1 0 0 1 1-1h6.586l-2.293-2.293a1 1 0 1 1 1.414-1.414l4 4a1 1 0 0 1 0 1.414l-4 4a1 1 0 1 1-1.414-1.414L12.586 11H6a1 1 0 0 1-1-1Z"
                                    />
                                </svg>
                                {{ kpi.changeLabel }}
                            </span>
                            <span class="text-xs text-muted-foreground">{{ kpi.period }}</span>
                        </div>
                    </div>
                </section>

                <section id="annonces" v-if="isBabysitter && announcementItems.length" class="grid gap-6 lg:grid-cols-12">
                    <div
                        class="dashboard-card rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur lg:col-span-12"
                        style="animation-delay: 360ms"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">
                                    Annonces correspondantes
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Les demandes qui correspondent aux services que vous proposez.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3">
                            <div
                                v-for="announcement in announcementItems"
                                :key="announcement.id"
                                class="flex flex-col gap-3 rounded-xl border border-border/60 bg-background/60 p-4"
                            >
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-semibold text-foreground">
                                                {{ announcement.title }}
                                            </p>
                                            <Badge class="border-transparent bg-sky-100 text-sky-700">
                                                {{ announcement.service }}
                                            </Badge>
                                        </div>
                                        <p v-if="formatChildLabel(announcement)" class="text-xs text-muted-foreground">
                                            Enfant: {{ formatChildLabel(announcement) }}
                                        </p>
                                        <div v-if="announcement.children?.length" class="flex flex-wrap items-center gap-2">
                                            <div
                                                v-for="(child, index) in announcement.children"
                                                :key="child.id ?? `${child.name ?? 'child'}-${index}`"
                                                class="flex items-center gap-2 rounded-full bg-muted/60 pr-2"
                                            >
                                                <div class="flex h-7 w-7 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-[10px] font-semibold text-slate-600">
                                                    <img
                                                        v-if="child.photo"
                                                        :src="child.photo"
                                                        :alt="child.name ?? 'Enfant'"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <span v-else>{{ childInitial(child.name) }}</span>
                                                </div>
                                                <span class="text-xs text-muted-foreground">{{ child.name || 'Enfant' }}</span>
                                            </div>
                                        </div>
                                        <p v-if="announcement.child_notes" class="text-sm text-muted-foreground">
                                            {{ announcement.child_notes }}
                                        </p>
                                        <p v-if="announcement.description" class="text-sm text-muted-foreground">
                                            {{ announcement.description }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-2 text-xs text-muted-foreground sm:items-end">
                                        <div class="flex flex-col gap-1">
                                            <span v-if="announcement.parent">
                                                Parent: {{ announcement.parent?.name }}
                                                <span v-if="announcement.parent?.city">- {{ announcement.parent.city }}</span>
                                            </span>
                                            <span v-if="announcement.created_at">
                                                {{ formatAnnouncementDate(announcement.created_at) }}
                                            </span>
                                        </div>
                                        <Button variant="outline" size="sm" as-child>
                                            <Link :href="route('announcements.show', { announcement: announcement.id })">
                                                Voir details
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-6 lg:grid-cols-12">
                    <div class="lg:col-span-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <div
                            class="dashboard-card rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur"
                            style="animation-delay: 460ms"
                        >
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold">Zones actives</h2>
                                <button class="text-sm font-medium text-muted-foreground transition hover:text-foreground" type="button">
                                    Tout voir
                                </button>
                            </div>
                            <div class="mt-5 flex flex-col gap-4">
                                <div v-for="country in countries" :key="country.name" class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-muted text-xs font-semibold">
                                        {{ country.code }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between text-sm font-medium">
                                            <span>{{ country.name }}</span>
                                            <span class="text-muted-foreground">{{ country.share }}%</span>
                                        </div>
                                        <div class="mt-2 h-1.5 w-full rounded-full bg-muted">
                                            <div
                                                class="h-1.5 rounded-full"
                                                :class="country.barClass"
                                                :style="{ width: `${country.share}%` }"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="dashboard-card rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur"
                            style="animation-delay: 520ms"
                        >
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold">Tarifs moyens</h2>
                                <span class="text-xs text-muted-foreground">Cette semaine</span>
                            </div>
                            <div class="mt-5 flex flex-col gap-4">
                                <div v-for="product in products" :key="product.name" class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl text-xs font-semibold"
                                        :class="product.badgeClass"
                                    >
                                        {{ product.badge }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold">{{ product.name }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ product.category }} - {{ product.units }}
                                        </p>
                                    </div>
                                    <span class="text-sm font-semibold">{{ product.revenue }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="isAdmin"
                            class="dashboard-card rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur"
                            style="animation-delay: 580ms"
                        >
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold">Parents actifs</h2>
                                <span class="text-xs text-muted-foreground">Ce mois-ci</span>
                            </div>
                            <div class="mt-5 flex flex-col gap-4">
                                <div v-for="customer in customers" :key="customer.name" class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full text-xs font-semibold"
                                        :class="customer.badgeClass"
                                    >
                                        {{ customer.badge }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold">{{ customer.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ customer.type }} - {{ customer.orders }}</p>
                                    </div>
                                    <span class="text-sm font-semibold">{{ customer.spend }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 grid gap-6">
                        <div
                            class="dashboard-card rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur"
                            style="animation-delay: 640ms"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold">Reservations du mois</h2>
                                    <p class="text-sm text-muted-foreground">Reservations ce mois</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-semibold">{{ monthlyCount }}</p>
                                    <p
                                        v-if="monthlyCountChangeLabel"
                                        class="text-xs"
                                        :class="monthlyCountTrendUp ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300'"
                                    >
                                        {{ monthlyCountChangeLabel }} vs mois dernier
                                    </p>
                                    <p v-else class="text-xs text-muted-foreground">Pas de comparaison</p>
                                </div>
                            </div>
                            <div class="mt-6 h-40 w-full rounded-2xl bg-muted/50 p-4">
                                <svg :viewBox="`0 0 ${chartSize.width} ${chartSize.height}`" class="h-full w-full">
                                    <defs>
                                        <linearGradient id="orderTrend" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.35" />
                                            <stop offset="100%" stop-color="#38BDF8" stop-opacity="0.05" />
                                        </linearGradient>
                                    </defs>
                                    <path :d="orderTrendArea" fill="url(#orderTrend)" />
                                    <polyline
                                        :points="orderTrendLine"
                                        fill="none"
                                        stroke="#0EA5E9"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                        </div>

                        <div
                            class="dashboard-card rounded-2xl border border-border/60 bg-card/80 p-6 shadow-sm backdrop-blur"
                            style="animation-delay: 700ms"
                        >
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold">Contacts rapides</h2>
                                <button class="text-sm text-muted-foreground transition hover:text-foreground" type="button">
                                    Voir tout
                                </button>
                            </div>
                            <div class="mt-5 flex items-center gap-3">
                                <div
                                    v-for="member in quickTeam"
                                    :key="member.initials"
                                    class="flex h-11 w-11 items-center justify-center rounded-full text-xs font-semibold"
                                    :class="member.class"
                                >
                                    {{ member.initials }}
                                </div>
                                <button
                                    class="flex h-11 w-11 items-center justify-center rounded-full border border-dashed border-border text-sm font-semibold text-muted-foreground transition hover:text-foreground"
                                    type="button"
                                >
                                    +
                                </button>
                            </div>
                            <div class="mt-6 rounded-2xl border border-border/60 bg-background/60 p-4 text-sm text-muted-foreground">
                                Besoin d'un coup de main? Ajoutez des contacts pour partager vos reservations.
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

    </AppLayout>
</template>

<style scoped>
.dashboard-card {
    animation: dashboard-rise 0.6s ease-out both;
}

@keyframes dashboard-rise {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .dashboard-card {
        animation: none;
    }
}
</style>
