<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — {{ config('app.name', 'Billora') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-900" style="font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif;">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="bg-slate-950 text-slate-100">
            <div class="flex h-16 items-center gap-2 border-b border-white/10 px-6">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-teal-400 text-sm font-bold text-slate-950">B</span>
                <div>
                    <p class="text-sm font-semibold tracking-wide">{{ config('app.name', 'Billora') }}</p>
                    <p class="text-xs text-slate-400">Admin console</p>
                </div>
            </div>
            <nav class="space-y-1 p-4 text-sm">
                <a href="{{ route('admin.dashboard') }}" @class([
                    'block rounded-lg px-3 py-2',
                    'bg-white/10 text-white' => request()->routeIs('admin.dashboard'),
                    'text-slate-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.dashboard'),
                ])>Revenue overview</a>
                <a href="{{ route('admin.users.index') }}" @class([
                    'block rounded-lg px-3 py-2',
                    'bg-white/10 text-white' => request()->routeIs('admin.users.*'),
                    'text-slate-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.users.*'),
                ])>Users & plans</a>
                <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 text-slate-300 hover:bg-white/5 hover:text-white">User dashboard</a>
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-slate-300 hover:bg-white/5 hover:text-white">Public plans</a>
            </nav>
        </aside>

        <div class="min-w-0">
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-8">
                <div>
                    <h1 class="text-lg font-semibold text-slate-900">{{ $header ?? 'Admin' }}</h1>
                    @isset($subheader)
                        <p class="text-sm text-slate-500">{{ $subheader }}</p>
                    @endisset
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="hidden text-slate-500 sm:inline">{{ auth()->user()->email }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-lg bg-slate-100 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-200">Log out</button>
                    </form>
                </div>
            </header>

            <main class="p-4 sm:p-8">
                <x-flash-alerts class="mb-6" />

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
