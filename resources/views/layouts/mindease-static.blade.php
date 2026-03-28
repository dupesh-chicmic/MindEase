<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MindEase') — MindEase</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        minease: {
                            bg: '#0c0c0f',
                            card: '#16161d',
                            'card-border': 'rgba(255, 255, 255, 0.08)',
                            muted: '#a1a1aa',
                            accent: '#a78bfa',
                        },
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-minease-bg font-sans text-white antialiased">
    <div class="mx-auto flex min-h-screen max-w-2xl flex-col px-4 pb-10 pt-6 sm:px-5 md:max-w-3xl md:px-6 lg:max-w-4xl">
        <header class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-5">
            <a href="{{ url('/') }}" class="text-lg font-bold tracking-tight text-white">MindEase</a>
            <nav class="flex flex-wrap items-center gap-1 text-sm font-medium text-minease-muted">
                <a href="{{ url('/about') }}" class="rounded-lg px-2.5 py-1.5 transition hover:bg-white/5 hover:text-white">About</a>
                <a href="{{ url('/help') }}" class="rounded-lg px-2.5 py-1.5 transition hover:bg-white/5 hover:text-white">Help</a>
                <a href="{{ url('/privacy') }}" class="rounded-lg px-2.5 py-1.5 transition hover:bg-white/5 hover:text-white">Privacy</a>
            </nav>
        </header>

        <main class="flex flex-1 flex-col gap-6">
            @yield('hero')
            @yield('content')
        </main>

        <footer class="mt-10 border-t border-white/10 pt-6 text-center text-xs text-minease-muted">
            <p>&copy; {{ date('Y') }} MindEase</p>
        </footer>
    </div>
</body>
</html>
