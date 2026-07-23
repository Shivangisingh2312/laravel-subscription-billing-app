<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'description',
    'monthly_price',
    'yearly_price',
    'stripe_monthly_price_id',
    'stripe_yearly_price_id',
    'features',
    'is_active',
    'sort_order',
])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monthly_price' => 'integer',
            'yearly_price' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Plan $plan): void {
            if (blank($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    /**
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Plan>  $query
     * @return Builder<Plan>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function priceFor(string $interval): int
    {
        return $interval === 'yearly' ? $this->yearly_price : $this->monthly_price;
    }

    public function stripePriceIdFor(string $interval): ?string
    {
        return $interval === 'yearly'
            ? $this->stripe_yearly_price_id
            : $this->stripe_monthly_price_id;
    }

    public function formattedPrice(string $interval): string
    {
        return '$'.number_format($this->priceFor($interval) / 100, 2);
    }

    public static function findByStripePriceId(string $stripePriceId): ?self
    {
        return static::query()
            ->where(function ($query) use ($stripePriceId): void {
                $query->where('stripe_monthly_price_id', $stripePriceId)
                    ->orWhere('stripe_yearly_price_id', $stripePriceId);
            })
            ->first();
    }

    public function intervalForStripePriceId(string $stripePriceId): ?string
    {
        if ($this->stripe_monthly_price_id === $stripePriceId) {
            return 'monthly';
        }

        if ($this->stripe_yearly_price_id === $stripePriceId) {
            return 'yearly';
        }

        return null;
    }
}
