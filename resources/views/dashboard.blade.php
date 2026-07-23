<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <p class="text-sm text-slate-500">Welcome back, {{ $user->name }}</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-teal-900 px-6 py-8 text-white">
                        <p class="text-sm uppercase tracking-[0.2em] text-teal-200/80">Current subscription</p>
                        <h3 class="mt-2 text-3xl font-semibold">{{ $plan?->name ?? 'No active plan' }}</h3>
                        <p class="mt-2 text-slate-300">
                            Status:
                            <span class="font-medium text-white">{{ $statusLabel }}</span>
                            @if ($billingInterval)
                                · {{ ucfirst($billingInterval) }} billing
                            @endif
                        </p>

                        @if ($subscription?->onTrial())
                            <p class="mt-4 text-sm text-teal-100">
                                Trial ends {{ $subscription->trial_ends_at?->toFormattedDateString() }}
                            </p>
                        @elseif ($subscription?->onGracePeriod())
                            <p class="mt-4 text-sm text-amber-200">
                                Access continues until {{ $subscription->ends_at?->toFormattedDateString() }}
                            </p>
                        @endif
                    </div>

                    <div class="p-6 flex flex-wrap gap-3">
                        <a href="{{ route('plans.index') }}" class="inline-flex items-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500">
                            {{ $plan ? 'Change plan' : 'Choose a plan' }}
                        </a>
                        <a href="{{ route('invoices.index') }}" class="inline-flex items-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                            View invoices
                        </a>

                        @if ($subscription && ($subscription->active() || $subscription->onTrial()) && ! $subscription->onGracePeriod())
                            <form method="POST" action="{{ route('subscriptions.destroy') }}" onsubmit="return confirm('Cancel your subscription at period end?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center rounded-lg bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">
                                    Cancel subscription
                                </button>
                            </form>
                        @endif

                        @if ($subscription?->onGracePeriod())
                            <form method="POST" action="{{ route('subscriptions.resume') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                                    Resume subscription
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Quick facts</h4>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Plan price</dt>
                            <dd class="font-medium text-slate-900">
                                @if ($plan && $billingInterval)
                                    {{ $plan->formattedPrice($billingInterval) }}
                                    / {{ $billingInterval === 'yearly' ? 'year' : 'month' }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Payments recorded</dt>
                            <dd class="font-medium text-slate-900">{{ $user->payments()->count() }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Invoices</dt>
                            <dd class="font-medium text-slate-900">{{ $user->localInvoices()->count() }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Trial used</dt>
                            <dd class="font-medium text-slate-900">{{ $user->hasUsedTrial() ? 'Yes' : 'No' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if ($failedPayments->isNotEmpty())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                    <h4 class="font-semibold text-rose-900">Payment issues</h4>
                    <ul class="mt-3 space-y-2 text-sm text-rose-800">
                        @foreach ($failedPayments as $payment)
                            <li>{{ $payment->description }} — {{ $payment->formattedAmount() }} ({{ $payment->created_at?->diffForHumans() }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                        <h4 class="font-semibold text-slate-900">Recent payments</h4>
                        <a href="{{ route('invoices.index') }}" class="text-sm text-teal-700 hover:text-teal-600">All billing</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($recentPayments as $payment)
                            <div class="px-6 py-4 flex items-center justify-between gap-4 text-sm">
                                <div>
                                    <p class="font-medium text-slate-900">{{ $payment->description ?? 'Payment' }}</p>
                                    <p class="text-slate-500">{{ $payment->paid_at?->toDayDateTimeString() ?? $payment->created_at?->toDayDateTimeString() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-slate-900">{{ $payment->formattedAmount() }}</p>
                                    <p class="capitalize {{ $payment->isFailed() ? 'text-rose-600' : 'text-emerald-600' }}">{{ $payment->status }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="px-6 py-8 text-sm text-slate-500">No payments yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                        <h4 class="font-semibold text-slate-900">Recent invoices</h4>
                        <a href="{{ route('invoices.index') }}" class="text-sm text-teal-700 hover:text-teal-600">View all</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($recentInvoices as $invoice)
                            <div class="px-6 py-4 flex items-center justify-between gap-4 text-sm">
                                <div>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-slate-900 hover:text-teal-700">{{ $invoice->number }}</a>
                                    <p class="text-slate-500">{{ $invoice->invoice_date?->toFormattedDateString() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-slate-900">{{ $invoice->formattedAmount() }}</p>
                                    <a href="{{ route('invoices.download', $invoice) }}" class="text-teal-700 hover:text-teal-600">PDF</a>
                                </div>
                            </div>
                        @empty
                            <p class="px-6 py-8 text-sm text-slate-500">No invoices yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
