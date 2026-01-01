<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-svh bg-background text-foreground">
        <main class="flex min-h-svh">
            <aside class="hidden min-h-svh w-full max-w-sm flex-col justify-between bg-muted p-8 lg:flex xl:max-w-md">
                <div class="flex items-center justify-between gap-4">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <AppLogoIcon class="h-9 w-auto" />
                        <span class="text-lg font-semibold text-foreground">Mikoo</span>
                    </Link>
                    <div class="flex items-center gap-2">
                        <label class="sr-only" for="auth-language">Language</label>
                        <select
                            id="auth-language"
                            class="rounded-lg border border-input bg-background px-3 py-2 text-xs font-medium text-foreground shadow-sm focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring/20"
                        >
                            <option value="fr">Francais</option>
                            <option value="en" selected>English</option>
                            <option value="de">Deutsch</option>
                        </select>
                    </div>
                </div>

                <div class="mt-12 space-y-6">
                    <p class="text-2xl font-semibold text-foreground">
                        La maniere simple de trouver une gardienne de confiance.
                    </p>
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                        <div class="space-y-4 text-sm text-muted-foreground">
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full bg-primary"></span>
                                Profils verifies et references locales.
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full bg-primary"></span>
                                Suivi clair pendant chaque garde.
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full bg-primary"></span>
                                Planning flexible pour les parents.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-muted-foreground">
                    <span>(c) 2026 Mikoo</span>
                    <span>contact@mikoo.test</span>
                </div>
            </aside>

            <section class="flex grow px-6 py-10 sm:px-8 lg:px-12">
                <div class="mx-auto flex w-full max-w-md flex-col justify-center space-y-6">
                    <div class="flex flex-col items-start gap-4">
                        <Link :href="route('home')" class="flex items-center gap-3">
                            <AppLogoIcon class="h-10 w-auto" />
                            <span class="text-lg font-semibold text-foreground">Mikoo</span>
                        </Link>
                        <div class="space-y-2">
                            <h1 v-if="title" class="text-2xl font-semibold text-foreground">
                                {{ title }}
                            </h1>
                            <p v-if="description" class="text-sm text-muted-foreground">
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <slot />
                </div>
            </section>
        </main>
    </div>
</template>
