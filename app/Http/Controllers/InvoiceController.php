<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = $request->user()
            ->localInvoices()
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(15);

        return view('invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Request $request, Invoice $invoice): View
    {
        abort_unless($invoice->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        return view('invoices.show', [
            'invoice' => $invoice->load('user'),
        ]);
    }

    public function download(Request $request, Invoice $invoice): Response|StreamedResponse
    {
        abort_unless($invoice->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        if ($invoice->hasPdf()) {
            return Storage::disk('local')->download(
                $invoice->pdf_path,
                $invoice->number.'.pdf'
            );
        }

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice->load('user')]);

        return $pdf->download($invoice->number.'.pdf');
    }
}
