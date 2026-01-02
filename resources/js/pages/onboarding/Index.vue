<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import FloatingInput from '@/components/FloatingInput.vue';
import FloatingSelect from '@/components/FloatingSelect.vue';
import FloatingTextarea from '@/components/FloatingTextarea.vue';
import AddressForm from '@/components/AddressForm.vue';
import MediaUploadForm from '@/components/MediaUploadForm.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { CHILD_DEFAULT_PHOTOS, resolveChildPhoto } from '@/lib/childPhotos';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
const page = usePage();

const currentStep = computed(() => Number(page.props.step ?? 1));
const isAuthed = computed(() => Boolean(page.props.auth?.user));
const role = computed(() => {
    const direct = page.props.role ?? null;
    if (direct) {
        return direct;
    }
    const fromAuth = page.props.auth?.role ?? '';
    if (typeof fromAuth === 'string' && fromAuth.length > 0) {
        return fromAuth.toLowerCase();
    }
    return 'parent';
});

const account = computed(() => page.props.account ?? {});
const address = computed(() => page.props.address ?? {});
const profile = computed(() => page.props.profile ?? {});
const profileSettings = computed(() => profile.value?.settings ?? {});
const isBabysitter = computed(() => role.value === 'babysitter');

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

const parentChildren = computed(() => {
    const children = profileSettings.value?.children;
    if (Array.isArray(children)) {
        return children;
    }
    return [];
});

const childrenSummary = computed(() => {
    if (parentChildren.value.length > 0) {
        const names = parentChildren.value
            .map((child) => child?.name)
            .filter((name) => typeof name === 'string' && name.trim() !== '');
        if (names.length > 0) {
            return `${parentChildren.value.length} (${names.join(', ')})`;
        }
        return String(parentChildren.value.length);
    }

    const count = profileSettings.value?.children_count;
    if (count !== null && count !== undefined && count !== '') {
        return String(count);
    }

    return '-';
});

const steps = [
    { id: 1, title: 'Compte', subtitle: 'Identite et acces' },
    { id: 2, title: 'Adresse', subtitle: 'Coordonnees' },
    { id: 3, title: 'Profil', subtitle: 'Infos specifiques' },
    { id: 4, title: 'Media', subtitle: 'Avatar et galerie' },
    { id: 5, title: 'Disponibilites', subtitle: 'Horaires et prefs' },
    { id: 6, title: 'Resume', subtitle: 'Validation finale' },
];

const roleOptions = [
    { value: 'parent', label: 'Parent' },
    { value: 'babysitter', label: 'Babysitter' },
];

const paymentOptions = [
    { value: 'per_task', label: 'Per task' },
    { value: 'daily', label: 'Daily' },
    { value: 'weekly', label: 'Weekly' },
    { value: 'biweekly', label: 'Biweekly' },
    { value: 'monthly', label: 'Monthly' },
];

const registerForm = useForm({
    first_name: account.value?.first_name ?? '',
    last_name: account.value?.last_name ?? '',
    email: account.value?.email ?? '',
    password: '',
    password_confirmation: '',
    role: role.value ?? 'parent',
});

const submitRegister = () => {
    registerForm.post(route('register'), {
        onFinish: () => registerForm.reset('password', 'password_confirmation'),
    });
};

const addressForm = useForm({
    street: address.value?.street ?? '',
    city: address.value?.city ?? '',
    province: address.value?.province ?? address.value?.state ?? '',
    postal_code: address.value?.postal_code ?? '',
    country: address.value?.country ?? '',
    latitude: address.value?.latitude ?? '',
    longitude: address.value?.longitude ?? '',
});

const addressModel = computed({
    get: () => ({
        street: addressForm.street,
        city: addressForm.city,
        province: addressForm.province,
        postal_code: addressForm.postal_code,
        country: addressForm.country,
        latitude: addressForm.latitude,
        longitude: addressForm.longitude,
    }),
    set: (value) => {
        addressForm.street = value?.street ?? '';
        addressForm.city = value?.city ?? '';
        addressForm.province = value?.province ?? '';
        addressForm.postal_code = value?.postal_code ?? '';
        addressForm.country = value?.country ?? '';
        addressForm.latitude = value?.latitude ?? '';
        addressForm.longitude = value?.longitude ?? '';
    },
});

const submitAddress = () => {
    addressForm.post(route('onboarding.address.store'));
};

