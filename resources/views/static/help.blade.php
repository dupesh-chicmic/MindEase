@extends('layouts.mindease-static')

@section('title', 'Help & Support')

@section('hero')
    <div class="mb-6 w-full min-w-0 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:mb-8 sm:p-5 md:p-6">
        <div class="flex items-start gap-3 sm:gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-500/15 ring-1 ring-violet-500/30 sm:h-12 sm:w-12">
                <svg class="h-6 w-6 text-minease-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-bold tracking-tight text-white sm:text-2xl md:text-3xl">Help &amp; Support</h1>
                <p class="mt-1 text-sm text-minease-muted sm:text-base">FAQs and contact us</p>
            </div>
            <svg class="mt-1 hidden h-5 w-5 shrink-0 text-minease-muted/60 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>
@endsection

@section('content')
    <section class="w-full min-w-0 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:p-5 md:p-6">
        <h2 class="mb-4 text-base font-semibold text-white md:text-lg">Common questions</h2>
        <ul class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-4 xl:grid-cols-3 xl:gap-5">
            <li class="min-w-0 rounded-xl bg-minease-bg/50 p-4 ring-1 ring-white/5 sm:p-5">
                <p class="text-sm font-medium text-white sm:text-base">How do mood suggestions work?</p>
                <p class="mt-2 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                    Suggestions are generated to match your selected mood. They are supportive and meant for wellness—not a substitute for professional care.
                </p>
            </li>
            <li class="min-w-0 rounded-xl bg-minease-bg/50 p-4 ring-1 ring-white/5 sm:p-5">
                <p class="text-sm font-medium text-white sm:text-base">Is my data private?</p>
                <p class="mt-2 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                    We take privacy seriously. See the <a href="{{ url('/privacy') }}" class="font-medium text-minease-accent underline decoration-minease-accent/40 underline-offset-2 hover:decoration-minease-accent">Privacy</a> page for details.
                </p>
            </li>
            <li class="min-w-0 rounded-xl bg-minease-bg/50 p-4 ring-1 ring-white/5 sm:p-5 lg:col-span-2 xl:col-span-1">
                <p class="text-sm font-medium text-white sm:text-base">API or app issues?</p>
                <p class="mt-2 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                    Check your connection, update the app, and ensure you are signed in. If something persists, contact us with your device model and steps to reproduce.
                </p>
            </li>
        </ul>
    </section>

    <section class="w-full min-w-0 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:p-5 md:p-6">
        <h2 class="text-base font-semibold text-white md:text-lg">Contact</h2>
        <p class="mt-3 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
            For support, email <a href="mailto:support@mindease.app" class="font-medium text-minease-accent underline-offset-2 hover:underline">support@mindease.app</a> (replace with your real address). We try to respond within a few business days.
        </p>
    </section>

    <a href="{{ url('/about') }}" class="flex min-h-12 w-full min-w-0 items-center justify-between gap-3 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border transition hover:ring-minease-accent/25 active:scale-[0.99] sm:gap-4 sm:p-5">
        <div class="flex min-w-0 flex-1 items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-500/15 ring-1 ring-zinc-500/25 sm:h-11 sm:w-11">
                <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white sm:text-base">About MindEase</p>
                <p class="truncate text-xs text-minease-muted sm:text-sm">Version &amp; mission</p>
            </div>
        </div>
        <svg class="h-5 w-5 shrink-0 text-minease-muted/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
@endsection
