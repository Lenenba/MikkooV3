<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>

    <Head :title="$t('welcome.head_title')">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&display=swap" />
    </Head>

    <div class="welcome relative min-h-screen overflow-hidden">
        <div class="absolute -top-24 right-[-10%] h-80 w-80 rounded-full blur-3xl opacity-70"
            style="background: var(--peach)"></div>
        <div class="absolute top-32 -left-24 h-72 w-72 rounded-full blur-3xl opacity-70"
            style="background: var(--mint)"></div>
        <div class="absolute bottom-[-10rem] right-16 h-96 w-96 rounded-full blur-3xl opacity-70"
            style="background: var(--primary-soft)"></div>
        <div class="absolute bottom-24 left-16 h-32 w-32 rounded-full blur-2xl opacity-80"
            style="background: var(--sun)"></div>

        <header class="relative z-10">
            <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 pt-6">
                <div class="flex items-center">
                    <AppLogoIcon class="h-10 w-auto" />
                </div>

                <nav class="hidden items-center gap-6 text-sm font-semibold text-slate-600 lg:flex">
                    <a class="nav-link" href="#services">{{ $t('welcome.nav.services') }}</a>
                    <a class="nav-link" href="#approche">{{ $t('welcome.nav.approach') }}</a>
                    <a class="nav-link" href="#process">{{ $t('welcome.nav.process') }}</a>
                    <a class="nav-link" href="#avis">{{ $t('welcome.nav.reviews') }}</a>
                </nav>

                <div class="flex items-center gap-2">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="btn btn-outline px-4 py-2 text-sm">
                        {{ $t('welcome.actions.dashboard') }}
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn-ghost px-4 py-2 text-sm">
                            {{ $t('welcome.actions.login') }}
                        </Link>
                        <Link :href="route('register')" class="btn btn-primary px-4 py-2 text-sm">
                            {{ $t('welcome.actions.register') }}
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="relative z-10">
            <section class="mx-auto w-full max-w-6xl px-6 pb-16 pt-12 lg:pb-24 lg:pt-20">
                <div class="grid gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                    <div class="space-y-6">
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-white/70 px-4 py-1.5 text-xs font-semibold text-slate-600 shadow-sm">
                            <span class="h-2 w-2 rounded-full" style="background: var(--primary)"></span>
                            {{ $t('welcome.hero.pill') }}
                        </div>
                        <h1
                            class="font-display text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl fade-up">
                            {{ $t('welcome.hero.title') }}
                        </h1>
                        <p class="max-w-xl text-lg text-slate-600 fade-up" style="animation-delay: 0.1s">
                            {{ $t('welcome.hero.description') }}
                        </p>
                        <div class="flex flex-wrap gap-3 fade-up" style="animation-delay: 0.2s">
                            <template v-if="!$page.props.auth.user">
                                <Link :href="route('register')" class="btn btn-primary px-6 py-3 text-sm">
                                    {{ $t('welcome.actions.find_babysitter') }}
                                </Link>
                            </template>
                            <template v-else>
                                <Link :href="route('dashboard')" class="btn btn-primary px-6 py-3 text-sm">
                                    {{ $t('welcome.actions.open_space') }}
                                </Link>
                            </template>
                            <a href="#process" class="btn btn-outline px-6 py-3 text-sm">
                                {{ $t('welcome.actions.see_process') }}
                            </a>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 fade-up" style="animation-delay: 0.3s">
                            <div class="feature-pill">
                                <span class="feature-icon">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                        <path d="M4 10l4 4 8-8" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.hero.features.verified.title') }}</p>
                                    <p class="text-xs text-slate-500">{{ $t('welcome.hero.features.verified.description') }}</p>
                                </div>
                            </div>
                            <div class="feature-pill">
                                <span class="feature-icon">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                        <path d="M4 10l4 4 8-8" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.hero.features.matching.title') }}</p>
                                    <p class="text-xs text-slate-500">{{ $t('welcome.hero.features.matching.description') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-6 text-sm text-slate-500 fade-up"
                            style="animation-delay: 0.4s">
                            <div class="flex -space-x-2">
                                <span class="avatar">EL</span>
                                <span class="avatar">MA</span>
                                <span class="avatar">SO</span>
                            </div>
                            <div>
                                <p class="text-base font-semibold text-slate-700">{{ $t('welcome.hero.stats.headline') }}</p>
                                <p class="text-xs text-slate-500">{{ $t('welcome.hero.stats.subline') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative fade-up" style="animation-delay: 0.2s">
                        <div class="hero-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $t('welcome.hero.card.today') }}</p>
                                    <p class="font-display text-2xl text-slate-900">{{ $t('welcome.hero.card.available') }}</p>
                                </div>
                                <div class="rounded-full px-3 py-1 text-xs font-semibold"
                                    style="background: var(--primary-soft); color: var(--primary-strong)">
                                    {{ $t('welcome.hero.card.time') }}
                                </div>
                            </div>
                            <div class="mt-6 rounded-3xl bg-white/80 p-5 shadow-lg shadow-black/10">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-sm font-semibold"
                                        style="background: var(--sun); color: #6b3d00">
                                        LM
                                    </div>
                                    <div>
                                        <p class="text-base font-semibold text-slate-800">{{ $t('welcome.hero.card.name') }}</p>
                                        <p class="text-xs text-slate-500">{{ $t('welcome.hero.card.experience') }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-3 gap-2 text-xs text-slate-600">
                                    <span class="rounded-full px-2 py-1 text-center"
                                        style="background: var(--primary-soft)">
                                        {{ $t('welcome.hero.card.tags.creative') }}
                                    </span>
                                    <span class="rounded-full px-2 py-1 text-center"
                                        style="background: var(--primary-soft)">
                                        {{ $t('welcome.hero.card.tags.calm') }}
                                    </span>
                                    <span class="rounded-full px-2 py-1 text-center"
                                        style="background: var(--primary-soft)">
                                        {{ $t('welcome.hero.card.tags.bilingual') }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-6 grid gap-4">
                                <div class="rounded-3xl bg-white/90 p-4 shadow-lg shadow-black/5">
                                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $t('welcome.hero.card.activities.title') }}</p>
                                    <p class="mt-2 text-sm text-slate-600">{{ $t('welcome.hero.card.activities.description') }}</p>
                                </div>
                                <div class="rounded-3xl p-4 text-white shadow-lg" style="background: var(--primary)">
                                    <p class="text-xs uppercase tracking-[0.2em] text-white/80">{{ $t('welcome.hero.card.parents.title') }}</p>
                                    <p class="mt-2 text-sm">{{ $t('welcome.hero.card.parents.description') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="floating-card hidden lg:flex">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $t('welcome.hero.card.next.title') }}</p>
                                <p class="font-display text-lg text-slate-900">{{ $t('welcome.hero.card.next.time') }}</p>
                            </div>
                            <span class="badge">{{ $t('welcome.hero.card.next.status') }}</span>
                        </div>
                    </div>
                </div>
            </section>
            <section id="services" class="relative z-10 bg-white/70">
                <div class="mx-auto w-full max-w-6xl px-6 py-16 lg:py-20">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="section-eyebrow">{{ $t('welcome.services.eyebrow') }}</p>
                            <h2 class="font-display text-3xl text-slate-900 sm:text-4xl">{{ $t('welcome.services.title') }}</h2>
                        </div>
                        <p class="max-w-xl text-sm text-slate-500">
                            {{ $t('welcome.services.description') }}
                        </p>
                    </div>
                    <div class="mt-10 grid gap-6 md:grid-cols-3">
                        <div class="service-card">
                            <div class="service-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                                    <circle cx="12" cy="12" r="4" />
                                    <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4" />
                                    <path d="M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                                </svg>
                            </div>
                            <h3 class="font-display text-xl text-slate-900">{{ $t('welcome.services.cards.after_school.title') }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $t('welcome.services.cards.after_school.description') }}</p>
                        </div>
                        <div class="service-card">
                            <div class="service-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                                    <path d="M21 14.5A8.5 8.5 0 1 1 9.5 3a7 7 0 0 0 11.5 11.5Z" />
                                    <path d="M15.5 8.5h0.01M18 10h0.01" />
                                </svg>
                            </div>
                            <h3 class="font-display text-xl text-slate-900">{{ $t('welcome.services.cards.evening.title') }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $t('welcome.services.cards.evening.description') }}</p>
                        </div>
                        <div class="service-card">
                            <div class="service-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                                    <rect x="4" y="5" width="16" height="15" rx="2" />
                                    <path d="M8 3v4M16 3v4M4 10h16" />
                                </svg>
                            </div>
                            <h3 class="font-display text-xl text-slate-900">{{ $t('welcome.services.cards.regular.title') }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $t('welcome.services.cards.regular.description') }}</p>
                        </div>
                    </div>
                </div>
            </section>
            <section id="approche" class="mx-auto w-full max-w-6xl px-6 py-16 lg:py-20">
                <div class="grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                    <div class="approach-panel">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $t('welcome.approach.panel.eyebrow') }}</p>
                                <p class="font-display text-2xl text-slate-900">{{ $t('welcome.approach.panel.title') }}</p>
                            </div>
                            <span class="badge">{{ $t('welcome.approach.panel.badge') }}</span>
                        </div>
                        <div class="mt-6 grid gap-4">
                            <div class="approach-card">
                                <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.approach.panel.cards.checkin.title') }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ $t('welcome.approach.panel.cards.checkin.description') }}</p>
                            </div>
                            <div class="approach-card">
                                <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.approach.panel.cards.moments.title') }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ $t('welcome.approach.panel.cards.moments.description') }}</p>
                            </div>
                            <div class="approach-card">
                                <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.approach.panel.cards.recap.title') }}</p>
                                <p class="mt-2 text-xs text-slate-500">{{ $t('welcome.approach.panel.cards.recap.description') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <p class="section-eyebrow">{{ $t('welcome.approach.eyebrow') }}</p>
                        <h2 class="font-display text-3xl text-slate-900 sm:text-4xl">
                            {{ $t('welcome.approach.title') }}
                        </h2>
                        <p class="text-sm text-slate-600">
                            {{ $t('welcome.approach.description') }}
                        </p>
                        <div class="grid gap-4">
                            <div class="flex items-start gap-4">
                                <span class="feature-icon">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                        <path d="M4 10l4 4 8-8" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.approach.features.safety.title') }}</p>
                                    <p class="text-xs text-slate-500">{{ $t('welcome.approach.features.safety.description') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <span class="feature-icon">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                        <path d="M4 10l4 4 8-8" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.approach.features.communication.title') }}</p>
                                    <p class="text-xs text-slate-500">{{ $t('welcome.approach.features.communication.description') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <span class="feature-icon">
                                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                        <path d="M4 10l4 4 8-8" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.approach.features.activities.title') }}</p>
                                    <p class="text-xs text-slate-500">{{ $t('welcome.approach.features.activities.description') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="#avis" class="btn btn-outline px-6 py-3 text-sm">{{ $t('welcome.actions.read_reviews') }}</a>
                            <template v-if="!$page.props.auth.user">
                                <Link :href="route('register')" class="btn btn-primary px-6 py-3 text-sm">
                                    {{ $t('welcome.actions.start_now') }}
                                </Link>
                            </template>
                            <template v-else>
                                <Link :href="route('dashboard')" class="btn btn-primary px-6 py-3 text-sm">
                                    {{ $t('welcome.actions.go_dashboard') }}
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </section>
            <section id="process" class="bg-white/70">
                <div class="mx-auto w-full max-w-6xl px-6 py-16 lg:py-20">
                    <div class="mx-auto max-w-2xl text-center">
                        <p class="section-eyebrow">{{ $t('welcome.process.eyebrow') }}</p>
                        <h2 class="font-display text-3xl text-slate-900 sm:text-4xl">{{ $t('welcome.process.title') }}</h2>
                        <p class="mt-4 text-sm text-slate-500">
                            {{ $t('welcome.process.description') }}
                        </p>
                    </div>
                    <div class="mt-10 grid gap-6 md:grid-cols-3">
                        <div class="step-card">
                            <span class="step-number">1</span>
                            <h3 class="mt-4 font-display text-xl text-slate-900">{{ $t('welcome.process.steps.one.title') }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $t('welcome.process.steps.one.description') }}</p>
                        </div>
                        <div class="step-card">
                            <span class="step-number">2</span>
                            <h3 class="mt-4 font-display text-xl text-slate-900">{{ $t('welcome.process.steps.two.title') }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $t('welcome.process.steps.two.description') }}</p>
                        </div>
                        <div class="step-card">
                            <span class="step-number">3</span>
                            <h3 class="mt-4 font-display text-xl text-slate-900">{{ $t('welcome.process.steps.three.title') }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $t('welcome.process.steps.three.description') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="avis" class="mx-auto w-full max-w-6xl px-6 py-16 lg:py-20">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="section-eyebrow">{{ $t('welcome.reviews.eyebrow') }}</p>
                        <h2 class="font-display text-3xl text-slate-900 sm:text-4xl">{{ $t('welcome.reviews.title') }}</h2>
                    </div>
                    <p class="max-w-xl text-sm text-slate-500">
                        {{ $t('welcome.reviews.description') }}
                    </p>
                </div>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <div class="testimonial-card">
                        <p class="text-sm text-slate-600">
                            {{ $t('welcome.reviews.items.first.quote') }}
                        </p>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.reviews.items.first.name') }}</p>
                            <span class="text-xs text-slate-500">{{ $t('welcome.reviews.items.first.rating') }}</span>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <p class="text-sm text-slate-600">
                            {{ $t('welcome.reviews.items.second.quote') }}
                        </p>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.reviews.items.second.name') }}</p>
                            <span class="text-xs text-slate-500">{{ $t('welcome.reviews.items.second.rating') }}</span>
                        </div>
                    </div>
                    <div class="testimonial-card">
                        <p class="text-sm text-slate-600">
                            {{ $t('welcome.reviews.items.third.quote') }}
                        </p>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-800">{{ $t('welcome.reviews.items.third.name') }}</p>
                            <span class="text-xs text-slate-500">{{ $t('welcome.reviews.items.third.rating') }}</span>
                        </div>
                    </div>
                </div>
            </section>
            <section class="mx-auto w-full max-w-6xl px-6 pb-16">
                <div class="cta-block">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="section-eyebrow">{{ $t('welcome.cta.eyebrow') }}</p>
                            <h2 class="font-display text-3xl text-slate-900 sm:text-4xl">{{ $t('welcome.cta.title') }}</h2>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ $t('welcome.cta.description') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <template v-if="!$page.props.auth.user">
                                <Link :href="route('register')" class="btn btn-primary px-6 py-3 text-sm">
                                    {{ $t('welcome.cta.actions.start') }}
                                </Link>
                                <Link :href="route('login')" class="btn btn-outline px-6 py-3 text-sm">
                                    {{ $t('welcome.cta.actions.login') }}
                                </Link>
                            </template>
                            <template v-else>
                                <Link :href="route('dashboard')" class="btn btn-primary px-6 py-3 text-sm">
                                    {{ $t('welcome.cta.actions.go_dashboard') }}
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-white/60">
            <div
                class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-6 py-10 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center">
                    <AppLogoIcon class="h-8 w-auto" />
                </div>
                <div class="flex flex-wrap gap-6 text-sm text-slate-500">
                    <a class="nav-link" href="#services">{{ $t('welcome.nav.services') }}</a>
                    <a class="nav-link" href="#approche">{{ $t('welcome.nav.approach') }}</a>
                    <a class="nav-link" href="#process">{{ $t('welcome.nav.process') }}</a>
                    <a class="nav-link" href="#avis">{{ $t('welcome.nav.reviews') }}</a>
                </div>
                <p class="text-xs text-slate-400">{{ $t('welcome.footer.copyright') }}</p>
            </div>
        </footer>
    </div>
</template>
<style scoped>
.welcome {
    --primary: #cb6ce6;
    --primary-strong: #b255cf;
    --primary-soft: #f5e7fb;
    --ink: #1f1632;
    --peach: #ffd9c9;
    --mint: #cceee5;
    --sun: #ffe8a3;
    background: linear-gradient(180deg, #fdf7f3 0%, #f7f3ff 50%, #fdf7f3 100%);
    color: var(--ink);
    font-family: 'Nunito', sans-serif;
}

.font-display {
    font-family: 'Fredoka', 'Nunito', sans-serif;
}

.nav-link {
    position: relative;
    color: #64748b;
    transition: color 0.2s ease;
}

.nav-link::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -6px;
    height: 2px;
    width: 0;
    background: var(--primary);
    border-radius: 999px;
    transition: width 0.2s ease;
}

.nav-link:hover,
.nav-link:focus-visible {
    color: #1f1632;
}

.nav-link:hover::after,
.nav-link:focus-visible::after {
    width: 100%;
}

.nav-link:focus-visible {
    outline: 2px solid rgba(203, 108, 230, 0.4);
    outline-offset: 4px;
    border-radius: 999px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 999px;
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
}

.btn-primary {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 16px 30px rgba(203, 108, 230, 0.35);
}

.btn-primary:hover {
    background: var(--primary-strong);
    transform: translateY(-1px);
    box-shadow: 0 18px 36px rgba(203, 108, 230, 0.45);
}

.btn-outline {
    background: #fff;
    border: 1px solid rgba(203, 108, 230, 0.25);
    color: #3c2b52;
    box-shadow: 0 10px 22px rgba(31, 22, 50, 0.08);
}

.btn-outline:hover {
    border-color: rgba(203, 108, 230, 0.45);
    transform: translateY(-1px);
}

.btn-ghost {
    color: #6b7280;
}

.btn-ghost:hover {
    color: #1f1632;
}

.feature-pill {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.8);
    box-shadow: 0 12px 26px rgba(31, 22, 50, 0.08);
}

.feature-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 12px;
    background: var(--primary-soft);
    color: var(--primary-strong);
}

.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: #fff;
    color: #5b3b78;
    font-weight: 700;
    box-shadow: 0 8px 18px rgba(31, 22, 50, 0.12);
    border: 2px solid #fff;
}

.hero-card {
    position: relative;
    border-radius: 32px;
    padding: 28px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 244, 250, 0.95) 100%);
    border: 1px solid rgba(203, 108, 230, 0.16);
    box-shadow: 0 30px 60px rgba(28, 15, 45, 0.18);
    overflow: hidden;
    animation: float 7s ease-in-out infinite;
}

.hero-card::after {
    content: '';
    position: absolute;
    top: -40px;
    right: -30px;
    width: 140px;
    height: 140px;
    background: radial-gradient(circle, rgba(203, 108, 230, 0.28), transparent 70%);
    border-radius: 999px;
}

.floating-card {
    position: absolute;
    right: -10px;
    bottom: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 20px 40px rgba(31, 22, 50, 0.18);
    animation: float 6s ease-in-out infinite;
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: var(--primary-soft);
    color: var(--primary-strong);
    font-size: 11px;
    font-weight: 700;
}

.section-eyebrow {
    font-size: 11px;
    letter-spacing: 0.34em;
    text-transform: uppercase;
    color: #9ca3af;
    font-weight: 700;
}

.service-card {
    padding: 24px;
    border-radius: 26px;
    background: #fff;
    box-shadow: 0 18px 40px rgba(31, 22, 50, 0.08);
    border: 1px solid rgba(203, 108, 230, 0.12);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 22px 50px rgba(31, 22, 50, 0.12);
}

.service-icon {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    background: var(--primary-soft);
    color: var(--primary-strong);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.approach-panel {
    border-radius: 32px;
    background: linear-gradient(160deg, rgba(255, 255, 255, 0.85), rgba(255, 230, 245, 0.75));
    border: 1px solid rgba(203, 108, 230, 0.15);
    padding: 26px;
    box-shadow: 0 24px 60px rgba(31, 22, 50, 0.12);
}

.approach-card {
    border-radius: 28px;
    background: #fff;
    padding: 22px;
    box-shadow: 0 18px 36px rgba(31, 22, 50, 0.1);
    border: 1px solid rgba(203, 108, 230, 0.12);
}

.step-card {
    border-radius: 24px;
    padding: 22px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(203, 108, 230, 0.12);
    box-shadow: 0 16px 36px rgba(31, 22, 50, 0.08);
}

.step-number {
    width: 36px;
    height: 36px;
    border-radius: 999px;
    background: var(--primary);
    color: #fff;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.testimonial-card {
    border-radius: 26px;
    padding: 24px;
    background: #fff;
    border: 1px solid rgba(203, 108, 230, 0.12);
    box-shadow: 0 20px 44px rgba(31, 22, 50, 0.08);
}

.cta-block {
    border-radius: 36px;
    padding: 32px;
    background: linear-gradient(120deg, rgba(203, 108, 230, 0.18), rgba(255, 215, 199, 0.35));
    border: 1px solid rgba(203, 108, 230, 0.2);
    box-shadow: 0 24px 60px rgba(31, 22, 50, 0.12);
}

.fade-up {
    animation: fadeUp 0.8s ease-out both;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-12px);
    }
}

@media (max-width: 1024px) {
    .floating-card {
        position: static;
        margin-top: 18px;
    }

    .hero-card {
        animation: none;
    }
}

@media (prefers-reduced-motion: reduce) {

    .fade-up,
    .hero-card,
    .floating-card {
        animation: none;
    }
}
</style>
