<script setup lang="ts">
import { Head, usePage, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue';
import DatePicker from '@/components/DatePicker.vue'; // Adaptez le chemin d'importation

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import InputError from '@/components/InputError.vue'
import AddressForm from '@/components/AddressForm.vue'

import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group'
import { type SharedData, type BabysitterProfile, type Address } from '@/types'
import { LoaderCircle, SaveIcon, CalendarDays, CalendarSync, CalendarCheck, Clock, Calendar1 } from 'lucide-vue-next'
const page = usePage<SharedData>()
const babysitterProfile = computed(() => page.props.babysitterProfile as BabysitterProfile)
const address = computed(() => page.props.address as Address)

const role = computed(() => page.props.role as string)
import {
    NumberField,
    NumberFieldContent,
    NumberFieldDecrement,
    NumberFieldIncrement,
    NumberFieldInput,
} from '@/components/ui/number-field'

const breadcrumbs = [
    { title: 'Profile Details', href: 'settings/babysitter/profile/details' },
]

const form = useForm({
    first_name: babysitterProfile?.value?.first_name ?? '',
    last_name: babysitterProfile?.value?.last_name ?? '',
    phone: babysitterProfile?.value?.phone ?? '',
    birthdate: babysitterProfile?.value?.birthdate ?? '',
    bio: babysitterProfile?.value?.bio ?? '',
    experience: babysitterProfile?.value?.experience ?? '',
    price_per_hour: Number(babysitterProfile?.value?.price_per_hour) ?? 0,
    payment_frequency: babysitterProfile?.value?.payment_frequency ?? 'per_task',
    address: address?.value ? { ...address.value } : {
        street: '',
        city: '',
        state: '',
        country: '',
        postal_code: '',
    },
})

function submit() {
    form.patch(route('babysitter.profile.update'), { preserveScroll: true })
}
</script>

<template>

    <Head title="Profile Details Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout>
            <div class="max-w-3xl mx-auto">
                <HeadingSmall title="Profile Information" description="Update your personal details" />
                <form @submit.prevent="submit">
                    <div class="my-8 w-full">
                        <Label for="first_name" class="mb-2">Payment frequencies</Label>
                        <ToggleGroup type="single" class="w-full" v-model="form.payment_frequency" variant="outline">
                            <ToggleGroupItem value="per_task" aria-label="Toggle bold">
                                <Calendar1 class="h-4 w-4" /> Per task
                            </ToggleGroupItem>
                            <ToggleGroupItem value="daily" aria-label="Toggle italic">
                                <Clock class="h-4 w-4" /> Daily
                            </ToggleGroupItem>
                            <ToggleGroupItem value="weekly" aria-label="Toggle italic">
                                <CalendarDays class="h-4 w-4" /> Weekly
                            </ToggleGroupItem>
                            <ToggleGroupItem value="biweekly" aria-label="Toggle strikethrough">
                                <CalendarCheck class="h-4 w-4" /> Biweekly
                            </ToggleGroupItem>
                            <ToggleGroupItem value="monthly" aria-label="Toggle underline">
                                <CalendarSync class="h-4 w-4" /> Monthly
                            </ToggleGroupItem>
                        </ToggleGroup>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <Label for="first_name">First Name</Label>
                            <Input id="first_name" v-model="form.first_name" class="mt-1 w-full" />
                            <InputError :message="form.errors.first_name" class="mt-1" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <Label for="last_name">Last Name</Label>
                            <Input id="last_name" v-model="form.last_name" class="mt-1 w-full" />
                            <InputError :message="form.errors.last_name" class="mt-1" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <Label for="phone">Phone</Label>
                            <Input id="phone" v-model="form.phone" class="mt-1 w-full" />
                            <InputError :message="form.errors.phone" class="mt-1" />
                        </div>

                        <!-- Birthdate -->
                        <div>
                            <Label for="birthdate">Birthdate</Label>
                            <Input id="birthdate" type="date" v-model="form.birthdate" class="mt-1 w-full" />
                            <InputError :message="form.errors.birthdate" class="mt-1" />
                        </div>

                        <NumberField class="gap-2" :min="0" :format-options="{
                            style: 'currency',
                            currency: 'EUR',
                            currencyDisplay: 'code',
                            currencySign: 'accounting',
                        }" v-model:model-value="form.price_per_hour">
                            <Label for="price_per_hour">Price per hour</Label>
                            <NumberFieldContent>
                                <NumberFieldDecrement />
                                <NumberFieldInput v-model="form.price_per_hour" />
                                <NumberFieldIncrement />
                            </NumberFieldContent>
                        </NumberField>

                        <!-- experience full width -->
                        <div class="sm:col-span-2">
                            <Label for="experience">experience</Label>
                            <Textarea id="experience" rows="3" v-model="form.experience" class="mt-1 w-full" />
                            <InputError :message="form.errors.experience" class="mt-1" />
                        </div>

                        <!-- Bio full width -->
                        <div class="sm:col-span-2">
                            <Label for="bio">Bio</Label>
                            <Textarea id="bio" rows="4" v-model="form.bio" class="mt-1 w-full" />
                            <InputError :message="form.errors.bio" class="mt-1" />
                        </div>
                        <!-- Action buttons -->
                    </div>
                    <div key="address" class="mt-6">
                        <HeadingSmall title="Address Information" description="Update your address details"
                            class="my-4" />
                        <AddressForm v-model="form.address" />
                    </div>
                    <div class="mt-8 flex flex-col">
                        <Button type="submit" class="w-full my-2" :tabindex="4" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <SaveIcon v-else /> Update profile details
                        </Button>
                        <Button type="button" variant="outline" class="w-full" @click="form.reset()">Reset</Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
