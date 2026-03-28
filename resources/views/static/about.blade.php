@extends('layouts.mindease-static')

@section('title', 'About')

@section('hero')
    <div class="mb-6 w-full min-w-0 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:mb-8 sm:p-5 md:p-6">
        <div class="flex items-start gap-3 sm:gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-zinc-500/15 ring-1 ring-zinc-500/30 sm:h-12 sm:w-12">
                <svg class="h-6 w-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-bold tracking-tight text-white sm:text-2xl md:text-3xl">About MindEase</h1>
                <p class="mt-1 text-sm text-minease-muted sm:text-base">Version 1.0.0</p>
            </div>
            <svg class="mt-1 hidden h-5 w-5 shrink-0 text-minease-muted/60 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>

    <div class="mb-6 flex w-full flex-col items-center text-center sm:mb-8">
        <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-minease-accent text-3xl font-bold text-white shadow-lg shadow-violet-500/20 ring-4 ring-minease-card sm:h-28 sm:w-28 sm:text-4xl md:h-32 md:w-32 md:text-5xl">
            M
        </div>
        <p class="mt-4 max-w-md px-2 text-sm leading-relaxed text-minease-muted sm:max-w-lg sm:text-base md:max-w-xl">
            Calm, clarity, and care—built for real life.
        </p>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-2 md:gap-4 lg:gap-5">
        <section class="min-w-0 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:p-5 md:p-6">
            <h2 class="text-base font-semibold text-white md:text-lg">Our mission</h2>
            <p class="mt-3 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                MindEase helps you check in with your mood, discover small actions that feel doable, and find encouragement in a Gen-Z friendly, Hinglish-friendly voice. We believe tiny steps and honest reflection can make heavy days lighter.
            </p>
        </section>

        <section class="min-w-0 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:p-5 md:p-6">
            <h2 class="text-base font-semibold text-white md:text-lg">Wellness note</h2>
            <p class="mt-3 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                MindEase is not a medical service. If you are in crisis or need urgent help, please contact local emergency services or a trusted professional.
            </p>
        </section>
    </div>

    <section class="mt-4 w-full min-w-0 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:mt-5 sm:p-5 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-minease-muted">Build</p>
                <p class="mt-1 text-lg font-semibold text-white sm:text-xl">1.0.0</p>
            </div>
            <div class="inline-flex w-fit max-w-full rounded-xl bg-minease-bg/60 px-3 py-2 text-xs font-medium text-minease-accent ring-1 ring-minease-accent/30 sm:text-sm">
                ✨ Wellness Explorer
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
        <a href="{{ url('/help') }}" class="flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-minease-card-border bg-minease-card px-4 py-3.5 text-center text-sm font-semibold text-minease-accent ring-1 ring-white/5 transition hover:border-minease-accent/40 active:scale-[0.99] sm:text-base">
            Help &amp; Support
        </a>
        <a href="{{ url('/privacy') }}" class="flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-emerald-500/30 bg-minease-card px-4 py-3.5 text-center text-sm font-semibold text-emerald-400/90 ring-1 ring-white/5 transition hover:border-emerald-400/50 active:scale-[0.99] sm:text-base">
            Privacy
        </a>
    </div>
@endsection
