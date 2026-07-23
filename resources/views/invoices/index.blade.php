<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Billing invoices') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-medium">Invoice</th>
                            <th class="px-6 py-3 font-medium">Plan</th>
                            <th class="px-6 py-3 font-medium">Date</th>
                            <th class="px-6 py-3 font-medium">Amount</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="hover:text-teal-700">{{ $invoice->number }}</a>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $invoice->plan_name ?? '—' }}
                                    @if ($invoice->billing_interval)
                                        <span class="text-slate-400">({{ $invoice->billing_interval }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $invoice->invoice_date?->toFormattedDateString() ?? '—' }}</td>
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $invoice->formattedAmount() }}</td>
                                <td class="px-6 py-4 capitalize text-slate-600">{{ $invoice->status }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('invoices.download', $invoice) }}" class="font-medium text-teal-700 hover:text-teal-600">Download PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-500">No invoices yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
