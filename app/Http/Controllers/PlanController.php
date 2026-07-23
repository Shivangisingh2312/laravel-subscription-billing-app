<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $plans = Plan::query()->active()->ordered()->get();
        $user = $request->user();
        $currentPlan = $user?->currentPlan();
        $currentInterval = $user?->currentBillingInterval();

        return view('plans.index', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
            'currentInterval' => $currentInterval,
            'hasSubscription' => $user?->subscribed('default') ?? false,
        ]);
    }
}
