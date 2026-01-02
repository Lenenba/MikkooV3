<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3'
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import FloatingInput from '@/components/FloatingInput.vue'
import InputError from '@/components/InputError.vue'
import { type SharedData, type ParentProfile, type Address } from '@/types'
import { LoaderCircle, SaveIcon } from 'lucide-vue-next'
import AddressForm from '@/components/AddressForm.vue'
const page = usePage<SharedData>()
const parentProfile = computed(() => page.props.parentProfile as ParentProfile)
const address = computed(() => page.props.address as Address)
const role = computed(() => page.props.role as string)

const { t } = useI18n();
const breadcrumbs = computed(() => ([
    { title: t('settings.profile_details.title'), href: 'settings/parent/profile/details' },
]))

const form = useForm({
    first_name: parentProfile?.value?.first_name ?? '',
    last_name: parentProfile?.value?.last_name ?? '',
    phone: parentProfile?.value?.phone ?? '',
    birthdate: parentProfile?.value?.birthdate ?? '',
    address: address?.value ? { ...address.value } : {
        street: '',
        city: '',
        state: '',
        country: '',
        postal_code: '',
    },
})

function submit() {
    form.patch(route('parent.profile.update'), { preserveScroll: true })
}
</script>

<template>

    <Head :title="$t('settings.profile_details.title')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <SettingsLayout>
            <div class="max-w-3xl mx-auto">
                <HeadingSmall
                    :title="$t('settings.profile_details.heading.title')"
                    :description="$t('settings.profile_details.heading.description')"
                />
                <form @submit.prevent="submit">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                        <!-- First Name -->
                        <div>
                            <FloatingInput id="first_name" :label="$t('common.labels.first_name')" v-model="form.first_name" />
                            <InputError :message="form.errors.first_name" class="mt-1" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <FloatingInput id="last_name" :label="$t('common.labels.last_name')" v-model="form.last_name" />
                            <InputError :message="form.errors.last_name" class="mt-1" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <FloatingInput id="phone" :label="$t('common.labels.phone')" v-model="form.phone" />
                            <InputError :message="form.errors.phone" class="mt-1" />
                        </div>

                        <!-- Birthdate -->
                        <div>
                            <FloatingInput id="birthdate" :label="$t('common.labels.birthdate')" type="date" v-model="form.birthdate" />
                            <InputError :message="form.errors.birthdate" class="mt-1" />
                        </div>

                        <!-- Action buttons -->
                    </div>
                    <div key="address" class="mt-6">
                        <HeadingSmall
                            :title="$t('settings.profile_details.address.title')"
                            :description="$t('settings.profile_details.address.description')"
                            class="my-4"
                        />
                        <AddressForm v-model="form.address" />
                    </div>
                    <div class="mt-8 flex flex-col">
                        <Button type="submit" class="w-full my-2" :tabindex="4" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            <SaveIcon v-else /> {{ $t('settings.profile.actions.update_profile') }}
                        </Button>
                        <Button type="button" variant="outline" class="w-full" @click="form.reset()">
                            {{ $t('common.actions.reset') }}
                        </Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
