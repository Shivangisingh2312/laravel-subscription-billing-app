@if (session('success') || session('error') || $errors->any())
    <div class="space-y-3 {{ $attributes->get('class') }}">
        @if (session('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 8000)"
                role="alert"
                class="flex items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm"
            >
                <div class="flex gap-3">
                    <span class="mt-0.5 font-bold text-emerald-600">✓</span>
                    <p>{{ session('success') }}</p>
                </div>
                <button type="button" @click="show = false" class="text-emerald-700/70 hover:text-emerald-900" aria-label="Dismiss">×</button>
            </div>
        @endif

        @if (session('error'))
            <div
                x-data="{ show: true }"
                x-show="show"
                role="alert"
                class="flex items-start justify-between gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm"
            >
                <div class="flex gap-3">
                    <span class="mt-0.5 font-bold text-rose-600">!</span>
                    <p>{{ session('error') }}</p>
                </div>
                <button type="button" @click="show = false" class="text-rose-700/70 hover:text-rose-900" aria-label="Dismiss">×</button>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-2 list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
