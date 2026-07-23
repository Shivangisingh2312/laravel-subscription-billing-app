<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Checkout canceled</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200 text-center">
                <h3 class="text-2xl font-semibold text-slate-900">No charge was made</h3>
                <p class="mt-3 text-slate-600">You left Stripe Checkout before completing payment. You can restart anytime.</p>
                <a href="{{ route('plans.index') }}" class="mt-8 inline-flex rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-500">
                    Back to plans
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
