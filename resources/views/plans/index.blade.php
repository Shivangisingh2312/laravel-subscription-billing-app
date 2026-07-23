<x-marketing-layout>
    @php
        $authUser = auth()->user();
    @endphp

    <div class="min-h-screen bg-slate-950 text-white">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-teal-700/40 via-slate-950 to-slate-950"></div>
            <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="text-2xl font-semibold tracking-tight">
                        {{ config('app.name', 'Billora') }}
                    </a>
                    <div class="flex items-center gap-3 text-sm">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-white/10 px-3 py-2 hover:bg-white/15">Dashboard</a>
                            @if ($authUser->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-teal-500 px-3 py-2 font-medium text-slate-950 hover:bg-teal-400">Admin</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 hover:bg-white/10">Log in</a>
                            <a href="{{ route('register') }}" class="rounded-lg bg-teal-500 px-3 py-2 font-medium text-slate-950 hover:bg-teal-400">Get started</a>
                        @endauth
                    </div>
                </div>

                <div class="mx-auto max-w-3xl py-16 text-center sm:py-24">
                    <p class="text-sm uppercase tracking-[0.25em] text-teal-300">Subscription billing</p>
                    <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">
                        {{ config('app.name', 'Billora') }}
                    </h1>
                    <p class="mt-4 text-lg text-slate-300">
                        Choose a plan, start a 14-day free trial, and manage upgrades, invoices, and receipts in one place.
                    </p>
                </div>
            </div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 8000)"
                    role="alert"
                    class="mb-6 flex items-start justify-between gap-3 rounded-lg border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100"
                >
                    <p>{{ session('success') }}</p>
                    <button type="button" @click="show = false" class="text-emerald-200 hover:text-white" aria-label="Dismiss">×</button>
                </div>
            @endif

            @if (session('error'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    role="alert"
                    class="mb-6 flex items-start justify-between gap-3 rounded-lg border border-rose-400/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100"
                >
                    <p>{{ session('error') }}</p>
                    <button type="button" @click="show = false" class="text-rose-200 hover:text-white" aria-label="Dismiss">×</button>
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-lg border border-rose-400/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-2 list-disc space-y-1 ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    @php
                        $isCurrent = $currentPlan?->is($plan);
                    @endphp
                    <div @class([
                        'rounded-2xl border p-6 shadow-xl backdrop-blur',
                        'border-teal-400/60 bg-teal-500/10 ring-1 ring-teal-400/40' => $plan->slug === 'pro',
                        'border-white/10 bg-white/5' => $plan->slug !== 'pro',
                    ])>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-2xl font-semibold">{{ $plan->name }}</h2>
                                <p class="mt-2 text-sm text-slate-300">{{ $plan->description }}</p>
                            </div>
                            @if ($plan->slug === 'pro')
                                <span class="rounded-full bg-teal-400 px-2.5 py-1 text-xs font-semibold text-slate-950">Popular</span>
                            @endif
                        </div>

                        <div class="mt-6 space-y-1">
                            <p class="text-3xl font-semibold">{{ $plan->formattedPrice('monthly') }}<span class="text-base font-normal text-slate-400">/mo</span></p>
                            <p class="text-sm text-slate-400">or {{ $plan->formattedPrice('yearly') }}/year</p>
                        </div>

                        <ul class="mt-6 space-y-2 text-sm text-slate-200">
                            @foreach ($plan->features ?? [] as $feature)
                                <li class="flex gap-2">
                                    <span class="text-teal-300">✓</span>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8 space-y-3">
                            @auth
                                @if ($hasSubscription)
                                    <form method="POST" action="{{ route('subscriptions.update') }}" class="space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                        <div class="grid grid-cols-2 gap-2">
                                            <button name="interval" value="monthly" type="submit" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">
                                                {{ $isCurrent && $currentInterval === 'monthly' ? 'Current monthly' : 'Switch monthly' }}
                                            </button>
                                            <button name="interval" value="yearly" type="submit" class="rounded-lg bg-teal-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-teal-400">
                                                {{ $isCurrent && $currentInterval === 'yearly' ? 'Current yearly' : 'Switch yearly' }}
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('subscriptions.store') }}" class="space-y-2">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                        <div class="grid grid-cols-2 gap-2">
                                            <button name="interval" value="monthly" type="submit" class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">
                                                Start monthly
                                            </button>
                                            <button name="interval" value="yearly" type="submit" class="rounded-lg bg-teal-500 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-teal-400">
                                                Start yearly
                                            </button>
                                        </div>
                                    </form>
                                    <p class="text-center text-xs text-slate-400">Includes a 14-day free trial on your first subscription.</p>
                                @endif
                            @else
                                <a href="{{ route('register') }}" class="block rounded-lg bg-teal-500 px-3 py-2 text-center text-sm font-semibold text-slate-950 hover:bg-teal-400">
                                    Create account to subscribe
                                </a>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-marketing-layout>
