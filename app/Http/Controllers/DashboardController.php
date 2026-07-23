<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $subscription = $user->subscription('default');
        $plan = $user->currentPlan();
        $recentPayments = $user->payments()->latest()->limit(5)->get();
        $recentInvoices = $user->localInvoices()->latest()->limit(5)->get();

        return view('dashboard', [
            'user' => $user,
            'subscription' => $subscription,
            'plan' => $plan,
            'billingInterval' => $user->currentBillingInterval(),
            'statusLabel' => $user->subscriptionStatusLabel(),
            'recentPayments' => $recentPayments,
            'recentInvoices' => $recentInvoices,
            'failedPayments' => $user->payments()->where('status', 'failed')->latest()->limit(3)->get(),
        ]);
    }
}