const profileForm = useForm({
    bio: profile.value?.bio ?? '',
    experience: profile.value?.experience ?? '',
    price_per_hour: profile.value?.price_per_hour ?? '',
    payment_frequency: profile.value?.payment_frequency ?? 'per_task',
    services: profileSettings.value?.services ?? '',
    children: buildInitialChildren(),
    preferences: profileSettings.value?.preferences ?? '',
});

const submitProfile = () => {
    if (!isBabysitter.value && hasChildContent(childDraft.value)) {
        profileForm.children.push({ ...childDraft.value });
        resetChildDraft();
        showChildForm.value = false;
    }
    profileForm.post(route('onboarding.profile.store'));
};

const showChildForm = ref(profileForm.children.length === 0);
const childDraft = ref(emptyChild());
const childPhotoKey = ref(0);
const childDefaultPhotos = CHILD_DEFAULT_PHOTOS;

const hasChildContent = (child) => {
    if (child.photo) {
        return true;
    }
    return ['name', 'age', 'allergies', 'details'].some((key) => {
        const value = child[key];
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
    showChildForm.value = profileForm.children.length === 0;
};

const saveChild = () => {
    if (!hasChildContent(childDraft.value)) {
        return;
    }
    profileForm.children.push({ ...childDraft.value });
    resetChildDraft();
    showChildForm.value = false;
};

const removeChild = (index) => {
    if (profileForm.children.length > 1) {
        profileForm.children.splice(index, 1);
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

const availabilityForm = useForm({
    availability: profileSettings.value?.availability ?? '',
    availability_notes: profileSettings.value?.availability_notes ?? '',
});

const submitAvailability = () => {
    availabilityForm.post(route('onboarding.availability.store'));
};

const finishForm = useForm({});
const finishOnboarding = () => {
    finishForm.post(route('onboarding.finish'));
};

const goToStep = (step: number) => {
    router.visit(route('onboarding.index', { step }));
};
</script>

<template>

    <Head title="Onboarding" />

    <div class="min-h-svh bg-muted/50 px-4 py-10">
        <div class="mx-auto w-full max-w-6xl">
            <div class="h-44 w-44 mx-auto">
                <AppLogoIcon />
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-[240px_1fr]">
                <aside class="space-y-3">
                    <div class="rounded-sm border border-border bg-card p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase text-muted-foreground">Progression</p>
                            <span class="text-xs text-muted-foreground/70">Step {{ currentStep }} / 6</span>
                        </div>
                        <ul class="mt-4 space-y-3">
                            <li v-for="step in steps" :key="step.id">
                                <button type="button"
                                    class="flex w-full items-start gap-3 rounded-sm px-2 py-2 text-left transition"
                                    :class="currentStep === step.id ? 'bg-muted' : ''"
                                    :disabled="!isAuthed && step.id > 1" @click="goToStep(step.id)">
                                    <span
                                        class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full border text-xs font-semibold"
                                        :class="currentStep >= step.id ? 'border-emerald-500 text-emerald-600' : 'border-border text-muted-foreground/70'">
                                        {{ step.id }}
                                    </span>
                                    <span>
                                        <span class="block text-sm font-medium text-foreground">
                                            {{ step.title }}
                                        </span>
                                        <span class="block text-xs text-muted-foreground/70">{{ step.subtitle }}</span>
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </aside>

                <section class="rounded-sm border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-lg font-semibold text-foreground">{{ steps[currentStep - 1]?.title }}</h1>
                            <p class="text-sm text-muted-foreground">{{ steps[currentStep - 1]?.subtitle }}</p>
                        </div>
                        <span class="text-xs text-muted-foreground/70">Step {{ currentStep }} / 6</span>
                    </div>

                    <form v-if="currentStep === 1" @submit.prevent="submitRegister" class="mt-6 space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <FloatingInput id="first_name" label="First name" v-model="registerForm.first_name"
                                    required />
                                <InputError :message="registerForm.errors.first_name" />
                            </div>
                            <div class="space-y-2">
                                <FloatingInput id="last_name" label="Last name" v-model="registerForm.last_name"
                                    required />
                                <InputError :message="registerForm.errors.last_name" />
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <FloatingInput id="email" label="Email" type="email" autocomplete="email"
                                    v-model="registerForm.email" required />
                                <InputError :message="registerForm.errors.email" />
                            </div>
                            <div class="space-y-2">
                                <FloatingInput id="password" label="Password" type="password"
                                    autocomplete="new-password" v-model="registerForm.password" required />
                                <InputError :message="registerForm.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <FloatingInput id="password_confirmation" label="Confirm password" type="password"
                                    autocomplete="new-password" v-model="registerForm.password_confirmation" required />
                                <InputError :message="registerForm.errors.password_confirmation" />
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <FloatingSelect id="role" label="Account type" :options="roleOptions"
                                    v-model="registerForm.role" required />
                                <InputError :message="registerForm.errors.role" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-sm text-muted-foreground">
                                Already have an account?
                                <Link :href="route('login')" class="text-foreground underline">Log in</Link>
                            </div>
                            <Button type="submit" :disabled="registerForm.processing">Create account</Button>
                        </div>
                    </form>

                    <form v-else-if="currentStep === 2" @submit.prevent="submitAddress" class="mt-6 space-y-5">
                        <div class="rounded-sm border border-border bg-muted/50 p-4 text-sm text-muted-foreground">
                            Search your address to fill the fields quickly.
                        </div>
                        <AddressForm v-model="addressModel" />
                        <InputError :message="addressForm.errors.city || addressForm.errors.country" />
                        <div class="flex items-center justify-between">
                            <Button type="button" variant="outline" @click="goToStep(1)">Back</Button>
                            <Button type="submit" :disabled="addressForm.processing">Continue</Button>
                        </div>
                    </form>

                    <form v-else-if="currentStep === 3" @submit.prevent="submitProfile" class="mt-6 space-y-5">
                        <div v-if="isBabysitter" class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2 sm:col-span-2">
                                <FloatingTextarea id="bio" label="Bio" rows="3" v-model="profileForm.bio" />
                                <InputError :message="profileForm.errors.bio" />
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <FloatingTextarea id="experience" label="Experience" rows="3"
                                    v-model="profileForm.experience" />
                                <InputError :message="profileForm.errors.experience" />
                            </div>
                            <div class="space-y-2">
                                <FloatingInput id="price_per_hour" label="Price per hour" type="number" min="0"
                                    step="0.01" v-model="profileForm.price_per_hour" />
                                <InputError :message="profileForm.errors.price_per_hour" />
                            </div>
                            <div class="space-y-2">
                                <FloatingSelect id="payment_frequency" label="Payment frequency"
                                    :options="paymentOptions" v-model="profileForm.payment_frequency" />
                                <InputError :message="profileForm.errors.payment_frequency" />
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <FloatingTextarea id="services" label="Services" rows="2"
                                    v-model="profileForm.services" />
                                <InputError :message="profileForm.errors.services" />
                            </div>
                        </div>

                        <div v-else class="space-y-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-foreground">Children details</p>
                                <Button type="button" variant="outline" @click="openChildForm">Add child</Button>
                            </div>
                            <InputError :message="profileForm.errors.children" />

                            <div v-if="showChildForm" class="rounded-sm border border-border p-4">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <FloatingInput id="child_name" label="Child name" v-model="childDraft.name" />
                                    </div>
                                    <div class="space-y-2">
                                        <FloatingInput id="child_age" label="Age" type="number" min="0"
                                            v-model="childDraft.age" />
                                    </div>
                                    <div class="space-y-2 sm:col-span-2">
                                        <FloatingInput id="child_allergies" label="Allergies"
                                            v-model="childDraft.allergies" />
                                    </div>
                                    <div class="space-y-2 sm:col-span-2">
                                        <FloatingTextarea id="child_details" label="Details" rows="2"
                                            v-model="childDraft.details" />
                                    </div>
                                    <div class="space-y-2 sm:col-span-2">
                                        <label class="text-sm font-medium text-foreground"
                                            for="child_photo">Photo</label>
                                        <input :key="childPhotoKey" id="child_photo" type="file" accept="image/*"
                                            class="block w-full text-sm text-foreground file:mr-3 file:rounded-sm file:border-0 file:bg-muted file:px-3 file:py-2 file:text-sm file:font-medium file:text-foreground hover:file:bg-muted"
                                            @change="setChildPhoto" />
                                        <div v-if="childDraft.photo" class="mt-3 flex items-center gap-3">
                                            <img :src="childDraft.photo" alt="Child photo preview"
                                                class="h-16 w-16 rounded-sm object-cover" />
                                            <Button type="button" variant="outline" @click="clearChildPhoto">
                                                Remove photo
                                            </Button>
                                        </div>
                                        <div class="mt-4 space-y-2">
                                            <p class="text-sm font-medium text-foreground">Images par defaut</p>
                                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
                                                <button v-for="photo in childDefaultPhotos" :key="photo" type="button"
                                                    class="group overflow-hidden rounded-sm border border-border transition"
                                                    :class="childDraft.photo === photo
                                                        ? 'ring-2 ring-primary/60'
                                                        : 'hover:border-primary/60'"
                                                    @click="selectDefaultChildPhoto(photo)">
                                                    <img :src="photo" alt="Default child"
                                                        class="h-16 w-full object-cover" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center justify-end gap-2">
                                    <Button type="button" variant="outline" @click="cancelChildForm">Cancel</Button>
                                    <Button type="button" @click="saveChild">Save child</Button>
                                </div>
                            </div>

                            <div v-if="profileForm.children.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div v-for="(child, index) in profileForm.children" :key="index"
                                    class="rounded-sm border border-border p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-sm bg-muted">
                                            <img :src="resolveChildPhoto(child.photo, [child.name, child.age], index)"
                                                alt="Child photo" class="h-full w-full object-cover" />
                                        </div>
                                        <div class="space-y-1 text-sm text-muted-foreground">
                                            <p class="text-sm font-semibold text-foreground">
                                                {{ child.name || `Child ${index + 1}` }}
                                            </p>
                                            <p>Age: {{ child.age || '-' }}</p>
                                            <p v-if="child.allergies">Allergies: {{ child.allergies }}</p>
                                            <p v-if="child.details">{{ child.details }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <Button v-if="profileForm.children.length > 1" type="button" variant="outline"
                                            @click="removeChild(index)">
                                            Remove
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <FloatingTextarea id="preferences" label="Preferences" rows="3"
                                    v-model="profileForm.preferences" />
                                <InputError :message="profileForm.errors.preferences" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <Button type="button" variant="outline" @click="goToStep(2)">Back</Button>
                            <Button type="submit" :disabled="profileForm.processing">Continue</Button>
                        </div>
                    </form>

                    <div v-else-if="currentStep === 4" class="mt-6 space-y-6">
                        <div class="rounded-sm border border-border bg-muted/50 p-4 text-sm text-muted-foreground">
                            Upload an avatar and a small gallery. You can update this later in settings.
                        </div>
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="space-y-3">
                                <h3 class="text-sm font-semibold text-foreground">Avatar</h3>
                                <MediaUploadForm collection-name="avatar" collection-label="Avatar" :max-photos="1"
                                    :hide-collection-input="true" />
                            </div>
                            <div class="space-y-3">
                                <h3 class="text-sm font-semibold text-foreground">Gallery</h3>
                                <MediaUploadForm collection-name="gallery" collection-label="Gallery" :max-photos="5"
                                    :hide-collection-input="true" />
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <Button type="button" variant="outline" @click="goToStep(3)">Back</Button>
                            <Button type="button" @click="goToStep(5)">Continue</Button>
                        </div>
                    </div>

                    <form v-else-if="currentStep === 5" @submit.prevent="submitAvailability" class="mt-6 space-y-5">
                        <div class="grid gap-4">
                            <FloatingTextarea id="availability" label="Availability" rows="3"
                                v-model="availabilityForm.availability" />
                            <InputError :message="availabilityForm.errors.availability" />
                            <FloatingTextarea id="availability_notes" label="Notes" rows="3"
                                v-model="availabilityForm.availability_notes" />
                            <InputError :message="availabilityForm.errors.availability_notes" />
                        </div>
                        <div class="flex items-center justify-between">
                            <Button type="button" variant="outline" @click="goToStep(4)">Back</Button>
                            <Button type="submit" :disabled="availabilityForm.processing">Continue</Button>
                        </div>
                    </form>

                    <div v-else class="mt-6 space-y-6">
                        <div class="grid gap-4 rounded-sm border border-border bg-muted/50 p-4 text-sm text-foreground">
                            <div>
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Account</p>
                                <p>{{ account.first_name ?? '' }} {{ account.last_name ?? '' }}</p>
                                <p class="text-muted-foreground">{{ account.email ?? '' }}</p>
                                <p class="text-muted-foreground">Role: {{ role }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Address</p>
                                <p>{{ address.street ?? '' }}</p>
                                <p>{{ address.city ?? '' }} {{ address.postal_code ?? '' }}</p>
                                <p>{{ address.country ?? '' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-muted-foreground">Profile</p>
                                <p v-if="isBabysitter">Price: {{ profile.price_per_hour ?? '-' }}</p>
                                <p v-else>Children: {{ childrenSummary }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <Button type="button" variant="outline" @click="goToStep(5)">Back</Button>
                            <Button type="button" :disabled="finishForm.processing" @click="finishOnboarding">
                                Finish
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
