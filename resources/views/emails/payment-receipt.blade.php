<x-mail::message>
# Payment received

Hi {{ $payment->user->name }},

We received your payment of **{{ $payment->formattedAmount() }}**.

@if ($payment->description)
**Description:** {{ $payment->description }}
@endif

@if ($payment->invoice)
**Invoice:** {{ $payment->invoice->number }}

You can download your invoice PDF from your billing page.
@endif

<x-mail::button :url="route('invoices.index')">
View billing history
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
