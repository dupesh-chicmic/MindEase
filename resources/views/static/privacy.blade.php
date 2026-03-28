@extends('layouts.mindease-static')

@section('title', 'Privacy')

@section('hero')
    <div class="mb-6 w-full min-w-0 rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:mb-8 sm:p-5 md:p-6">
        <div class="flex items-start gap-3 sm:gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 ring-1 ring-emerald-500/30 sm:h-12 sm:w-12">
                <svg class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-bold tracking-tight text-white sm:text-2xl md:text-3xl">Privacy</h1>
                <p class="mt-1 text-sm text-minease-muted sm:text-base">Your data is encrypted</p>
            </div>
            <svg class="mt-1 hidden h-5 w-5 shrink-0 text-minease-muted/60 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-2 md:gap-4 lg:gap-5">
        <section class="rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:p-5 md:p-6">
            <h2 class="text-base font-semibold text-white md:text-lg">What we collect</h2>
            <p class="mt-3 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                MindEase is designed to support your wellness journey. We only collect information needed to run the service—such as account details you provide, app usage needed for stability, and mood-related inputs you choose to save—always with security in mind.
            </p>
        </section>

        <section class="rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:p-5 md:p-6">
            <h2 class="text-base font-semibold text-white md:text-lg">How we use data</h2>
            <p class="mt-3 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                Data is used to personalize suggestions, improve reliability, and keep your account safe. We do not sell your personal information. AI features may process prompts you send to generate supportive content, according to our providers’ terms.
            </p>
        </section>

        <section class="rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:p-5 md:p-6">
            <h2 class="text-base font-semibold text-white md:text-lg">Security</h2>
            <p class="mt-3 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                We use industry-standard practices including encryption in transit (HTTPS) and careful access controls. No method is 100% secure; please use a strong password and protect your device.
            </p>
        </section>

        <section class="rounded-2xl bg-minease-card p-4 ring-1 ring-minease-card-border sm:p-5 md:p-6">
            <h2 class="text-base font-semibold text-white md:text-lg">Your choices</h2>
            <p class="mt-3 break-words text-sm leading-relaxed text-minease-muted sm:text-base">
                You can request access, correction, or deletion of your account data where applicable. Contact us through Help &amp; Support for privacy-related requests.
            </p>
        </section>
    </div>

    <p class="px-1 pt-2 text-center text-xs leading-relaxed text-minease-muted/80 sm:text-sm">
        Last updated {{ now()->format('F j, Y') }}. This page is a static summary; your final policy may differ for production.
    </p>
@endsection
