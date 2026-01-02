<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import AppLayout from '@/layouts/AppLayout.vue';
import FloatingInput from '@/components/FloatingInput.vue';
import FloatingTextarea from '@/components/FloatingTextarea.vue';
import InputError from '@/components/InputError.vue';
import DataTable from '@/components/Reservation/data-table.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { type BreadcrumbItem } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import {
    CalendarCheck,
    ClipboardList,
    MoreHorizontal,
    Plus,
    Star,
    TrendingUp,
} from 'lucide-vue-next';

interface ServiceItem {
    id: number;
    name: string;
    description?: string | null;
    price: number;
    bookings_count: number;
}

interface CatalogItem {
    id: number;
    name: string;
    description?: string | null;
}

interface Kpis {
    total_services: number;
    total_bookings: number;
    top_service_name?: string | null;
    top_service_count?: number;
}

const props = defineProps<{
    services: ServiceItem[];
    catalog: CatalogItem[];
    kpis: Kpis;
}>();

const { t } = useI18n();
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('settings.services.title'),
        href: '/settings/services',
    },
]);

const editingId = ref<number | null>(null);
const form = useForm({
    name: '',
    description: '',
    price: '',
});
const isDialogOpen = ref(false);

const currencyFormatter = new Intl.NumberFormat('fr-CA', { style: 'currency', currency: 'CAD' });
const numberFormatter = new Intl.NumberFormat('fr-CA');

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

const formatPrice = (value: number | string | null | undefined) => currencyFormatter.format(toNumber(value));
const formatCount = (value: number | string | null | undefined) => numberFormatter.format(toNumber(value));

const topServiceLabel = computed(() => {
    if (!props.kpis?.top_service_name) {
        return t('settings.services.kpis.none');
    }
    const count = props.kpis.top_service_count ?? 0;
    return t('settings.services.kpis.top_service', { name: props.kpis.top_service_name, count });
});

const serviceCountLabel = computed(() => t('settings.services.kpis.count', { count: props.services.length }));

const dialogTitle = computed(() =>
    editingId.value ? t('settings.services.dialog.edit_title') : t('settings.services.dialog.create_title')
);

const dialogDescription = computed(() =>
    editingId.value
        ? t('settings.services.dialog.edit_description')
        : t('settings.services.dialog.create_description')
);

const submitLabel = computed(() =>
    editingId.value ? t('common.actions.update') : t('settings.services.dialog.submit_create')
);

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

