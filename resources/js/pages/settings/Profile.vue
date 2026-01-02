<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import FloatingInput from '@/components/FloatingInput.vue';
import FloatingTextarea from '@/components/FloatingTextarea.vue';
import FloatingSelect from '@/components/FloatingSelect.vue';
import AddressForm from '@/components/AddressForm.vue';
import MediaUploadForm from '@/components/MediaUploadForm.vue';
import MediaScrollingHorizontal from '@/components/MediaScrollingHorizontal.vue';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User, type ParentProfile, type BabysitterProfile, type Address, type MediaItem } from '@/types';
import { CHILD_DEFAULT_PHOTOS, resolveChildPhoto } from '@/lib/childPhotos';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    role?: string | null;
    parentProfile?: ParentProfile | null;
    babysitterProfile?: BabysitterProfile | null;
    address?: Address | null;
    media?: MediaItem[];
}

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => ([
    {
        title: t('settings.profile.breadcrumb'),
        href: '/settings/profile',
    },
]));

const page = usePage<SharedData & Props>();
const user = page.props.auth.user as User;

const resolvedRole = computed(() => {
    const direct = props.role ?? page.props.auth?.role ?? '';
    if (typeof direct === 'string' && direct.length > 0) {
        return direct.toLowerCase();
    }
    return 'parent';
});

const isBabysitter = computed(() => resolvedRole.value === 'babysitter');
const isSuperAdmin = computed(() => resolvedRole.value === 'superadmin' || resolvedRole.value === 'admin');
const profile = computed(() => {
    if (isSuperAdmin.value) {
        return null;
    }
    return (isBabysitter.value ? props.babysitterProfile : props.parentProfile) ?? null;
});
const profileSettings = computed(() => (profile.value?.settings ?? {}) as Record<string, any>);
const mediaItems = computed(() => props.media ?? []);

const paymentOptions = computed(() => ([
    { value: 'per_task', label: t('common.payment.per_task') },
    { value: 'daily', label: t('common.payment.daily') },
    { value: 'weekly', label: t('common.payment.weekly') },
    { value: 'biweekly', label: t('common.payment.biweekly') },
    { value: 'monthly', label: t('common.payment.monthly') },
]));

const accountForm = useForm({
    name: user.name,
    email: user.email,
});

