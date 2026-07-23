<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 28px; }
        .brand { font-size: 22px; font-weight: bold; color: #0f766e; }
        .meta td { padding: 4px 0; }
        .box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-top: 18px; }
        .amount { font-size: 24px; font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 18px; }
        table.items th, table.items td { border-bottom: 1px solid #e2e8f0; padding: 10px 4px; text-align: left; }
        table.items th { color: #64748b; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">{{ config('app.name', 'Billora') }}</div>
            <div>Subscription invoice</div>
        </div>
        <div style="text-align: right;">
            <div><strong>{{ $invoice->number }}</strong></div>
            <div>{{ $invoice->invoice_date?->toFormattedDateString() }}</div>
            <div style="text-transform: capitalize;">{{ $invoice->status }}</div>
        </div>
    </div>

    <table class="meta" width="100%">
        <tr>
            <td width="50%">
                <strong>Billed to</strong><br>
                {{ $invoice->user->name }}<br>
                {{ $invoice->user->email }}
            </td>
            <td width="50%" style="text-align: right;">
                <div class="amount">{{ $invoice->formattedAmount() }}</div>
            </td>
        </tr>
    </table>

    <div class="box">
        <strong>Billing period</strong><br>
        {{ $invoice->period_start?->toFormattedDateString() ?? '—' }}
        —
        {{ $invoice->period_end?->toFormattedDateString() ?? '—' }}
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th>Interval</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->plan_name ?? 'Subscription' }}</td>
                <td>{{ $invoice->billing_interval ?? '—' }}</td>
                <td>{{ $invoice->formattedAmount() }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
