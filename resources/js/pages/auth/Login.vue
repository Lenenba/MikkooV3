<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import FloatingInput from '@/components/FloatingInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase :title="$t('auth.ui.login.title')" :description="$t('auth.ui.login.description')">

        <Head :title="$t('auth.ui.login.head_title')" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <FloatingInput id="email" :label="$t('auth.ui.login.email')" type="email" required autofocus :tabindex="1"
                        autocomplete="email" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <FloatingInput id="password" :label="$t('auth.ui.login.password')" type="password" required :tabindex="2"
                        autocomplete="current-password" v-model="form.password" />
                    <InputError :message="form.errors.password" />
                </div>
                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model="form.remember" :tabindex="3" />
                        <span>{{ $t('auth.ui.login.remember') }}</span>
                    </Label>
                    <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                        {{ $t('auth.ui.login.forgot') }}
                    </TextLink>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    {{ $t('auth.ui.login.submit') }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ $t('auth.ui.login.no_account') }}
                <TextLink :href="route('register')" :tabindex="5">{{ $t('auth.ui.login.signup') }}</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