const statCards = computed(() => {
    const totalServices = toNumber(props.kpis?.total_services);
    const totalBookings = toNumber(props.kpis?.total_bookings);
    const topCount = toNumber(props.kpis?.top_service_count);

    return [
        {
            title: t('settings.services.kpis.offered'),
            value: formatCount(totalServices),
            change: '',
            changeText: t('settings.services.kpis.total_suffix'),
            trendIcon: TrendingUp,
            trendClass: 'text-muted-foreground/70',
            showTrend: false,
            icon: ClipboardList,
            iconClass: 'bg-violet-100 text-violet-600',
            sparkline: buildTrendSeries(Math.max(0, totalServices - 1), totalServices),
            sparklineClass: 'stroke-violet-400',
        },
        {
            title: t('settings.services.kpis.booked'),
            value: formatCount(totalBookings),
            change: '',
            changeText: t('settings.services.kpis.total_suffix'),
            trendIcon: TrendingUp,
            trendClass: 'text-muted-foreground/70',
            showTrend: false,
            icon: CalendarCheck,
            iconClass: 'bg-amber-100 text-amber-600',
            sparkline: buildTrendSeries(Math.max(0, totalBookings - 1), totalBookings),
            sparklineClass: 'stroke-amber-400',
        },
        {
            title: t('settings.services.kpis.top_requested'),
            value: topServiceLabel.value,
            valueClass: 'text-lg',
            change: '',
            changeText: t('settings.services.kpis.top_suffix'),
            trendIcon: TrendingUp,
            trendClass: 'text-muted-foreground/70',
            showTrend: false,
            icon: Star,
            iconClass: 'bg-rose-100 text-rose-600',
            sparkline: buildTrendSeries(Math.max(0, topCount - 1), topCount),
            sparklineClass: 'stroke-rose-400',
        },
    ];
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    editingId.value = null;
};

const normalizeName = (value: string) => value.trim().toLowerCase();

const existingServiceNames = computed(() => {
    return new Set(props.services.map((service) => normalizeName(service.name)));
});

const catalogItems = computed(() =>
    props.catalog.map((item) => ({
        ...item,
        is_added: existingServiceNames.value.has(normalizeName(item.name)),
    }))
);

const openCreateDialog = () => {
    resetForm();
    isDialogOpen.value = true;
};

const openCatalogDialog = (service: CatalogItem) => {
    resetForm();
    form.name = service.name;
    form.description = service.description ?? '';
    form.price = '0';
    isDialogOpen.value = true;
};

const startEdit = (service: ServiceItem) => {
    editingId.value = service.id;
    form.name = service.name;
    form.description = service.description ?? '';
    form.price = `${service.price ?? 0}`;
    isDialogOpen.value = true;
};

const submitForm = () => {
    const onSuccess = () => {
        resetForm();
        isDialogOpen.value = false;
    };
    if (editingId.value) {
        form.patch(route('services.update', { service: editingId.value }), {
            preserveScroll: true,
            onSuccess,
        });
        return;
    }
    form.post(route('services.store'), {
        preserveScroll: true,
        onSuccess,
    });
};

const deleteService = (service: ServiceItem) => {
    if (!confirm(t('settings.services.confirm_delete', { name: service.name }))) {
        return;
    }
    form.delete(route('services.destroy', { service: service.id }), {
        preserveScroll: true,
        onSuccess: resetForm,
    });
};

const headerClass = 'text-xs font-semibold uppercase tracking-wide text-muted-foreground';

const columns: ColumnDef<ServiceItem>[] = [
    {
        accessorKey: 'name',
        header: () => h('span', { class: headerClass }, t('common.labels.service')),
        cell: ({ row }) => {
            const service = row.original;
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'text-sm font-medium text-foreground' }, service.name),
                service.description
                    ? h('span', { class: 'text-xs text-muted-foreground' }, service.description)
                    : null,
            ]);
        },
    },
    {
        accessorKey: 'price',
        header: () => h('div', { class: ['text-right', headerClass] }, t('common.labels.price')),
        cell: ({ row }) =>
            h('div', { class: 'text-right text-sm font-semibold text-foreground' }, formatPrice(row.original.price)),
    },
    {
        accessorKey: 'bookings_count',
        header: () => h('div', { class: ['text-right', headerClass] }, t('settings.services.columns.requests')),
        cell: ({ row }) =>
            h('div', { class: 'text-right text-sm text-muted-foreground' }, formatCount(row.original.bookings_count)),
    },
    {
        id: 'actions',
        enableHiding: false,
        header: () => h('div', { class: ['text-right', headerClass] }, t('common.table.actions')),
        cell: ({ row }) =>
            h('div', { class: 'flex justify-end' }, [
                h(
                    DropdownMenu,
                    null,
                    {
                        default: () => [
                            h(
                                DropdownMenuTrigger,
                                { asChild: true },
                                {
                                    default: () =>
                                        h(
                                            Button,
                                            {
                                                variant: 'ghost',
                                                size: 'icon',
                                                class: 'h-8 w-8',
                                            },
                                            {
                                                default: () => h(MoreHorizontal, { class: 'h-4 w-4' }),
                                            }
                                        ),
                                }
                            ),
                            h(
                                DropdownMenuContent,
                                { align: 'end', class: 'w-40' },
                                {
                                    default: () => [
                                        h(
                                            DropdownMenuItem,
                                            { onSelect: () => startEdit(row.original) },
                                            { default: () => t('common.actions.edit') }
                                        ),
                                        h(
                                            DropdownMenuItem,
                                            {
                                                onSelect: () => deleteService(row.original),
                                            },
                                            { default: () => t('common.actions.delete') }
                                        ),
                                    ],
                                }
                            ),
                        ],
                    }
                ),
            ]),
    },
];

