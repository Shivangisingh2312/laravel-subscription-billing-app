<?php

namespace App\Http\Controllers;

use App\Actions\CancelSubscriptionAction;
use App\Actions\ChangeSubscriptionPlanAction;
use App\Actions\SubscribeToPlanAction;
use App\Http\Requests\ChangePlanRequest;
use App\Http\Requests\SubscribeToPlanRequest;
use App\Models\Plan;
use App\Support\MapsStripeExceptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SubscriptionController extends Controller
{
    public function store(SubscribeToPlanRequest $request, SubscribeToPlanAction $action): Response
    {
        $plan = Plan::query()->active()->whereKey($request->integer('plan_id'))->firstOrFail();

        if (! MapsStripeExceptions::stripeIsConfigured()) {
            return back()->with(
                'error',
                'Stripe is not configured correctly. Add valid STRIPE_KEY and STRIPE_SECRET test keys to your .env file, then run: php artisan config:clear'
            );
        }

        try {
            $checkout = $action->handle($request->user(), $plan, $request->string('interval')->toString());
        } catch (RuntimeException|ApiErrorException $exception) {
            return $this->redirectWithBillingError($exception);
        } catch (Throwable $exception) {
            return $this->redirectWithBillingError($exception, report: true);
        }

        return $checkout->redirect();
    }

    public function update(ChangePlanRequest $request, ChangeSubscriptionPlanAction $action): RedirectResponse
    {
        $plan = Plan::query()->active()->whereKey($request->integer('plan_id'))->firstOrFail();

        try {
            $action->handle($request->user(), $plan, $request->string('interval')->toString());
        } catch (RuntimeException|ApiErrorException $exception) {
            return $this->redirectWithBillingError($exception);
        } catch (Throwable $exception) {
            return $this->redirectWithBillingError($exception, report: true);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your subscription plan has been updated.');
    }

    public function destroy(Request $request, CancelSubscriptionAction $action): RedirectResponse
    {
        try {
            $action->handle($request->user());
        } catch (RuntimeException|ApiErrorException $exception) {
            return $this->redirectWithBillingError($exception);
        } catch (Throwable $exception) {
            return $this->redirectWithBillingError($exception, report: true);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your subscription will end at the close of the current billing period.');
    }

    public function resume(Request $request): RedirectResponse
    {
        $subscription = $request->user()->subscription('default');

        if (! $subscription || ! $subscription->onGracePeriod()) {
            return back()->with('error', 'There is no canceled subscription to resume.');
        }

        try {
            $subscription->resume();
        } catch (ApiErrorException $exception) {
            return $this->redirectWithBillingError($exception);
        } catch (Throwable $exception) {
            return $this->redirectWithBillingError($exception, report: true);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Your subscription has been resumed.');
    }

    public function success(Request $request): View
    {
        return view('subscriptions.success', [
            'sessionId' => $request->string('session_id')->toString(),
        ]);
    }

    public function cancel(): View
    {
        return view('subscriptions.cancel');
    }

    private function redirectWithBillingError(Throwable $exception, bool $report = false): RedirectResponse
    {
        if ($report) {
            Log::warning('Subscription billing error', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        return back()->with('error', MapsStripeExceptions::message($exception));
    }
}
