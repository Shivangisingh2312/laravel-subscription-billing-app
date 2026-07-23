<?php

namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'stripe_invoice_id',
    'number',
    'amount',
    'currency',
    'status',
    'plan_name',
    'billing_interval',
    'pdf_path',
    'invoice_date',
    'period_start',
    'period_end',
    'line_items',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'usd',
        'status' => 'open',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'invoice_date' => 'datetime',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'line_items' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function formattedAmount(): string
    {
        return strtoupper($this->currency).' '.number_format($this->amount / 100, 2);
    }

    public function hasPdf(): bool
    {
        return filled($this->pdf_path) && Storage::disk('local')->exists($this->pdf_path);
    }
}
