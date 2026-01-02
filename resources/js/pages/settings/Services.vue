<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Services',
        href: '/settings/services',
    },
];

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
        return 'Aucun service';
    }
    const count = props.kpis.top_service_count ?? 0;
    return `${props.kpis.top_service_name} (${count})`;
});

const serviceCountLabel = computed(() =>
    props.services.length === 1 ? 'service' : 'services'
);

const dialogTitle = computed(() =>
    editingId.value ? 'Modifier le service' : 'Ajouter un service'
);

const dialogDescription = computed(() =>
    editingId.value
        ? 'Mettez a jour les informations de votre service.'
        : 'Ajoutez un service que vous proposez.'
);

const submitLabel = computed(() =>
    editingId.value ? 'Mettre a jour' : 'Ajouter le service'
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
            title: 'Services proposes',
            value: formatCount(totalServices),
            change: '',
            changeText: 'au total',
            trendIcon: TrendingUp,
            trendClass: 'text-muted-foreground/70',
            showTrend: false,
            icon: ClipboardList,
            iconClass: 'bg-violet-100 text-violet-600',
            sparkline: buildTrendSeries(Math.max(0, totalServices - 1), totalServices),
            sparklineClass: 'stroke-violet-400',
        },
        {
            title: 'Services reserves',
            value: formatCount(totalBookings),
            change: '',
            changeText: 'au total',
            trendIcon: TrendingUp,
            trendClass: 'text-muted-foreground/70',
            showTrend: false,
            icon: CalendarCheck,
            iconClass: 'bg-amber-100 text-amber-600',
            sparkline: buildTrendSeries(Math.max(0, totalBookings - 1), totalBookings),
            sparklineClass: 'stroke-amber-400',
        },
        {
            title: 'Plus sollicite',
            value: topServiceLabel.value,
            valueClass: 'text-lg',
            change: '',
            changeText: 'le plus demande',
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
    if (!confirm(`Supprimer le service "${service.name}" ?`)) {
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
        header: () => h('span', { class: headerClass }, 'Service'),
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
        header: () => h('div', { class: ['text-right', headerClass] }, 'Prix'),
        cell: ({ row }) =>
            h('div', { class: 'text-right text-sm font-semibold text-foreground' }, formatPrice(row.original.price)),
    },
    {
        accessorKey: 'bookings_count',
        header: () => h('div', { class: ['text-right', headerClass] }, 'Demandes'),
        cell: ({ row }) =>
            h('div', { class: 'text-right text-sm text-muted-foreground' }, formatCount(row.original.bookings_count)),
    },
    {
        id: 'actions',
        enableHiding: false,
        header: () => h('div', { class: ['text-right', headerClass] }, 'Actions'),
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
                                            { default: () => 'Modifier' }
                                        ),
                                        h(
                                            DropdownMenuItem,
                                            {
                                                onSelect: () => deleteService(row.original),
                                            },
                                            { default: () => 'Supprimer' }
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
        header: () => h('span', { class: headerClass }, 'Service'),
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
        header: () => h('div', { class: ['text-right', headerClass] }, 'Actions'),
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
                    { default: () => (isAdded ? 'Ajoute' : 'Ajouter') }
                ),
            ]);
        },
    },
];
</script>

<template>

    <Head title="Services" />

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
                            <FloatingInput id="service-name" label="Nom du service" v-model="form.name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <FloatingTextarea id="service-description" label="Description" rows="3"
                                v-model="form.description" />
                            <InputError :message="form.errors.description" />
                        </div>
                        <div class="space-y-2">
                            <FloatingInput id="service-price" label="Prix (CAD)" type="number" min="0" step="0.01"
                                v-model="form.price" />
                            <InputError :message="form.errors.price" />
                        </div>
                        <DialogFooter class="mt-4">
                            <DialogClose as-child>
                                <Button type="button" variant="outline" @click="resetForm">
                                    Annuler
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
                    <h2 class="text-base font-semibold text-foreground">Catalogue de services</h2>
                    <p class="text-sm text-muted-foreground">
                        Selectionnez un service connu puis ajustez votre prix, ou creez votre propre service.
                    </p>
                </div>
                <DataTable :columns="catalogColumns" :data="catalogItems" search-column="name"
                    search-placeholder="Rechercher un service du catalogue..."
                    empty-message="Aucun service catalogue disponible." />
            </div>

            <DataTable :columns="columns" :data="props.services" search-column="name"
                search-placeholder="Rechercher un service..." empty-message="Aucun service ajoute pour le moment.">
                <template #toolbar-actions="{ table }">
                    <Button variant="outline" class="h-9 w-full sm:w-auto" @click="table.resetColumnFilters()">
                        Effacer
                    </Button>
                    <Button class="h-9 w-full sm:w-auto" size="sm" @click="openCreateDialog">
                        <Plus class="mr-2 h-4 w-4" />
                        Nouveau service
                    </Button>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
