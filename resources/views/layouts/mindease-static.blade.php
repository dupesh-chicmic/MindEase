<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="dark">
    <title>@yield('title') — MindEase</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        minease: {
                            bg: '#0b0c16',
                            card: '#1a1b2e',
                            'card-border': '#2d2e42',
                            accent: '#a78bfa',
                            muted: '#9ca3af',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }
        body { font-family: Inter, system-ui, sans-serif; }
    </style>
    @stack('head')
</head>
<body class="min-h-full overflow-x-hidden bg-minease-bg text-white antialiased">
    {{-- Outer: full width, horizontal padding scales; inner: max-width grows with breakpoint (no shrink at lg) --}}
    <div class="mx-auto flex min-h-full w-full max-w-full flex-col px-3 pt-[max(1rem,env(safe-area-inset-top))] pb-[max(2.5rem,env(safe-area-inset-bottom))] min-[400px]:px-4 sm:px-6 md:px-8 lg:px-10 xl:px-12">
        <div class="mx-auto w-full max-w-lg sm:max-w-xl md:max-w-2xl lg:max-w-3xl xl:max-w-4xl">
            <header class="mb-6 flex items-center gap-3 sm:mb-8 md:mb-10">
                <a href="{{ url('/') }}" class="flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-xl bg-minease-card text-minease-accent ring-1 ring-minease-card-border transition hover:bg-minease-card-border active:scale-[0.98]" aria-label="Back to home">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span class="min-w-0 truncate text-sm font-medium text-minease-muted sm:text-base">MindEase</span>
            </header>

            @yield('hero')

            <main class="w-full min-w-0 space-y-4 sm:space-y-5">
                @yield('content')
            </main>

            <footer class="mt-8 flex w-full justify-center sm:mt-10 md:mt-12">
                <a href="{{ url('/') }}" class="inline-flex w-full max-w-full min-h-12 items-center justify-center gap-2 rounded-2xl border border-minease-card-border bg-minease-card px-5 py-3.5 text-sm font-semibold text-minease-accent ring-1 ring-white/5 transition hover:border-minease-accent/40 hover:ring-minease-accent/20 active:scale-[0.99] sm:w-auto sm:min-w-[220px] md:text-base">
                    Back to home
                </a>
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
