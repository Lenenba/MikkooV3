<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthBase from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import AddressForm from '@/components/AddressForm.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    street: '',
    city: '',
    province: '',
    postal_code: '',
    country: '',
    latitude: '',
    longitude: '',
});

const addressModel = computed({
    get: () => ({
        street: form.street,
        city: form.city,
        province: form.province,
        postal_code: form.postal_code,
        country: form.country,
        latitude: form.latitude,
        longitude: form.longitude,
    }),
    set: (value) => {
        form.street = value.street ?? '';
        form.city = value.city ?? '';
        form.province = value.province ?? '';
        form.postal_code = value.postal_code ?? '';
        form.country = value.country ?? '';
        form.latitude = value.latitude ?? '';
        form.longitude = value.longitude ?? '';
    },
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase
        title="Cree ton compte"
        description="Tout se passe ici: compte + adresse pour des recherches precises."
    >
        <Head title="Onboarding" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="name">Nom complet</Label>
                    <Input id="name" type="text" required autofocus autocomplete="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Adresse email</Label>
                    <Input id="email" type="email" required autocomplete="email" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Mot de passe</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        v-model="form.password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmation</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <div class="grid gap-2">
                    <Label>Adresse</Label>
                    <AddressForm v-model="addressModel" />
                    <InputError :message="form.errors.city || form.errors.country" />
                </div>

                <Button type="submit" class="mt-2 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Creer mon compte
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Deja un compte ?
                <TextLink :href="route('login')" class="underline underline-offset-4">Se connecter</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
