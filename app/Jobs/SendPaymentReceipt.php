<?php

namespace App\Jobs;

use App\Mail\PaymentReceiptMail;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPaymentReceipt implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [1, 5, 10];

    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        $payment = $this->payment->loadMissing('user', 'invoice');

        if (! $payment->user) {
            return;
        }

        Mail::to($payment->user)->send(new PaymentReceiptMail($payment));
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to send payment receipt', [
            'payment_id' => $this->payment->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
