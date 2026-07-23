<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->string('number')->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('usd');
            $table->string('status')->default('open')->index();
            $table->string('plan_name')->nullable();
            $table->string('billing_interval')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->json('line_items')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
