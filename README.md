# Billora — Laravel Subscription Billing Platform

Subscription billing app built with **Laravel**, **Laravel Breeze**, **Laravel Cashier (Stripe)**, **Blade**, and **Tailwind CSS**.

## Features

- Authentication: register, login, logout, email verification, password reset
- Plans: Basic, Pro, Enterprise with monthly & yearly pricing
- Stripe Checkout subscriptions with a **14-day free trial** on first subscribe
- Upgrade / downgrade / cancel / resume
- Stripe webhooks: `invoice.paid`, `invoice.payment_failed` (`payment_failed`), `customer.subscription.deleted`
- Payment history stored locally
- Invoice history + PDF download (DomPDF)
- Queued email receipt after successful payment
- Admin console: users & plans, revenue overview, manual activate/cancel

## Requirements

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL (or SQLite for local/demo)
- Stripe account (test mode)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure `.env`:

```env
APP_NAME=Billora
APP_URL=http://billing_app.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=billing_app
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
MAIL_MAILER=log
```

### Stripe test mode credentials

1. Open [Stripe Dashboard → Developers → API keys](https://dashboard.stripe.com/test/apikeys)
2. Copy your **Publishable** and **Secret** test keys into `.env`:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

3. Create three Products in Stripe (Basic, Pro, Enterprise), each with monthly + yearly Prices.
4. Paste the Price IDs:

```env
STRIPE_PRICE_BASIC_MONTHLY=price_...
STRIPE_PRICE_BASIC_YEARLY=price_...
STRIPE_PRICE_PRO_MONTHLY=price_...
STRIPE_PRICE_PRO_YEARLY=price_...
STRIPE_PRICE_ENTERPRISE_MONTHLY=price_...
STRIPE_PRICE_ENTERPRISE_YEARLY=price_...
```

5. Create a webhook endpoint pointing to:

```text
https://your-app.test/stripe/webhook
```

Or create Stripe products/prices automatically:

```bash
php artisan billing:sync-stripe-prices --write-env
php artisan config:clear
```

This creates Basic / Pro / Enterprise products in your Stripe **test** account, saves the Price IDs on local plans, and updates `.env`.

   Events to enable:
   - `invoice.paid`
   - `invoice.payment_failed`
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded` (optional compatibility)

6. Set the signing secret:

```env
STRIPE_WEBHOOK_SECRET=whsec_...
```

For local webhooks, use the Stripe CLI:

```bash
stripe listen --forward-to https://billing_app.test/stripe/webhook
```

## Database

```bash
php artisan migrate --seed
npm install
npm run build
```

Start the queue worker (required for receipt emails + PDF generation):

```bash
php artisan queue:work
```

Or run everything together:

```bash
composer run dev
```

The app is also available via Laravel Herd at `http://billing_app.test` / `https://billing_app.test`.

## Demo accounts

Seeded by `DemoUserSeeder` (password for all: `password`):

| Email | Role | Notes |
|-------|------|-------|
| `admin@billing.test` | Admin | Full admin console |
| `user@billing.test` | Customer | Active Pro monthly + invoices/payments |
| `free@billing.test` | Customer | No subscription |

## Key URLs

- `/` — public plans / marketing
- `/dashboard` — subscription status
- `/plans` — subscribe / change plan
- `/invoices` — invoice history + PDF
- `/admin` — admin revenue dashboard
- `/admin/users` — manage users & subscriptions
- `/stripe/webhook` — Stripe webhooks

## Testing

```bash
php artisan test --compact
```

## Notes

- First-time subscriptions include a **14-day trial** via Cashier Checkout.
- Admin “Activate subscription” creates a **local/demo** subscription (no Stripe API call) so you can exercise the UI without live keys.
- Real Stripe Checkout requires valid `STRIPE_*` keys and matching Price IDs.
