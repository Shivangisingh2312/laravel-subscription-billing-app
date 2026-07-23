<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AdminActivateSubscriptionAction;
use App\Actions\AdminCancelSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminActivateSubscriptionRequest;
use App\Models\User;
use App\Support\MapsStripeExceptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Throwable;

class SubscriptionController extends Controller
{
    public function activate(
        AdminActivateSubscriptionRequest $request,
        User $user,
        AdminActivateSubscriptionAction $action
    ): RedirectResponse {
        try {
            $action->handle(
                $user,
                $request->integer('plan_id'),
                $request->string('interval')->toString()
            );
        } catch (RuntimeException|ApiErrorException $exception) {
            return back()->with('error', MapsStripeExceptions::message($exception));
        } catch (Throwable $exception) {
            Log::warning('Admin subscription activate failed', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', MapsStripeExceptions::message($exception));
        }

        return back()->with('success', 'Subscription activated for '.$user->name.'.');
    }

    public function cancel(
        Request $request,
        User $user,
        AdminCancelSubscriptionAction $action
    ): RedirectResponse {
        try {
            $action->handle($user, $request->boolean('immediately'));
        } catch (RuntimeException|ApiErrorException $exception) {
            return back()->with('error', MapsStripeExceptions::message($exception));
        } catch (Throwable $exception) {
            Log::warning('Admin subscription cancel failed', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', MapsStripeExceptions::message($exception));
        }

        return back()->with('success', 'Subscription canceled for '.$user->name.'.');
    }
}
