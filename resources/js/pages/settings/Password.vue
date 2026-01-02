<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import HeadingSmall from '@/components/HeadingSmall.vue';
import FloatingInput from '@/components/FloatingInput.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';

const { t } = useI18n();
const breadcrumbItems = computed<BreadcrumbItem[]>(() => ([
    {
        title: t('settings.password.title'),
        href: '/settings/password',
    },
]));

const passwordInput = ref<{ focus: () => void } | null>(null);
const currentPasswordInput = ref<{ focus: () => void } | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }

            if (errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="$t('settings.password.title')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="$t('settings.password.heading.title')"
                    :description="$t('settings.password.heading.description')"
                />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <FloatingInput
                            id="current_password"
                            ref="currentPasswordInput"
                            :label="$t('settings.password.labels.current_password')"
                            v-model="form.current_password"
                            type="password"
                            autocomplete="current-password"
                        />
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <FloatingInput
                            id="password"
                            ref="passwordInput"
                            :label="$t('settings.password.labels.new_password')"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <FloatingInput
                            id="password_confirmation"
                            :label="$t('settings.password.labels.confirm_password')"
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ $t('settings.password.actions.save') }}</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-muted-foreground">
                                {{ $t('common.misc.saved') }}
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
