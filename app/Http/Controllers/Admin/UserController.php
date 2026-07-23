<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $users = User::query()
            ->with(['subscriptions' => fn ($query) => $query->where('type', 'default')])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function show(User $user): View
    {
        $user->load([
            'subscriptions' => fn ($query) => $query->latest(),
            'payments' => fn ($query) => $query->latest()->limit(20),
            'localInvoices' => fn ($query) => $query->latest()->limit(20),
        ]);

        return view('admin.users.show', [
            'user' => $user,
            'plan' => $user->currentPlan(),
            'billingInterval' => $user->currentBillingInterval(),
            'statusLabel' => $user->subscriptionStatusLabel(),
            'plans' => Plan::query()->active()->ordered()->get(),
        ]);
    }
}
