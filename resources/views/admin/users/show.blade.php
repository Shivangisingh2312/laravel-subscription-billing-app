@php
    use App\Models\Plan;
    $plans = Plan::query()->active()->ordered()->get();
    $subscription = $user->subscription('default');
@endphp

<x-admin-layout :header="$user->name" :subheader="$user->email" :title="'Manage '.$user->name">
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Subscription</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-sm text-slate-500">Plan</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $plan?->name ?? 'None' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Status</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $statusLabel }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Interval</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $billingInterval ? ucfirst($billingInterval) : '—' }}</p>
                    </div>
                </div>

                @if ($subscription)
                    <dl class="mt-6 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-500">Stripe subscription</dt>
                            <dd class="font-mono text-slate-800">{{ $subscription->stripe_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Stripe price</dt>
                            <dd class="font-mono text-slate-800">{{ $subscription->stripe_price }}</dd>
                        </div>
                    </dl>
                @endif
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Payment history</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($user->payments as $payment)
                        <div class="flex items-center justify-between gap-4 px-6 py-4 text-sm">
                            <div>
                                <p class="font-medium text-slate-900">{{ $payment->description }}</p>
                                <p class="text-slate-500">{{ $payment->paid_at?->toDayDateTimeString() ?? $payment->created_at?->toDayDateTimeString() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">{{ $payment->formattedAmount() }}</p>
                                <p class="capitalize {{ $payment->isFailed() ? 'text-rose-600' : 'text-emerald-600' }}">{{ $payment->status }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-slate-500">No payments for this user.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-900">Invoices</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($user->localInvoices as $invoice)
                        <div class="flex items-center justify-between gap-4 px-6 py-4 text-sm">
                            <div>
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-slate-900 hover:text-teal-700">{{ $invoice->number }}</a>
                                <p class="text-slate-500">{{ $invoice->invoice_date?->toFormattedDateString() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">{{ $invoice->formattedAmount() }}</p>
                                <a href="{{ route('invoices.download', $invoice) }}" class="text-teal-700 hover:text-teal-600">PDF</a>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-sm text-slate-500">No invoices for this user.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">Manually activate</h2>
                <p class="mt-1 text-sm text-slate-500">Useful for demos when Stripe Checkout is unavailable.</p>

                <form method="POST" action="{{ route('admin.users.subscription.activate', $user) }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Plan</label>
                        <select name="plan_id" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
                            @foreach ($plans as $availablePlan)
                                <option value="{{ $availablePlan->id }}">{{ $availablePlan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Interval</label>
                        <select name="interval" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <button class="w-full rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500">
                        Activate subscription
                    </button>
                </form>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-rose-100 ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">Cancel subscription</h2>
                <p class="mt-1 text-sm text-slate-500">Immediately ends access for local/demo subscriptions.</p>

                <form method="POST" action="{{ route('admin.users.subscription.cancel', $user) }}" class="mt-4 space-y-3" onsubmit="return confirm('Cancel this subscription now?')">
                    @csrf
                    <input type="hidden" name="immediately" value="1">
                    <button class="w-full rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                        Cancel now
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
