<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3'
import { computed } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { type SharedData, type ParentProfile } from '@/types'
import { LoaderCircle, SaveIcon } from 'lucide-vue-next'
const page = usePage<SharedData>()
const parentProfile = computed(() => page.props.parentProfile as ParentProfile)
const role = computed(() => page.props.role as string)

const breadcrumbs = [
    { title: 'Profile Details', href: 'settings/parent/profile/details' },
]

const form = useForm({
    first_name: parentProfile?.value?.first_name ?? '',
    last_name: parentProfile?.value?.last_name ?? '',
    phone: parentProfile?.value?.phone ?? '',
    birthdate: parentProfile?.value?.birthdate ?? '',
})

function submit() {
    form.post(route('parent.profile.details.update'), { preserveScroll: true })
}
</script>

<template>

    <Head title="Profile Details Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout>
            <div class="max-w-3xl mx-auto">
                <HeadingSmall title="Profile Information" description="Update your personal details" />
                <form @submit.prevent="submit">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
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

                        <!-- Action buttons -->
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
