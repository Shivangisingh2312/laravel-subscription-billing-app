<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $successfulStatuses = ['succeeded', 'paid'];

        $totalRevenue = (int) Payment::query()
            ->whereIn('status', $successfulStatuses)
            ->sum('amount');

        $monthlyRevenue = (int) Payment::query()
            ->whereIn('status', $successfulStatuses)
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $activeSubscriptions = User::query()
            ->whereHas('subscriptions', function ($query): void {
                $query->where('type', 'default')
                    ->where(function ($statusQuery): void {
                        $statusQuery
                            ->whereIn('stripe_status', ['active', 'trialing'])
                            ->orWhere(function ($graceQuery): void {
                                $graceQuery->whereNotNull('ends_at')
                                    ->where('ends_at', '>', now());
                            });
                    });
            })
            ->count();

        $totalUsers = User::query()->count();
        $failedPayments = Payment::query()->where('status', 'failed')->count();

        $recentPayments = Payment::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();

        $paymentsLastSixMonths = Payment::query()
            ->whereIn('status', $successfulStatuses)
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['amount', 'paid_at']);

        $monthlyBreakdown = collect(range(5, 0))->map(function (int $monthsAgo) use ($paymentsLastSixMonths): array {
            $month = now()->subMonths($monthsAgo)->startOfMonth();

            $total = $paymentsLastSixMonths
                ->filter(fn (Payment $payment): bool => $payment->paid_at?->isSameMonth($month) ?? false)
                ->sum('amount');

            return [
                'month' => $month->format('M Y'),
                'total' => (int) $total,
            ];
        });

        return view('admin.dashboard', [
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'activeSubscriptions' => $activeSubscriptions,
            'totalUsers' => $totalUsers,
            'failedPayments' => $failedPayments,
            'recentPayments' => $recentPayments,
            'monthlyBreakdown' => $monthlyBreakdown,
        ]);
    }
}
