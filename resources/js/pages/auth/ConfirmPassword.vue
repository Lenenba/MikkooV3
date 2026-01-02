<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import FloatingInput from '@/components/FloatingInput.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.ui.confirm.title')" :description="$t('auth.ui.confirm.description')">
        <Head :title="$t('auth.ui.confirm.head_title')" />

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <FloatingInput
                        id="password"
                        :label="$t('auth.ui.confirm.password')"
                        type="password"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        autofocus
                    />

                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ $t('auth.ui.confirm.submit') }}
                    </Button>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