const catalogColumns: ColumnDef<CatalogItem & { is_added: boolean }>[] = [
    {
        accessorKey: 'name',
        header: () => h('span', { class: headerClass }, t('common.labels.service')),
        cell: ({ row }) => {
            const service = row.original;
            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'text-sm font-medium text-foreground' }, service.name),
                service.description
                    ? h('span', { class: 'text-xs text-muted-foreground' }, service.description)
                    : null,
            ]);
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        header: () => h('div', { class: ['text-right', headerClass] }, t('common.table.actions')),
        cell: ({ row }) => {
            const service = row.original;
            const isAdded = service.is_added;
            return h('div', { class: 'flex justify-end' }, [
                h(
                    Button,
                    {
                        variant: isAdded ? 'outline' : 'default',
                        size: 'sm',
                        disabled: isAdded,
                        onClick: () => openCatalogDialog(service),
                    },
                    { default: () => (isAdded ? t('settings.services.catalog.added') : t('common.actions.add')) }
                ),
            ]);
        },
    },
];
</script>

<template>

    <Head :title="$t('settings.services.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="stat in statCards" :key="stat.title"
                    class="rounded-sm border border-border bg-card p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div :class="['flex h-10 w-10 items-center justify-center rounded-full', stat.iconClass]">
                                <component :is="stat.icon" class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    {{ stat.title }}
                                </p>
                                <p :class="['font-semibold text-foreground', stat.valueClass ?? 'text-2xl']">
                                    {{ stat.value }}
                                </p>
                            </div>
                        </div>
                        <svg class="h-10 w-28" viewBox="0 0 120 40" fill="none">
                            <polyline :points="sparklinePoints(stat.sparkline)" class="fill-none stroke-2"
                                :class="stat.sparklineClass" stroke-linecap="round" stroke-linejoin="round"
                                stroke-dasharray="2 3" />
                        </svg>
                    </div>
                    <div class="mt-3 flex items-center gap-1 text-xs">
                        <component v-if="stat.showTrend" :is="stat.trendIcon" :class="['h-3 w-3', stat.trendClass]" />
                        <span v-if="stat.change" :class="stat.trendClass">{{ stat.change }}</span>
                        <span class="text-muted-foreground/70">{{ stat.changeText }}</span>
                    </div>
                </div>
            </div>

            <Dialog v-model:open="isDialogOpen">
                <DialogContent class="rounded-sm sm:max-w-xl">
                    <DialogHeader class="border-b border-border pb-3">
                        <DialogTitle class="text-lg font-semibold text-foreground">
                            {{ dialogTitle }}
                        </DialogTitle>
                        <DialogDescription class="text-sm text-muted-foreground">
                            {{ dialogDescription }}
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-2">
                            <FloatingInput id="service-name" :label="$t('settings.services.form.name_label')" v-model="form.name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <FloatingTextarea id="service-description" :label="$t('common.labels.description')" rows="3"
                                v-model="form.description" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div class="space-y-2">
                            <FloatingInput id="service-price" :label="$t('settings.services.form.price_label')" type="number" min="0" step="0.01"
                                v-model="form.price" />
                            <InputError :message="form.errors.price" />
                        </div>
                        <DialogFooter class="mt-4">
                            <DialogClose as-child>
                                <Button type="button" variant="outline" @click="resetForm">
                                    {{ $t('common.actions.cancel') }}
                                </Button>
                            </DialogClose>
                            <Button type="submit" :disabled="form.processing">
                                {{ submitLabel }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <div class="space-y-3">
                <div class="flex flex-col gap-1">
                    <h2 class="text-base font-semibold text-foreground">{{ $t('settings.services.catalog.title') }}</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ $t('settings.services.catalog.description') }}
                    </p>
                </div>
                <DataTable :columns="catalogColumns" :data="catalogItems" search-column="name"
                    :search-placeholder="$t('settings.services.catalog.search')"
                    :empty-message="$t('settings.services.catalog.empty')" />
            </div>

            <DataTable :columns="columns" :data="props.services" search-column="name"
                :search-placeholder="$t('settings.services.table.search')" :empty-message="$t('settings.services.table.empty')">
                <template #toolbar-actions="{ table }">
                    <Button variant="outline" class="h-9 w-full sm:w-auto" @click="table.resetColumnFilters()">
                        {{ $t('common.actions.clear') }}
                    </Button>
                    <Button class="h-9 w-full sm:w-auto" size="sm" @click="openCreateDialog">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ $t('settings.services.actions.new') }}
                    </Button>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
