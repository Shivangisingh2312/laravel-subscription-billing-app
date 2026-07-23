<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'invoice_id',
    'stripe_payment_intent_id',
    'stripe_invoice_id',
    'amount',
    'currency',
    'status',
    'description',
    'paid_at',
    'metadata',
])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'usd',
        'status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'metadata' => 'array',
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
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function formattedAmount(): string
    {
        return strtoupper($this->currency).' '.number_format($this->amount / 100, 2);
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['succeeded', 'paid'], true);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
