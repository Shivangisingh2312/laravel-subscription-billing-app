<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Support\MapsStripeExceptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class SyncStripePricesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'billing:sync-stripe-prices
                            {--write-env : Update STRIPE_PRICE_* values in the .env file}';

    /**
     * @var string
     */
    protected $description = 'Create Stripe products/prices for Basic, Pro, and Enterprise and link them to local plans';

    public function handle(): int
    {
        if (! MapsStripeExceptions::stripeIsConfigured()) {
            $this->error('Stripe secret key is missing or still a placeholder. Set STRIPE_SECRET in .env first.');

            return self::FAILURE;
        }

        $stripe = new StripeClient(config('cashier.secret'));

        $definitions = [
            'basic' => [
                'name' => 'Basic',
                'description' => 'Essential billing tools for individuals getting started.',
                'monthly' => 999,
                'yearly' => 9900,
                'env' => ['monthly' => 'STRIPE_PRICE_BASIC_MONTHLY', 'yearly' => 'STRIPE_PRICE_BASIC_YEARLY'],
            ],
            'pro' => [
                'name' => 'Pro',
                'description' => 'Advanced controls for growing teams and businesses.',
                'monthly' => 2999,
                'yearly' => 29900,
                'env' => ['monthly' => 'STRIPE_PRICE_PRO_MONTHLY', 'yearly' => 'STRIPE_PRICE_PRO_YEARLY'],
            ],
            'enterprise' => [
                'name' => 'Enterprise',
                'description' => 'Full platform access with dedicated support and controls.',
                'monthly' => 9999,
                'yearly' => 99900,
                'env' => ['monthly' => 'STRIPE_PRICE_ENTERPRISE_MONTHLY', 'yearly' => 'STRIPE_PRICE_ENTERPRISE_YEARLY'],
            ],
        ];

        $envUpdates = [];

        try {
            foreach ($definitions as $slug => $definition) {
                $this->info("Syncing {$definition['name']}...");

                $product = $stripe->products->create([
                    'name' => $definition['name'].' — '.config('app.name', 'Billora'),
                    'description' => $definition['description'],
                    'metadata' => [
                        'app' => config('app.name', 'Billora'),
                        'plan_slug' => $slug,
                    ],
                ]);

                $monthlyPrice = $stripe->prices->create([
                    'product' => $product->id,
                    'unit_amount' => $definition['monthly'],
                    'currency' => config('cashier.currency', 'usd'),
                    'recurring' => ['interval' => 'month'],
                    'metadata' => [
                        'plan_slug' => $slug,
                        'billing_interval' => 'monthly',
                    ],
                ]);

                $yearlyPrice = $stripe->prices->create([
                    'product' => $product->id,
                    'unit_amount' => $definition['yearly'],
                    'currency' => config('cashier.currency', 'usd'),
                    'recurring' => ['interval' => 'year'],
                    'metadata' => [
                        'plan_slug' => $slug,
                        'billing_interval' => 'yearly',
                    ],
                ]);

                Plan::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'monthly_price' => $definition['monthly'],
                        'yearly_price' => $definition['yearly'],
                        'stripe_monthly_price_id' => $monthlyPrice->id,
                        'stripe_yearly_price_id' => $yearlyPrice->id,
                        'is_active' => true,
                    ]
                );

                $envUpdates[$definition['env']['monthly']] = $monthlyPrice->id;
                $envUpdates[$definition['env']['yearly']] = $yearlyPrice->id;

                $this->line("  monthly: {$monthlyPrice->id}");
                $this->line("  yearly:  {$yearlyPrice->id}");
            }
        } catch (ApiErrorException $exception) {
            $this->error(MapsStripeExceptions::message($exception));

            return self::FAILURE;
        }

        // Remove leftover factory/demo plans that are not part of the catalog.
        Plan::query()->whereNotIn('slug', array_keys($definitions))->delete();

        if ($this->option('write-env')) {
            $this->writeEnvValues($envUpdates);
            $this->info('Updated .env with new Stripe Price IDs.');
            $this->comment('Run: php artisan config:clear');
        } else {
            $this->newLine();
            $this->info('Add these to your .env file (or re-run with --write-env):');
            foreach ($envUpdates as $key => $value) {
                $this->line("{$key}={$value}");
            }
        }

        $this->newLine();
        $this->info('Done. You can subscribe from /plans now.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $values
     */
    private function writeEnvValues(array $values): void
    {
        $path = base_path('.env');

        if (! File::exists($path)) {
            $this->warn('.env file not found; skipped writing.');

            return;
        }

        $contents = File::get($path);

        foreach ($values as $key => $value) {
            $line = $key.'='.$value;

            if (preg_match("/^{$key}=.*/m", $contents) === 1) {
                $contents = preg_replace("/^{$key}=.*/m", $line, $contents) ?? $contents;
            } else {
                $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
            }
        }

        File::put($path, $contents);
    }
}
