<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import FloatingInput from '@/components/FloatingInput.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.ui.reset.title')" :description="$t('auth.ui.reset.description')">
        <Head :title="$t('auth.ui.reset.head_title')" />

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <FloatingInput
                        id="email"
                        :label="$t('auth.ui.reset.email')"
                        type="email"
                        name="email"
                        autocomplete="email"
                        v-model="form.email"
                        readonly
                    />
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <div class="grid gap-2">
                    <FloatingInput
                        id="password"
                        :label="$t('auth.ui.reset.password')"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        v-model="form.password"
                        autofocus
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <FloatingInput
                        id="password_confirmation"
                        :label="$t('auth.ui.reset.password_confirm')"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ $t('auth.ui.reset.submit') }}
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