const submitAccount = () => {
    accountForm.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

const emptyChild = () => ({
    name: '',
    age: '',
    allergies: '',
    details: '',
    photo: '',
});

const buildInitialChildren = () => {
    const existing = profileSettings.value?.children;
    if (Array.isArray(existing) && existing.length > 0) {
        return existing.map((child) => ({
            name: child?.name ?? '',
            age: child?.age ?? '',
            allergies: child?.allergies ?? '',
            details: child?.details ?? '',
            photo: child?.photo ?? '',
        }));
    }

    const countRawValue = profileSettings.value?.children_count;
    const countRaw = Number(countRawValue ?? 0);
    const agesRaw = typeof profileSettings.value?.children_ages === 'string'
        ? profileSettings.value.children_ages
        : '';
    const ages = agesRaw
        .split(',')
        .map((value) => value.trim())
        .filter(Boolean);
    const hasLegacy = (countRawValue !== null && countRawValue !== undefined && countRawValue !== '')
        || ages.length > 0;
    if (!hasLegacy) {
        return [];
    }

    const fallbackCount = Number.isFinite(countRaw) && countRaw > 0 ? countRaw : ages.length;
    const count = fallbackCount > 0 ? Math.min(fallbackCount, 10) : 1;

    return Array.from({ length: count }, (_, index) => ({
        name: '',
        age: ages[index] ?? '',
        allergies: '',
        details: '',
        photo: '',
    }));
};

const initialChildren = buildInitialChildren();

const detailsForm = useForm<any>(
    isSuperAdmin.value
        ? {
            address: {
                street: '',
                city: '',
                province: '',
                country: '',
                postal_code: '',
                latitude: '',
                longitude: '',
            },
            children: [],
        }
        : isBabysitter.value
            ? {
                first_name: profile.value?.first_name ?? '',
                last_name: profile.value?.last_name ?? '',
                phone: profile.value?.phone ?? '',
                birthdate: profile.value?.birthdate ?? '',
                bio: profile.value?.bio ?? '',
                experience: profile.value?.experience ?? '',
                price_per_hour: profile.value?.price_per_hour ?? 0,
                payment_frequency: profile.value?.payment_frequency ?? 'per_task',
                services: profileSettings.value?.services ?? '',
                availability: profileSettings.value?.availability ?? '',
                availability_notes: profileSettings.value?.availability_notes ?? '',
                address: props.address
                    ? { ...props.address }
                    : {
                        street: '',
                        city: '',
                        province: '',
                        country: '',
                        postal_code: '',
                        latitude: '',
                        longitude: '',
                    },
            }
            : {
                first_name: profile.value?.first_name ?? '',
                last_name: profile.value?.last_name ?? '',
                phone: profile.value?.phone ?? '',
                birthdate: profile.value?.birthdate ?? '',
                preferences: profileSettings.value?.preferences ?? '',
                availability: profileSettings.value?.availability ?? '',
                availability_notes: profileSettings.value?.availability_notes ?? '',
                children: initialChildren,
                address: props.address
                    ? { ...props.address }
                    : {
                        street: '',
                        city: '',
                        province: '',
                        country: '',
                        postal_code: '',
                        latitude: '',
                        longitude: '',
                    },
            }
);

const addressModel = computed({
    get: () => ({
        street: detailsForm.address.street ?? '',
        city: detailsForm.address.city ?? '',
        province: detailsForm.address.province ?? detailsForm.address.state ?? '',
        postal_code: detailsForm.address.postal_code ?? '',
        country: detailsForm.address.country ?? '',
        latitude: detailsForm.address.latitude ?? '',
        longitude: detailsForm.address.longitude ?? '',
    }),
    set: (value) => {
        detailsForm.address.street = value?.street ?? '';
        detailsForm.address.city = value?.city ?? '';
        detailsForm.address.province = value?.province ?? '';
        detailsForm.address.postal_code = value?.postal_code ?? '';
        detailsForm.address.country = value?.country ?? '';
        detailsForm.address.latitude = value?.latitude ?? '';
        detailsForm.address.longitude = value?.longitude ?? '';
    },
});

const submitDetails = () => {
    if (isSuperAdmin.value) {
        return;
    }
    const routeName = isBabysitter.value ? 'babysitter.profile.update' : 'parent.profile.update';
    detailsForm
        .transform((data) => {
            if (isBabysitter.value) {
                return data;
            }
            const payload = { ...data };
            if (!Array.isArray(payload.children) || payload.children.length === 0) {
                delete payload.children;
            }
            return payload;
        })
        .patch(route(routeName), { preserveScroll: true });
};

const showChildForm = ref(!isBabysitter.value && initialChildren.length === 0);
const childDraft = ref(emptyChild());
const childPhotoKey = ref(0);
const childDefaultPhotos = CHILD_DEFAULT_PHOTOS;

const hasChildContent = (child: ReturnType<typeof emptyChild>) => {
    if (child.photo) {
        return true;
    }
    return ['name', 'age', 'allergies', 'details'].some((key) => {
        const value = child[key as keyof typeof child];
        return String(value ?? '').trim() !== '';
    });
};

const resetChildDraft = () => {
    childDraft.value = emptyChild();
    childPhotoKey.value += 1;
};

const openChildForm = () => {
    showChildForm.value = true;
};

const cancelChildForm = () => {
    resetChildDraft();
    showChildForm.value = Array.isArray(detailsForm.children) && detailsForm.children.length === 0;
};

const saveChild = () => {
    if (!hasChildContent(childDraft.value)) {
        return;
    }
    detailsForm.children.push({ ...childDraft.value });
    resetChildDraft();
    showChildForm.value = false;
};

const removeChild = (index: number) => {
    if (detailsForm.children.length > 1) {
        detailsForm.children.splice(index, 1);
    }
};

const setChildPhoto = (event: Event) => {
    const target = event.target as HTMLInputElement | null;
    const file = target?.files?.[0] ?? null;
    if (!file) {
        childDraft.value.photo = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = () => {
        childDraft.value.photo = typeof reader.result === 'string' ? reader.result : '';
    };
    reader.readAsDataURL(file);
};

const clearChildPhoto = () => {
    childDraft.value.photo = '';
    childPhotoKey.value += 1;
};

const selectDefaultChildPhoto = (photo: string) => {
    childDraft.value.photo = photo;
};

const tabItems = computed(() => {
    const items = [
        { value: 'account', label: t('settings.profile.tabs.account') },
    ];

    if (isSuperAdmin.value) {
        items.push({ value: 'media', label: t('settings.profile.tabs.media') });
        items.push({ value: 'gallery', label: t('settings.profile.tabs.gallery') });
        return items;
    }

    items.push({ value: 'address', label: t('settings.profile.tabs.address') });

    if (!isBabysitter.value) {
        items.push({ value: 'children', label: t('settings.profile.tabs.children') });
    }

    items.push({ value: 'availability', label: t('settings.profile.tabs.availability') });
    items.push({ value: 'media', label: t('settings.profile.tabs.media') });
    items.push({ value: 'gallery', label: t('settings.profile.tabs.gallery') });

    return items;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="$t('settings.profile.title')" />

        <SettingsLayout>
            <Tabs default-value="account" class="space-y-6">
                <TabsList class="flex flex-wrap gap-2">
                    <TabsTrigger v-for="tab in tabItems" :key="tab.value" :value="tab.value">
                        {{ tab.label }}
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="account" class="space-y-6">
                    <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                        <HeadingSmall
                            :title="$t('settings.profile.sections.account.title')"
                            :description="$t('settings.profile.sections.account.description')"
                        />
                        <form @submit.prevent="submitAccount" class="mt-6 space-y-6">
                            <div class="grid gap-2">
                                <FloatingInput
                                    id="name"
                                    :label="$t('common.labels.name')"
                                    v-model="accountForm.name"
                                    required
                                    autocomplete="name"
                                />
                                <InputError class="mt-2" :message="accountForm.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <FloatingInput
                                    id="email"
                                    :label="$t('common.labels.email_address')"
                                    type="email"
                                    v-model="accountForm.email"
                                    required
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="accountForm.errors.email" />
                            </div>

                            <div v-if="mustVerifyEmail && !user.email_verified_at">
                                <p class="-mt-4 text-sm text-muted-foreground">
                                    {{ $t('settings.profile.account.unverified') }}
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        {{ $t('settings.profile.account.resend') }}
                                    </Link>
                                </p>

                                <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                                    {{ $t('settings.profile.account.link_sent') }}
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <Button :disabled="accountForm.processing">{{ $t('common.actions.save') }}</Button>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-show="accountForm.recentlySuccessful" class="text-sm text-muted-foreground">
                                        {{ $t('common.misc.saved') }}
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </div>

                    <div v-if="!isSuperAdmin" class="rounded-lg border border-border bg-card p-6 shadow-sm">
                        <HeadingSmall
                            :title="$t('settings.profile.sections.details.title')"
                            :description="$t('settings.profile.sections.details.description')"
                        />
                        <form @submit.prevent="submitDetails" class="mt-6 space-y-6">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <FloatingInput id="first_name" :label="$t('common.labels.first_name')" v-model="detailsForm.first_name" />
                                    <InputError :message="detailsForm.errors.first_name" />
                                </div>
                                <div class="space-y-2">
                                    <FloatingInput id="last_name" :label="$t('common.labels.last_name')" v-model="detailsForm.last_name" />
                                    <InputError :message="detailsForm.errors.last_name" />
                                </div>
                                <div class="space-y-2">
                                    <FloatingInput id="phone" :label="$t('common.labels.phone')" v-model="detailsForm.phone" />
                                    <InputError :message="detailsForm.errors.phone" />
                                </div>
                                <div class="space-y-2">
                                    <FloatingInput id="birthdate" :label="$t('common.labels.birthdate')" type="date" v-model="detailsForm.birthdate" />
                                    <InputError :message="detailsForm.errors.birthdate" />
                                </div>
                            </div>

                            <div v-if="isBabysitter" class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <FloatingInput
                                            id="price_per_hour"
                                            :label="$t('common.labels.price_per_hour')"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            v-model="detailsForm.price_per_hour"
                                        />
                                        <InputError :message="detailsForm.errors.price_per_hour" />
                                    </div>
                                    <div class="space-y-2">
                                        <FloatingSelect
                                            id="payment_frequency"
                                            :label="$t('common.labels.payment_frequency')"
                                            :options="paymentOptions"
                                            v-model="detailsForm.payment_frequency"
                                        />
                                        <InputError :message="detailsForm.errors.payment_frequency" />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <FloatingTextarea id="services" :label="$t('common.labels.services')" rows="2" v-model="detailsForm.services" />
                                    <InputError :message="detailsForm.errors.services" />
                                </div>

                                <div class="space-y-2">
                                    <FloatingTextarea id="experience" :label="$t('common.labels.experience')" rows="3" v-model="detailsForm.experience" />
                                    <InputError :message="detailsForm.errors.experience" />
                                </div>

                                <div class="space-y-2">
                                    <FloatingTextarea id="bio" :label="$t('common.labels.bio')" rows="3" v-model="detailsForm.bio" />
                                    <InputError :message="detailsForm.errors.bio" />
                                </div>
                            </div>

                            <div v-else class="space-y-2">
                                <FloatingTextarea id="preferences" :label="$t('common.labels.preferences')" rows="3" v-model="detailsForm.preferences" />
                                <InputError :message="detailsForm.errors.preferences" />
                            </div>

                            <div class="flex items-center justify-between">
                                <Button type="submit" :disabled="detailsForm.processing">
                                    {{ $t('settings.profile.actions.save_profile') }}
                                </Button>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-show="detailsForm.recentlySuccessful" class="text-sm text-muted-foreground">
                                        {{ $t('common.misc.saved') }}
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </div>

                    <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                        <DeleteUser />
                    </div>
                </TabsContent>

                <TabsContent v-if="!isSuperAdmin" value="address">
                    <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                        <HeadingSmall
                            :title="$t('settings.profile.sections.address.title')"
                            :description="$t('settings.profile.sections.address.description')"
                        />
                        <form @submit.prevent="submitDetails" class="mt-6 space-y-6">
                            <AddressForm v-model="addressModel" />
                            <InputError
                                :message="detailsForm.errors['address.street'] || detailsForm.errors['address.city'] || detailsForm.errors['address.country']"
                            />
                            <div class="flex items-center justify-between">
                                <Button type="submit" :disabled="detailsForm.processing">
                                    {{ $t('settings.profile.actions.save_address') }}
                                </Button>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-show="detailsForm.recentlySuccessful" class="text-sm text-muted-foreground">
                                        {{ $t('common.misc.saved') }}
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </TabsContent>


                <TabsContent v-if="!isBabysitter && !isSuperAdmin" value="children">
                    <div class="rounded-lg border border-border bg-card p-6 shadow-sm space-y-6">
                        <HeadingSmall
                            :title="$t('settings.profile.sections.children.title')"
                            :description="$t('settings.profile.sections.children.description')"
                        />
                        <InputError :message="detailsForm.errors.children" />
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-muted-foreground">{{ $t('settings.profile.children.hint') }}</p>
                            <Button type="button" variant="outline" @click="openChildForm">
                                {{ $t('common.actions.add_child') }}
                            </Button>
                        </div>

                        <div v-if="showChildForm" class="rounded-sm border border-border p-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <FloatingInput id="child_name" :label="$t('common.labels.child_name')" v-model="childDraft.name" />
                                </div>
                                <div class="space-y-2">
                                    <FloatingInput id="child_age" :label="$t('common.labels.age')" type="number" min="0" v-model="childDraft.age" />
                                </div>
                                <div class="space-y-2 sm:col-span-2">
                                    <FloatingInput id="child_allergies" :label="$t('common.labels.allergies')" v-model="childDraft.allergies" />
                                </div>
                                <div class="space-y-2 sm:col-span-2">
                                    <FloatingTextarea id="child_details" :label="$t('common.labels.details')" rows="2" v-model="childDraft.details" />
                                </div>
                                <div class="space-y-2 sm:col-span-2">
                                    <label class="text-sm font-medium text-foreground" for="child_photo">
                                        {{ $t('common.labels.photo') }}
                                    </label>
                                    <input
                                        :key="childPhotoKey"
                                        id="child_photo"
                                        type="file"
                                        accept="image/*"
                                        class="block w-full text-sm text-foreground file:mr-3 file:rounded-sm file:border-0 file:bg-muted file:px-3 file:py-2 file:text-sm file:font-medium file:text-foreground hover:file:bg-muted"
                                        @change="setChildPhoto"
                                    />
                                    <div v-if="childDraft.photo" class="mt-3 flex items-center gap-3">
                                        <img
                                            :src="childDraft.photo"
                                            :alt="$t('settings.profile.children.photo_preview_alt')"
                                            class="h-16 w-16 rounded-sm object-cover"
                                        />
                                        <Button type="button" variant="outline" @click="clearChildPhoto">
                                            {{ $t('settings.profile.actions.remove_photo') }}
                                        </Button>
                                    </div>
                                    <div class="mt-4 space-y-2">
                                        <p class="text-sm font-medium text-foreground">{{ $t('common.labels.images_default') }}</p>
                                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
                                            <button
                                                v-for="photo in childDefaultPhotos"
                                                :key="photo"
                                                type="button"
                                                class="group overflow-hidden rounded-sm border border-border transition"
                                                :class="childDraft.photo === photo
                                                    ? 'ring-2 ring-primary/60'
                                                    : 'hover:border-primary/60'"
                                                @click="selectDefaultChildPhoto(photo)"
                                            >
                                                <img
                                                    :src="photo"
                                                    :alt="$t('settings.profile.children.default_photo_alt')"
                                                    class="h-16 w-full object-cover"
                                                />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-end gap-2">
                                <Button type="button" variant="outline" @click="cancelChildForm">
                                    {{ $t('common.actions.cancel') }}
                                </Button>
                                <Button type="button" @click="saveChild">
                                    {{ $t('settings.profile.actions.save_child') }}
                                </Button>
                            </div>
                        </div>

                        <div v-if="detailsForm.children.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="(child, index) in detailsForm.children"
                                :key="index"
                                class="rounded-sm border border-border p-4"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-sm bg-muted">
                                        <img
                                            :src="resolveChildPhoto(child.photo, [child.name, child.age], index)"
                                            :alt="$t('settings.profile.children.photo_alt')"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div class="space-y-1 text-sm text-muted-foreground">
                                        <p class="text-sm font-semibold text-foreground">
                                            {{ child.name || $t('settings.profile.children.default_name', { index: index + 1 }) }}
                                        </p>
                                        <p>{{ $t('settings.profile.children.age_line', { age: child.age || '-' }) }}</p>
                                        <p v-if="child.allergies">{{ $t('settings.profile.children.allergies_line', { allergies: child.allergies }) }}</p>
                                        <p v-if="child.details">{{ child.details }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <Button
                                        v-if="detailsForm.children.length > 1"
                                        type="button"
                                        variant="outline"
                                        @click="removeChild(index)"
                                    >
                                        {{ $t('common.actions.remove') }}
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <Button type="button" variant="outline" @click="submitDetails" :disabled="detailsForm.processing">
                                {{ $t('settings.profile.actions.save_children') }}
                            </Button>
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p v-show="detailsForm.recentlySuccessful" class="text-sm text-muted-foreground">
                                    {{ $t('common.misc.saved') }}
                                </p>
                            </Transition>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent v-if="!isSuperAdmin" value="availability">
                    <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                        <HeadingSmall
                            :title="$t('settings.profile.sections.availability.title')"
                            :description="$t('settings.profile.sections.availability.description')"
                        />
                        <form @submit.prevent="submitDetails" class="mt-6 space-y-4">
                            <FloatingTextarea
                                id="availability"
                                :label="$t('common.labels.availability')"
                                rows="3"
                                v-model="detailsForm.availability"
                            />
                            <InputError :message="detailsForm.errors.availability" />
                            <FloatingTextarea
                                id="availability_notes"
                                :label="$t('common.labels.availability_notes')"
                                rows="3"
                                v-model="detailsForm.availability_notes"
                            />
                            <InputError :message="detailsForm.errors.availability_notes" />
                            <div class="flex items-center justify-between">
                                <Button type="submit" :disabled="detailsForm.processing">
                                    {{ $t('settings.profile.actions.save_availability') }}
                                </Button>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-show="detailsForm.recentlySuccessful" class="text-sm text-muted-foreground">
                                        {{ $t('common.misc.saved') }}
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </TabsContent>

                <TabsContent value="media">
                    <div class="rounded-lg border border-border bg-card p-6 shadow-sm space-y-6">
                        <HeadingSmall
                            :title="$t('settings.profile.sections.media.title')"
                            :description="$t('settings.profile.sections.media.description')"
                        />
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="space-y-3">
                                <h3 class="text-sm font-semibold text-foreground">{{ $t('common.labels.avatar') }}</h3>
                                <MediaUploadForm
                                    collection-name="avatar"
                                    :collection-label="$t('common.labels.avatar')"
                                    :max-photos="1"
                                    :hide-collection-input="true"
                                />
                            </div>
                            <div class="space-y-3">
                                <h3 class="text-sm font-semibold text-foreground">{{ $t('common.labels.gallery') }}</h3>
                                <MediaUploadForm
                                    collection-name="gallery"
                                    :collection-label="$t('common.labels.gallery')"
                                    :max-photos="5"
                                    :hide-collection-input="true"
                                />
                            </div>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="gallery">
                    <div class="rounded-lg border border-border bg-card p-6 shadow-sm space-y-6">
                        <HeadingSmall
                            :title="$t('settings.profile.sections.gallery.title')"
                            :description="$t('settings.profile.sections.gallery.description')"
                        />
                        <MediaScrollingHorizontal :items="mediaItems" />
                    </div>
                </TabsContent>

            </Tabs>
        </SettingsLayout>
    </AppLayout>
</template>
