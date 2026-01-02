<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import FloatingInput from '@/components/FloatingInput.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout :title="$t('auth.ui.forgot.title')" :description="$t('auth.ui.forgot.description')">
        <Head :title="$t('auth.ui.forgot.head_title')" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2">
                    <FloatingInput
                        id="email"
                        :label="$t('auth.ui.forgot.email')"
                        type="email"
                        name="email"
                        autocomplete="off"
                        v-model="form.email"
                        autofocus
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ $t('auth.ui.forgot.submit') }}
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>{{ $t('auth.ui.forgot.return_to') }}</span>
                <TextLink :href="route('login')">{{ $t('auth.ui.forgot.login') }}</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
