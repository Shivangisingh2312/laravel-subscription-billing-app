<?php

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('users can view their invoice history', function () {
    $user = User::factory()->create();
    Invoice::factory()->create([
        'user_id' => $user->id,
        'number' => 'INV-TEST-0001',
    ]);

    $this->actingAs($user)
        ->get(route('invoices.index'))
        ->assertSuccessful()
        ->assertSee('INV-TEST-0001');
});

test('users can download invoice pdfs', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $invoice = Invoice::factory()->create([
        'user_id' => $user->id,
        'number' => 'INV-PDF-0001',
    ]);

    $this->actingAs($user)
        ->get(route('invoices.download', $invoice))
        ->assertSuccessful();
});

test('users cannot view another users invoice', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $invoice = Invoice::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($intruder)
        ->get(route('invoices.show', $invoice))
        ->assertForbidden();
});
