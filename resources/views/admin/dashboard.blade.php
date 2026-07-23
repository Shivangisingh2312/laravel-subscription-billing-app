<x-admin-layout header="Revenue overview" subheader="Live billing health across customers">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-5 text-white shadow-sm">
            <p class="text-sm text-slate-300">Total revenue</p>
            <p class="mt-2 text-3xl font-semibold">${{ number_format($totalRevenue / 100, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm text-slate-500">Revenue this month</p>
            <p class="mt-2 text-3xl font-semibold text-teal-700">${{ number_format($monthlyRevenue / 100, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm text-slate-500">Active subscriptions</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $activeSubscriptions }}</p>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm text-slate-500">Users / failed payments</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $totalUsers }} <span class="text-lg text-slate-400">/</span> <span class="text-rose-600">{{ $failedPayments }}</span></p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-5">
        <div class="xl:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Monthly revenue</h2>
            <div class="mt-6 space-y-4">
                @php $max = max(1, $monthlyBreakdown->max('total')); @endphp
                @foreach ($monthlyBreakdown as $row)
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="text-slate-600">{{ $row['month'] }}</span>
                            <span class="font-medium text-slate-900">${{ number_format($row['total'] / 100, 2) }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-teal-500" style="width: {{ ($row['total'] / $max) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="xl:col-span-3 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="font-semibold text-slate-900">Recent payments</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-medium">Customer</th>
                            <th class="px-6 py-3 font-medium">Description</th>
                            <th class="px-6 py-3 font-medium">Amount</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td class="px-6 py-3">
                                    <a href="{{ route('admin.users.show', $payment->user) }}" class="font-medium text-slate-900 hover:text-teal-700">
                                        {{ $payment->user?->name ?? 'Unknown' }}
                                    </a>
                                </td>
                                <td class="px-6 py-3 text-slate-600">{{ $payment->description }}</td>
                                <td class="px-6 py-3 font-medium">{{ $payment->formattedAmount() }}</td>
                                <td class="px-6 py-3">
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold capitalize',
                                        'bg-emerald-50 text-emerald-700' => $payment->isSuccessful(),
                                        'bg-rose-50 text-rose-700' => $payment->isFailed(),
                                        'bg-slate-100 text-slate-700' => ! $payment->isSuccessful() && ! $payment->isFailed(),
                                    ])>{{ $payment->status }}</span>
                                </td>
                                <td class="px-6 py-3 text-slate-500">{{ $payment->paid_at?->diffForHumans() ?? $payment->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
