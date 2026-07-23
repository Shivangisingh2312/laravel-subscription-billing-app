<x-admin-layout header="Users & plans" subheader="Inspect customers and their active subscriptions">
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 flex flex-col gap-3 sm:flex-row">
        <input
            type="search"
            name="search"
            value="{{ $search }}"
            placeholder="Search by name or email"
            class="w-full rounded-xl border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:max-w-md"
        >
        <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Search</button>
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-6 py-3 font-medium">User</th>
                    <th class="px-6 py-3 font-medium">Role</th>
                    <th class="px-6 py-3 font-medium">Active plan</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $user)
                    @php
                        $plan = $user->currentPlan();
                    @endphp
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900">{{ $user->name }}</div>
                            <div class="text-slate-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if ($user->isAdmin())
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Admin</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">Customer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-700">
                            {{ $plan?->name ?? '—' }}
                            @if ($user->currentBillingInterval())
                                <span class="text-slate-400">({{ $user->currentBillingInterval() }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-700">{{ $user->subscriptionStatusLabel() }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-teal-700 hover:text-teal-600">Manage</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</x-admin-layout>
