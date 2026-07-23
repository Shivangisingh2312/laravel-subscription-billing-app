<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Invoice {{ $invoice->number }}</h2>
            <a href="{{ route('invoices.download', $invoice) }}" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500">
                Download PDF
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200 space-y-6">
                <div class="flex justify-between gap-6">
                    <div>
                        <p class="text-sm uppercase tracking-wide text-slate-500">Billed to</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $invoice->user->name }}</p>
                        <p class="text-slate-600">{{ $invoice->user->email }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm uppercase tracking-wide text-slate-500">Amount</p>
                        <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $invoice->formattedAmount() }}</p>
                        <p class="capitalize text-slate-600">{{ $invoice->status }}</p>
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-slate-500">Invoice date</dt>
                        <dd class="font-medium text-slate-900">{{ $invoice->invoice_date?->toFormattedDateString() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Plan</dt>
                        <dd class="font-medium text-slate-900">{{ $invoice->plan_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Period start</dt>
                        <dd class="font-medium text-slate-900">{{ $invoice->period_start?->toFormattedDateString() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Period end</dt>
                        <dd class="font-medium text-slate-900">{{ $invoice->period_end?->toFormattedDateString() ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
