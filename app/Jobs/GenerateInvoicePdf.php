<?php

namespace App\Jobs;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [1, 5, 10];

    public function __construct(public Invoice $invoice) {}

    public function handle(): void
    {
        $invoice = $this->invoice->loadMissing('user');
        $path = 'invoices/'.$invoice->number.'.pdf';

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        Storage::disk('local')->put($path, $pdf->output());

        $invoice->update(['pdf_path' => $path]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to generate invoice PDF', [
            'invoice_id' => $this->invoice->id,
            'error' => $exception?->getMessage(),
        ]);
    }
}
