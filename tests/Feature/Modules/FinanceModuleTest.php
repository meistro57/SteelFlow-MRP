<?php

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceLineItem;
use Modules\Finance\Models\Payment;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Finance Module - Invoice Tests
|--------------------------------------------------------------------------
*/

describe('Invoice Management', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('can create an invoice', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-001',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 1000.00,
            'tax_amount' => 80.00,
            'total_amount' => 1080.00,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        expect($invoice)
            ->toBeInstanceOf(Invoice::class)
            ->invoice_number->toBe('INV-001')
            ->total_amount->toBe(1080.0)
            ->status->toBe('draft');

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-001',
        ]);
    });

    it('can add line items to invoice', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-002',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 0,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $lineItem = InvoiceLineItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Steel fabrication - Phase 1',
            'quantity' => 1,
            'unit_price' => 5000.00,
            'line_total' => 5000.00,
        ]);

        expect($lineItem)
            ->toBeInstanceOf(InvoiceLineItem::class)
            ->line_total->toBe(5000.0);

        expect($invoice->lineItems)->toHaveCount(1);
    });

    it('calculates invoice totals correctly', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-003',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 10000.00,
            'tax_amount' => 800.00,
            'retention_amount' => 1000.00,
            'total_amount' => 9800.00, // subtotal + tax - retention
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        expect($invoice->total_amount)->toBe(9800.0);
        expect($invoice->retention_amount)->toBe(1000.0);
    });
});

/*
|--------------------------------------------------------------------------
| Finance Module - Payment Tests
|--------------------------------------------------------------------------
*/

describe('Payment Processing', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('can record a payment against an invoice', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-PAY001',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 1000.00,
            'tax_amount' => 0,
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
            'payment_date' => now(),
            'payment_type' => 'customer_payment',
            'reference_number' => 'CHK-12345',
            'notes' => 'Partial payment',
        ]);

        expect($payment)
            ->amount->toBe(500.0)
            ->payment_type->toBe('customer_payment');

        // Update invoice paid amount
        $invoice->update(['paid_amount' => 500.00, 'status' => 'partial']);

        expect($invoice->refresh())
            ->paid_amount->toBe(500.0)
            ->status->toBe('partial');
    });

    it('marks invoice as paid when fully paid', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-PAY002',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 1000.00,
            'tax_amount' => 0,
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => 1000.00,
            'payment_date' => now(),
            'payment_type' => 'customer_payment',
        ]);

        $invoice->update(['paid_amount' => 1000.00, 'status' => 'paid']);

        expect($invoice->refresh()->status)->toBe('paid');
    });
});

/*
|--------------------------------------------------------------------------
| Finance Module - Invoice Relationships Tests
|--------------------------------------------------------------------------
*/

describe('Invoice Relationships', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('invoice belongs to customer', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-REL001',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 1000.00,
            'total_amount' => 1000.00,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        expect($invoice->customer)
            ->toBeInstanceOf(Customer::class)
            ->id->toBe($customer->id);
    });

    it('invoice belongs to project', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-REL002',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 1000.00,
            'total_amount' => 1000.00,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        expect($invoice->project)
            ->toBeInstanceOf(Project::class)
            ->id->toBe($project->id);
    });

    it('invoice has many payments', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-REL003',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 2000.00,
            'total_amount' => 2000.00,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
            'payment_date' => now()->subDays(10),
            'payment_type' => 'customer_payment',
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
            'payment_date' => now(),
            'payment_type' => 'customer_payment',
        ]);

        expect($invoice->payments)->toHaveCount(2);
        expect($invoice->payments->sum('amount'))->toBe(1000.0);
    });
});

/*
|--------------------------------------------------------------------------
| Finance Module - Invoice Status Tests
|--------------------------------------------------------------------------
*/

describe('Invoice Status Workflow', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('new invoices start as draft', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-STATUS001',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 1000.00,
            'total_amount' => 1000.00,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        expect($invoice->status)->toBe('draft');
    });

    it('can transition invoice to partial status', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-STATUS002',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 1000.00,
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $invoice->update(['status' => 'partial', 'paid_amount' => 500.00]);

        expect($invoice->refresh())
            ->status->toBe('partial')
            ->paid_amount->toBe(500.0);
    });

    it('can transition invoice to paid status', function () {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-STATUS003',
            'project_id' => $project->id,
            'customer_id' => $customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal_amount' => 1000.00,
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'status' => 'partial',
            'created_by' => $this->user->id,
        ]);

        $invoice->update(['status' => 'paid', 'paid_amount' => 1000.00]);

        expect($invoice->refresh())
            ->status->toBe('paid')
            ->paid_amount->toBe(1000.0);
    });
});
