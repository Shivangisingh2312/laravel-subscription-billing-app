<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Subscription started</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200 text-center">
                <p class="text-sm uppercase tracking-wide text-teal-700">Success</p>
                <h3 class="mt-2 text-2xl font-semibold text-slate-900">Payment checkout completed</h3>
                <p class="mt-3 text-slate-600">
                    Stripe is confirming your subscription. Your dashboard will update once the webhook is processed.
                </p>
                @if ($sessionId)
                    <p class="mt-4 text-xs text-slate-400 break-all">Session: {{ $sessionId }}</p>
                @endif
                <a href="{{ route('dashboard') }}" class="mt-8 inline-flex rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500">
                    Go to dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
