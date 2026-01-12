# Estimating and Point of Sale Module Architecture

**Version:** 1.0
**Date:** 2026-01-12
**Status:** Architecture Design

---

## Table of Contents

1. [Overview](#overview)
2. [Estimating Module](#estimating-module)
3. [Point of Sale Module](#point-of-sale-module)
4. [Integration Points](#integration-points)
5. [Implementation Roadmap](#implementation-roadmap)

---

## Overview

This document outlines the architecture for two new modules in SteelFlow MRP:

- **Estimating Module**: Quote/bid management system with cost calculation, markup, and quote-to-project conversion
- **Point of Sale (POS) Module**: Counter sales system for walk-in customers, remnant sales, and direct material sales

Both modules follow established SteelFlow patterns:
- **Service Layer Pattern**: Business logic in `app/Services/`
- **Inertia.js**: Server-driven SPA with Vue.js pages
- **Dual Units**: Imperial and Metric weight/measurement tracking
- **Soft Deletes**: Audit trail preservation
- **Database Transactions**: Data integrity for multi-step operations

---

## Estimating Module

### Business Requirements

The Estimating module enables sales and engineering teams to:

1. **Create Quotes** - Build estimates for fabrication projects
2. **Calculate Costs** - Material, labor, overhead, and subcontractor costs
3. **Apply Markup** - Profit margins and pricing strategies
4. **Track Revisions** - Version control for quote changes
5. **Approval Workflow** - Multi-level quote approval process
6. **Convert to Project** - Transform accepted quotes into production projects
7. **Historical Analysis** - Win/loss tracking and pricing analytics

### Database Schema

#### Migration: `create_estimating_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main quotes table
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number', 20)->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('project_id')->nullable()->constrained(); // If converted
            $table->string('project_name', 255);
            $table->text('description')->nullable();
            $table->enum('status', [
                'draft',           // Initial creation
                'pending_review',  // Submitted for review
                'approved',        // Approved internally
                'sent',           // Sent to customer
                'accepted',       // Customer accepted
                'rejected',       // Customer rejected
                'expired',        // Past expiration date
                'converted',      // Converted to project
                'cancelled'       // Cancelled
            ])->default('draft');

            // Dates
            $table->date('quote_date');
            $table->date('valid_until');
            $table->date('sent_date')->nullable();
            $table->date('accepted_date')->nullable();

            // Contact information
            $table->string('contact_name', 100)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 50)->nullable();

            // Location/delivery
            $table->string('delivery_address_1', 255)->nullable();
            $table->string('delivery_city', 100)->nullable();
            $table->string('delivery_state', 50)->nullable();
            $table->string('delivery_zip', 20)->nullable();

            // Financial summary (calculated from line items)
            $table->decimal('subtotal_materials', 12, 2)->default(0);
            $table->decimal('subtotal_labor', 12, 2)->default(0);
            $table->decimal('subtotal_overhead', 12, 2)->default(0);
            $table->decimal('subtotal_subcontractors', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('markup_percentage', 5, 2)->default(0);
            $table->decimal('markup_amount', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            // Weight estimates
            $table->decimal('estimated_weight_lbs', 12, 2)->nullable();
            $table->decimal('estimated_weight_kg', 12, 2)->nullable();

            // Terms and conditions
            $table->string('payment_terms', 100)->default('Net 30');
            $table->integer('lead_time_days')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();

            // Revision tracking
            $table->integer('revision_number')->default(0);
            $table->foreignId('parent_quote_id')->nullable()->constrained('quotes');

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('quote_number');
            $table->index('customer_id');
            $table->index('status');
            $table->index('quote_date');
        });

        // Quote line items
        Schema::create('quote_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->integer('line_number')->default(0);
            $table->enum('line_type', [
                'material',       // Stock material
                'fabrication',    // Labor/fabrication
                'subcontractor',  // Outside service
                'misc',          // Miscellaneous charge
                'discount',      // Line-level discount
                'section_header' // Visual grouping
            ])->default('material');

            // Item description
            $table->string('description', 255);
            $table->text('notes')->nullable();

            // Material-specific fields
            $table->foreignId('material_id')->nullable()->constrained();
            $table->string('material_size', 50)->nullable();
            $table->string('material_grade', 50)->nullable();

            // Quantities
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit', 20)->default('EA'); // EA, LF, TON, LBS, etc.
            $table->decimal('length_ft', 12, 2)->nullable();
            $table->decimal('weight_lbs', 12, 2)->nullable();
            $table->decimal('weight_kg', 12, 2)->nullable();

            // Pricing
            $table->decimal('unit_cost', 12, 4)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 4)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('markup_percentage', 5, 2)->nullable(); // Override

            // Labor estimates (for fabrication lines)
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('labor_rate', 10, 2)->nullable();

            // Vendor/subcontractor (for subcontractor lines)
            $table->foreignId('vendor_id')->nullable()->constrained();

            // Grouping
            $table->string('phase_code', 50)->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['quote_id', 'line_number']);
        });

        // Quote attachments (drawings, specifications, etc.)
        Schema::create('quote_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('description', 255)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Quote activity log
        Schema::create('quote_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('action', 100); // created, updated, sent, approved, etc.
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamps();

            $table->index(['quote_id', 'created_at']);
        });

        // Quote templates (reusable configurations)
        Schema::create('quote_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('default_markup_percentage', 5, 2)->default(0);
            $table->string('default_payment_terms', 100)->default('Net 30');
            $table->integer('default_lead_time_days')->nullable();
            $table->text('default_terms_conditions')->nullable();
            $table->json('default_sections')->nullable(); // Predefined line item sections
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_templates');
        Schema::dropIfExists('quote_activities');
        Schema::dropIfExists('quote_attachments');
        Schema::dropIfExists('quote_line_items');
        Schema::dropIfExists('quotes');
    }
};
```

### Eloquent Models

#### Quote Model

**Location:** `app/Models/Quote.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property string $quote_number
 * @property int $customer_id
 * @property int|null $project_id
 * @property string $project_name
 * @property string|null $description
 * @property string $status
 * @property \Illuminate\Support\Carbon $quote_date
 * @property \Illuminate\Support\Carbon $valid_until
 * @property decimal $total_cost
 * @property decimal $total_price
 * @property decimal $markup_percentage
 * @property int $revision_number
 */
class Quote extends Model
{
    use Searchable, SoftDeletes;

    protected $fillable = [
        'quote_number',
        'customer_id',
        'project_id',
        'project_name',
        'description',
        'status',
        'quote_date',
        'valid_until',
        'sent_date',
        'accepted_date',
        'contact_name',
        'contact_email',
        'contact_phone',
        'delivery_address_1',
        'delivery_city',
        'delivery_state',
        'delivery_zip',
        'subtotal_materials',
        'subtotal_labor',
        'subtotal_overhead',
        'subtotal_subcontractors',
        'total_cost',
        'markup_percentage',
        'markup_amount',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_price',
        'estimated_weight_lbs',
        'estimated_weight_kg',
        'payment_terms',
        'lead_time_days',
        'terms_conditions',
        'notes',
        'revision_number',
        'parent_quote_id',
        'created_by',
        'approved_by',
        'updated_by',
        'approved_at',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'sent_date' => 'date',
        'accepted_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal_materials' => 'decimal:2',
        'subtotal_labor' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_price' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(QuoteLineItem::class)->orderBy('sort_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(QuoteAttachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(QuoteActivity::class)->latest();
    }

    public function parentQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'parent_quote_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Quote::class, 'parent_quote_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if quote is expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until < now() &&
               !in_array($this->status, ['accepted', 'converted', 'cancelled']);
    }

    /**
     * Check if quote can be edited
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'pending_review']);
    }

    /**
     * Check if quote can be converted to project
     */
    public function canConvert(): bool
    {
        return $this->status === 'accepted' && $this->project_id === null;
    }
}
```

#### QuoteLineItem Model

**Location:** `app/Models/QuoteLineItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLineItem extends Model
{
    protected $fillable = [
        'quote_id',
        'line_number',
        'line_type',
        'description',
        'notes',
        'material_id',
        'material_size',
        'material_grade',
        'quantity',
        'unit',
        'length_ft',
        'weight_lbs',
        'weight_kg',
        'unit_cost',
        'total_cost',
        'unit_price',
        'total_price',
        'markup_percentage',
        'estimated_hours',
        'labor_rate',
        'vendor_id',
        'phase_code',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:2',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
```

#### QuoteAttachment Model

**Location:** `app/Models/QuoteAttachment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteAttachment extends Model
{
    protected $fillable = [
        'quote_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'uploaded_by',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
```

#### QuoteActivity Model

**Location:** `app/Models/QuoteActivity.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteActivity extends Model
{
    protected $fillable = [
        'quote_id',
        'user_id',
        'action',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Service Layer

#### EstimatingService

**Location:** `app/Services/EstimatingService.php`

```php
<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteLineItem;
use App\Models\Project;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EstimatingService
{
    /**
     * Calculate quote totals from line items
     */
    public function calculateQuoteTotals(Quote $quote): void
    {
        $quote->load('lineItems');

        $subtotalMaterials = 0;
        $subtotalLabor = 0;
        $subtotalSubcontractors = 0;
        $subtotalMisc = 0;
        $totalWeight = 0;

        foreach ($quote->lineItems as $item) {
            switch ($item->line_type) {
                case 'material':
                    $subtotalMaterials += $item->total_cost;
                    $totalWeight += $item->weight_lbs ?? 0;
                    break;
                case 'fabrication':
                    $subtotalLabor += $item->total_cost;
                    break;
                case 'subcontractor':
                    $subtotalSubcontractors += $item->total_cost;
                    break;
                case 'misc':
                    $subtotalMisc += $item->total_cost;
                    break;
            }
        }

        $totalCost = $subtotalMaterials + $subtotalLabor +
                     $subtotalSubcontractors + $subtotalMisc;

        $markupAmount = $totalCost * ($quote->markup_percentage / 100);
        $subtotalWithMarkup = $totalCost + $markupAmount;
        $discountAmount = $subtotalWithMarkup * ($quote->discount_percentage / 100);
        $subtotalAfterDiscount = $subtotalWithMarkup - $discountAmount;
        $taxAmount = $subtotalAfterDiscount * ($quote->tax_percentage / 100);
        $totalPrice = $subtotalAfterDiscount + $taxAmount;

        $quote->update([
            'subtotal_materials' => $subtotalMaterials,
            'subtotal_labor' => $subtotalLabor,
            'subtotal_subcontractors' => $subtotalSubcontractors,
            'subtotal_overhead' => $subtotalMisc,
            'total_cost' => $totalCost,
            'markup_amount' => $markupAmount,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_price' => $totalPrice,
            'estimated_weight_lbs' => $totalWeight,
            'estimated_weight_kg' => $totalWeight * 0.453592,
        ]);
    }

    /**
     * Calculate line item totals
     */
    public function calculateLineItemTotals(QuoteLineItem $item): void
    {
        // Calculate cost
        $totalCost = match($item->line_type) {
            'material' => $item->quantity * $item->unit_cost,
            'fabrication' => ($item->estimated_hours ?? 0) * ($item->labor_rate ?? 0),
            'subcontractor' => $item->quantity * $item->unit_cost,
            'misc' => $item->quantity * $item->unit_cost,
            default => 0,
        };

        // Apply markup (use line-level or quote-level)
        $markupPct = $item->markup_percentage ?? $item->quote->markup_percentage ?? 0;
        $totalPrice = $totalCost * (1 + $markupPct / 100);

        $item->update([
            'total_cost' => $totalCost,
            'total_price' => $totalPrice,
            'unit_price' => $item->quantity > 0 ? $totalPrice / $item->quantity : 0,
        ]);
    }

    /**
     * Create a new revision of an existing quote
     */
    public function createRevision(Quote $originalQuote): Quote
    {
        return DB::transaction(function () use ($originalQuote) {
            $newQuote = $originalQuote->replicate();
            $newQuote->revision_number = $originalQuote->revision_number + 1;
            $newQuote->parent_quote_id = $originalQuote->id;
            $newQuote->status = 'draft';
            $newQuote->quote_number = $this->generateQuoteNumber($originalQuote->quote_number);
            $newQuote->created_by = Auth::id();
            $newQuote->save();

            // Copy line items
            foreach ($originalQuote->lineItems as $item) {
                $newItem = $item->replicate();
                $newItem->quote_id = $newQuote->id;
                $newItem->save();
            }

            // Log activity
            $this->logActivity($newQuote, 'revision_created',
                "Revision {$newQuote->revision_number} created from {$originalQuote->quote_number}");

            return $newQuote;
        });
    }

    /**
     * Convert accepted quote to project
     */
    public function convertToProject(Quote $quote): Project
    {
        if (!$quote->canConvert()) {
            throw new \Exception('Quote cannot be converted to project');
        }

        return DB::transaction(function () use ($quote) {
            // Create project
            $project = Project::create([
                'job_number' => $this->generateJobNumber(),
                'name' => $quote->project_name,
                'customer_id' => $quote->customer_id,
                'status' => 'awarded',
                'po_number' => null, // Set later
                'contract_weight_lbs' => $quote->estimated_weight_lbs,
                'contract_weight_kg' => $quote->estimated_weight_kg,
                'notes' => "Converted from Quote {$quote->quote_number}\n\n{$quote->notes}",
            ]);

            // Link quote to project
            $quote->update([
                'project_id' => $project->id,
                'status' => 'converted',
            ]);

            // Log activity
            $this->logActivity($quote, 'converted_to_project',
                "Converted to Project {$project->job_number}");

            return $project;
        });
    }

    /**
     * Send quote to customer
     */
    public function sendQuote(Quote $quote, string $recipientEmail): void
    {
        DB::transaction(function () use ($quote, $recipientEmail) {
            $quote->update([
                'status' => 'sent',
                'sent_date' => now(),
            ]);

            $this->logActivity($quote, 'sent', "Quote sent to {$recipientEmail}");

            // TODO: Trigger email notification
            // Mail::to($recipientEmail)->send(new QuoteMail($quote));
        });
    }

    /**
     * Approve quote internally
     */
    public function approveQuote(Quote $quote): void
    {
        DB::transaction(function () use ($quote) {
            $quote->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            $this->logActivity($quote, 'approved', 'Quote approved');
        });
    }

    /**
     * Log activity for audit trail
     */
    protected function logActivity(Quote $quote, string $action, string $description = null, array $metadata = []): void
    {
        $quote->activities()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Generate unique quote number
     */
    protected function generateQuoteNumber(string $baseNumber = null): string
    {
        if ($baseNumber && preg_match('/^(Q\d{6})-R(\d+)$/', $baseNumber, $matches)) {
            // Revision: Q240001-R1 -> Q240001-R2
            return $matches[1] . '-R' . ((int)$matches[2] + 1);
        }

        // New quote: Q240001
        $year = date('y');
        $latest = Quote::where('quote_number', 'like', "Q{$year}%")
            ->whereNull('parent_quote_id')
            ->orderBy('quote_number', 'desc')
            ->value('quote_number');

        if ($latest && preg_match('/^Q\d{2}(\d{4})$/', $latest, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('Q%s%04d', $year, $nextSeq);
    }

    /**
     * Generate unique job number for converted projects
     */
    protected function generateJobNumber(): string
    {
        $year = date('y');
        $latest = Project::where('job_number', 'like', "J{$year}%")
            ->orderBy('job_number', 'desc')
            ->value('job_number');

        if ($latest && preg_match('/^J\d{2}(\d{4})$/', $latest, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('J%s%04d', $year, $nextSeq);
    }
}
```

### Controllers

#### QuoteController

**Location:** `app/Http/Controllers/QuoteController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Quote;
use App\Models\Customer;
use App\Models\Material;
use App\Services\EstimatingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QuoteController extends Controller
{
    public function __construct(
        protected EstimatingService $estimatingService
    ) {}

    /**
     * Display a listing of quotes
     */
    public function index(): Response
    {
        $quotes = Quote::with(['customer', 'createdBy'])
            ->withCount('lineItems')
            ->when(request('status'), fn($q, $status) => $q->where('status', $status))
            ->when(request('search'), fn($q, $search) =>
                $q->where('quote_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
            )
            ->latest('quote_date')
            ->paginate(15);

        return Inertia::render('Quotes/Index', [
            'quotes' => $quotes,
            'filters' => request()->only(['search', 'status']),
            'statuses' => $this->getStatuses(),
        ]);
    }

    /**
     * Show the form for creating a new quote
     */
    public function create(): Response
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $materials = Material::where('is_active', true)
            ->with('grade')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Quotes/Create', [
            'customers' => $customers,
            'materials' => $materials,
            'statuses' => $this->getStatuses(),
        ]);
    }

    /**
     * Store a newly created quote
     */
    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        $quote = Quote::create(array_merge(
            $request->validated(),
            ['created_by' => auth()->id()]
        ));

        $this->estimatingService->logActivity($quote, 'created', 'Quote created');

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Quote created successfully.');
    }

    /**
     * Display the specified quote
     */
    public function show(Quote $quote): Response
    {
        $quote->load([
            'customer',
            'lineItems.material.grade',
            'lineItems.vendor',
            'attachments.uploadedBy',
            'activities.user',
            'createdBy',
            'approvedBy',
        ]);

        return Inertia::render('Quotes/Show', [
            'quote' => $quote,
        ]);
    }

    /**
     * Show the form for editing the specified quote
     */
    public function edit(Quote $quote): Response
    {
        if (!$quote->canEdit()) {
            return redirect()
                ->route('quotes.show', $quote)
                ->with('error', 'Quote cannot be edited in current status.');
        }

        $quote->load('lineItems');

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $materials = Material::where('is_active', true)
            ->with('grade')
            ->get();

        return Inertia::render('Quotes/Edit', [
            'quote' => $quote,
            'customers' => $customers,
            'materials' => $materials,
        ]);
    }

    /**
     * Update the specified quote
     */
    public function update(UpdateQuoteRequest $request, Quote $quote): RedirectResponse
    {
        $quote->update($request->validated());
        $this->estimatingService->calculateQuoteTotals($quote);

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Quote updated successfully.');
    }

    /**
     * Remove the specified quote
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()
            ->route('quotes.index')
            ->with('success', 'Quote deleted successfully.');
    }

    /**
     * Create a new revision
     */
    public function createRevision(Quote $quote): RedirectResponse
    {
        $newQuote = $this->estimatingService->createRevision($quote);

        return redirect()
            ->route('quotes.edit', $newQuote)
            ->with('success', "Revision {$newQuote->revision_number} created.");
    }

    /**
     * Send quote to customer
     */
    public function send(Quote $quote): RedirectResponse
    {
        $this->estimatingService->sendQuote($quote, $quote->contact_email);

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Quote sent successfully.');
    }

    /**
     * Approve quote
     */
    public function approve(Quote $quote): RedirectResponse
    {
        $this->estimatingService->approveQuote($quote);

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Quote approved.');
    }

    /**
     * Convert to project
     */
    public function convertToProject(Quote $quote): RedirectResponse
    {
        $project = $this->estimatingService->convertToProject($quote);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Quote converted to project successfully.');
    }

    /**
     * Get available statuses
     */
    protected function getStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'pending_review' => 'Pending Review',
            'approved' => 'Approved',
            'sent' => 'Sent',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
            'converted' => 'Converted',
            'cancelled' => 'Cancelled',
        ];
    }
}
```

### Routes

**Location:** `routes/web.php`

```php
// Estimating Routes (add to existing routes)
Route::middleware('auth')->group(function () {
    // ... existing routes ...

    // Quote management
    Route::resource('quotes', QuoteController::class);
    Route::post('/quotes/{quote}/revise', [QuoteController::class, 'createRevision'])
        ->name('quotes.revise');
    Route::post('/quotes/{quote}/send', [QuoteController::class, 'send'])
        ->name('quotes.send');
    Route::post('/quotes/{quote}/approve', [QuoteController::class, 'approve'])
        ->name('quotes.approve');
    Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convertToProject'])
        ->name('quotes.convert');

    // Quote line items
    Route::post('/quotes/{quote}/line-items', [QuoteLineItemController::class, 'store'])
        ->name('quotes.line-items.store');
    Route::put('/quote-line-items/{item}', [QuoteLineItemController::class, 'update'])
        ->name('quote-line-items.update');
    Route::delete('/quote-line-items/{item}', [QuoteLineItemController::class, 'destroy'])
        ->name('quote-line-items.destroy');

    // Quote attachments
    Route::post('/quotes/{quote}/attachments', [QuoteAttachmentController::class, 'store'])
        ->name('quotes.attachments.store');
    Route::delete('/quote-attachments/{attachment}', [QuoteAttachmentController::class, 'destroy'])
        ->name('quote-attachments.destroy');
});
```

### Frontend Pages

#### Quotes Index Page

**Location:** `resources/js/Pages/Quotes/Index.vue`

```vue
<template>
  <AppLayout title="Quotes">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Quotes</h1>
          <Link
            :href="route('quotes.create')"
            class="btn btn-primary"
          >
            New Quote
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search quotes..."
              class="form-input"
              @input="search"
            />
            <select
              v-model="filters.status"
              class="form-select"
              @change="search"
            >
              <option value="">All Statuses</option>
              <option
                v-for="(label, value) in statuses"
                :key="value"
                :value="value"
              >
                {{ label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Quotes Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Quote #
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Customer
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Project Name
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Total
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="quote in quotes.data"
                :key="quote.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  <Link
                    :href="route('quotes.show', quote.id)"
                    class="text-blue-600 hover:text-blue-800"
                  >
                    {{ quote.quote_number }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ quote.customer?.name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ quote.project_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(quote.quote_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  ${{ formatNumber(quote.total_price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <StatusBadge :status="quote.status" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="route('quotes.show', quote.id)"
                    class="text-blue-600 hover:text-blue-800 mr-3"
                  >
                    View
                  </Link>
                  <Link
                    v-if="quote.can_edit"
                    :href="route('quotes.edit', quote.id)"
                    class="text-green-600 hover:text-green-800"
                  >
                    Edit
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination :links="quotes.links" class="mt-6" />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Pagination from '@/Components/Pagination.vue';

defineProps({
  quotes: Object,
  filters: Object,
  statuses: Object,
});

const filters = reactive({
  search: '',
  status: '',
});

const search = () => {
  router.get(route('quotes.index'), filters, {
    preserveState: true,
    replace: true,
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(num);
};
</script>
```

---

## Point of Sale Module

### Business Requirements

The POS module enables counter sales operations for:

1. **Walk-in Sales** - Direct customer purchases without project context
2. **Remnant Sales** - Sell unused stock items, drops, and offcuts
3. **Cash Register** - Quick transaction processing
4. **Inventory Deduction** - Real-time stock adjustment
5. **Receipt Generation** - Print/email customer receipts
6. **Payment Processing** - Cash, check, credit card tracking
7. **Daily Close-Out** - End-of-day reconciliation

### Database Schema

#### Migration: `create_pos_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // POS transactions
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 20)->unique();
            $table->foreignId('customer_id')->nullable()->constrained(); // Walk-in can be null
            $table->string('customer_name', 100)->nullable(); // For unregistered customers
            $table->enum('transaction_type', [
                'sale',      // Regular sale
                'return',    // Return/refund
                'void'       // Voided transaction
            ])->default('sale');

            // Financial totals
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Payment
            $table->enum('payment_method', [
                'cash',
                'check',
                'credit_card',
                'debit_card',
                'account',  // Bill to customer account
                'other'
            ])->nullable();
            $table->string('payment_reference', 100)->nullable(); // Check #, Last 4 digits, etc.
            $table->decimal('amount_tendered', 12, 2)->nullable();
            $table->decimal('change_given', 12, 2)->nullable();

            // Status
            $table->enum('status', ['pending', 'completed', 'voided', 'returned'])->default('pending');
            $table->timestamp('completed_at')->nullable();

            // Metadata
            $table->text('notes')->nullable();
            $table->foreignId('cashier_id')->constrained('users'); // Who processed sale
            $table->foreignId('voided_by')->nullable()->constrained('users');
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();

            // Cash register session
            $table->foreignId('register_session_id')->nullable()->constrained('register_sessions');

            $table->timestamps();
            $table->softDeletes();

            $table->index('transaction_number');
            $table->index(['cashier_id', 'created_at']);
            $table->index('status');
        });

        // Transaction line items
        Schema::create('pos_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('pos_transactions')->cascadeOnDelete();
            $table->integer('line_number')->default(0);

            // Item identification
            $table->enum('item_type', [
                'stock',     // Stock item from inventory
                'material',  // New material (not in stock)
                'labor',     // Labor/service charge
                'misc'       // Miscellaneous charge
            ])->default('stock');

            $table->foreignId('stock_item_id')->nullable()->constrained('stock_items');
            $table->foreignId('material_id')->nullable()->constrained();

            // Description
            $table->string('description', 255);
            $table->text('notes')->nullable();

            // Quantity and measurements
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 20)->default('EA');
            $table->decimal('length_ft', 12, 2)->nullable();
            $table->decimal('weight_lbs', 12, 2)->nullable();
            $table->decimal('weight_kg', 12, 2)->nullable();

            // Pricing
            $table->decimal('unit_price', 12, 4)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);

            // Tax
            $table->boolean('is_taxable')->default(true);

            $table->timestamps();

            $table->index(['transaction_id', 'line_number']);
        });

        // Cash register sessions (drawer tracking)
        Schema::create('register_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number', 20)->unique();
            $table->foreignId('register_id')->constrained('registers');
            $table->foreignId('cashier_id')->constrained('users');

            // Session timing
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();

            // Opening cash
            $table->decimal('opening_cash', 12, 2)->default(0);

            // Calculated totals during session
            $table->decimal('cash_sales', 12, 2)->default(0);
            $table->decimal('check_sales', 12, 2)->default(0);
            $table->decimal('card_sales', 12, 2)->default(0);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->integer('transaction_count')->default(0);

            // Closing counts
            $table->decimal('closing_cash_counted', 12, 2)->nullable();
            $table->decimal('expected_cash', 12, 2)->nullable();
            $table->decimal('cash_variance', 12, 2')->nullable();

            // Deposits
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->string('deposit_reference', 100)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cashier_id', 'opened_at']);
        });

        // Physical cash registers
        Schema::create('registers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // "Front Counter", "Shop Floor", etc.
            $table->string('location', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('default_tax_rate', 5, 2)->default(0); // Default sales tax
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Quick sale presets (frequently sold items)
        Schema::create('pos_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('category', 50)->nullable(); // "Remnants", "Consumables", etc.
            $table->text('description')->nullable();

            // Linked item (optional)
            $table->foreignId('material_id')->nullable()->constrained();

            // Default pricing
            $table->decimal('default_price', 12, 4)->default(0);
            $table->string('default_unit', 20)->default('EA');
            $table->boolean('is_taxable')->default(true);

            // Display
            $table->string('button_color', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_presets');
        Schema::dropIfExists('register_sessions');
        Schema::dropIfExists('registers');
        Schema::dropIfExists('pos_transaction_items');
        Schema::dropIfExists('pos_transactions');
    }
};
```

### Eloquent Models

#### POSTransaction Model

**Location:** `app/Models/POSTransaction.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $transaction_number
 * @property int|null $customer_id
 * @property string $transaction_type
 * @property decimal $total
 * @property string $payment_method
 * @property string $status
 */
class POSTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'pos_transactions';

    protected $fillable = [
        'transaction_number',
        'customer_id',
        'customer_name',
        'transaction_type',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'discount_percentage',
        'discount_amount',
        'total',
        'payment_method',
        'payment_reference',
        'amount_tendered',
        'change_given',
        'status',
        'completed_at',
        'notes',
        'cashier_id',
        'voided_by',
        'voided_at',
        'void_reason',
        'register_session_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(POSTransactionItem::class, 'transaction_id')
            ->orderBy('line_number');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function registerSession(): BelongsTo
    {
        return $this->belongsTo(RegisterSession::class);
    }

    /**
     * Check if transaction can be voided
     */
    public function canVoid(): bool
    {
        return in_array($this->status, ['completed']) &&
               $this->created_at->isToday();
    }
}
```

#### POSTransactionItem Model

**Location:** `app/Models/POSTransactionItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSTransactionItem extends Model
{
    protected $table = 'pos_transaction_items';

    protected $fillable = [
        'transaction_id',
        'line_number',
        'item_type',
        'stock_item_id',
        'material_id',
        'description',
        'notes',
        'quantity',
        'unit',
        'length_ft',
        'weight_lbs',
        'weight_kg',
        'unit_price',
        'total_price',
        'discount_percentage',
        'discount_amount',
        'is_taxable',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(POSTransaction::class, 'transaction_id');
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
```

#### RegisterSession Model

**Location:** `app/Models/RegisterSession.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegisterSession extends Model
{
    protected $fillable = [
        'session_number',
        'register_id',
        'cashier_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'cash_sales',
        'check_sales',
        'card_sales',
        'total_sales',
        'transaction_count',
        'closing_cash_counted',
        'expected_cash',
        'cash_variance',
        'deposit_amount',
        'deposit_reference',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
    ];

    public function register(): BelongsTo
    {
        return $this->belongsTo(Register::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(POSTransaction::class, 'register_session_id');
    }

    /**
     * Check if session is currently open
     */
    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }
}
```

### Service Layer

#### POSService

**Location:** `app/Services/POSService.php`

```php
<?php

namespace App\Services;

use App\Models\POSTransaction;
use App\Models\POSTransactionItem;
use App\Models\RegisterSession;
use App\Models\StockItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class POSService
{
    /**
     * Create a new POS transaction
     */
    public function createTransaction(array $data, RegisterSession $session): POSTransaction
    {
        return DB::transaction(function () use ($data, $session) {
            // Create transaction
            $transaction = POSTransaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'customer_id' => $data['customer_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'transaction_type' => 'sale',
                'status' => 'pending',
                'cashier_id' => Auth::id(),
                'register_session_id' => $session->id,
                'tax_percentage' => $data['tax_percentage'] ?? $session->register->default_tax_rate,
            ]);

            return $transaction;
        });
    }

    /**
     * Add item to transaction
     */
    public function addItemToTransaction(
        POSTransaction $transaction,
        array $itemData
    ): POSTransactionItem {
        return DB::transaction(function () use ($transaction, $itemData) {
            $lineNumber = $transaction->items()->max('line_number') + 1;

            $item = $transaction->items()->create([
                'line_number' => $lineNumber,
                'item_type' => $itemData['item_type'],
                'stock_item_id' => $itemData['stock_item_id'] ?? null,
                'material_id' => $itemData['material_id'] ?? null,
                'description' => $itemData['description'],
                'quantity' => $itemData['quantity'],
                'unit' => $itemData['unit'] ?? 'EA',
                'length_ft' => $itemData['length_ft'] ?? null,
                'weight_lbs' => $itemData['weight_lbs'] ?? null,
                'weight_kg' => $itemData['weight_kg'] ?? null,
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                'is_taxable' => $itemData['is_taxable'] ?? true,
            ]);

            // Recalculate transaction totals
            $this->calculateTransactionTotals($transaction);

            return $item;
        });
    }

    /**
     * Calculate transaction totals
     */
    public function calculateTransactionTotals(POSTransaction $transaction): void
    {
        $transaction->load('items');

        $subtotal = $transaction->items->sum('total_price');
        $taxableAmount = $transaction->items
            ->where('is_taxable', true)
            ->sum('total_price');

        $discountAmount = $subtotal * ($transaction->discount_percentage / 100);
        $subtotalAfterDiscount = $subtotal - $discountAmount;

        $taxableAfterDiscount = $taxableAmount *
            (1 - $transaction->discount_percentage / 100);
        $taxAmount = $taxableAfterDiscount * ($transaction->tax_percentage / 100);

        $total = $subtotalAfterDiscount + $taxAmount;

        $transaction->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    /**
     * Complete transaction and process payment
     */
    public function completeTransaction(
        POSTransaction $transaction,
        string $paymentMethod,
        float $amountTendered = null,
        string $paymentReference = null
    ): void {
        DB::transaction(function () use (
            $transaction,
            $paymentMethod,
            $amountTendered,
            $paymentReference
        ) {
            // Calculate change if cash
            $changeGiven = null;
            if ($paymentMethod === 'cash' && $amountTendered) {
                $changeGiven = $amountTendered - $transaction->total;
            }

            $transaction->update([
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentReference,
                'amount_tendered' => $amountTendered,
                'change_given' => $changeGiven,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Process inventory deductions
            foreach ($transaction->items as $item) {
                if ($item->item_type === 'stock' && $item->stock_item_id) {
                    $this->deductStockItem($item);
                }
            }

            // Update register session totals
            $this->updateSessionTotals($transaction->registerSession);
        });
    }

    /**
     * Deduct stock item from inventory
     */
    protected function deductStockItem(POSTransactionItem $item): void
    {
        $stockItem = $item->stockItem;

        if (!$stockItem) {
            return;
        }

        // Mark stock item as used
        $stockItem->update([
            'status' => 'used',
            'quantity_remaining' => $stockItem->quantity_remaining - $item->quantity,
        ]);

        // Create stock movement record
        // (Assuming StockMovement model exists)
        // StockMovement::create([...]);
    }

    /**
     * Void a transaction
     */
    public function voidTransaction(POSTransaction $transaction, string $reason): void
    {
        if (!$transaction->canVoid()) {
            throw new \Exception('Transaction cannot be voided');
        }

        DB::transaction(function () use ($transaction, $reason) {
            // Restore inventory if items were deducted
            foreach ($transaction->items as $item) {
                if ($item->item_type === 'stock' && $item->stock_item_id) {
                    $stockItem = $item->stockItem;
                    $stockItem->update([
                        'status' => 'free',
                        'quantity_remaining' => $stockItem->quantity_remaining + $item->quantity,
                    ]);
                }
            }

            $transaction->update([
                'status' => 'voided',
                'voided_by' => Auth::id(),
                'voided_at' => now(),
                'void_reason' => $reason,
            ]);

            // Update session totals
            $this->updateSessionTotals($transaction->registerSession);
        });
    }

    /**
     * Open register session
     */
    public function openRegisterSession(int $registerId, float $openingCash): RegisterSession
    {
        return RegisterSession::create([
            'session_number' => $this->generateSessionNumber(),
            'register_id' => $registerId,
            'cashier_id' => Auth::id(),
            'opened_at' => now(),
            'opening_cash' => $openingCash,
        ]);
    }

    /**
     * Close register session
     */
    public function closeRegisterSession(
        RegisterSession $session,
        float $closingCashCounted,
        float $depositAmount = null,
        string $depositReference = null
    ): void {
        $expectedCash = $session->opening_cash + $session->cash_sales;
        $cashVariance = $closingCashCounted - $expectedCash;

        $session->update([
            'closed_at' => now(),
            'closing_cash_counted' => $closingCashCounted,
            'expected_cash' => $expectedCash,
            'cash_variance' => $cashVariance,
            'deposit_amount' => $depositAmount,
            'deposit_reference' => $depositReference,
        ]);
    }

    /**
     * Update session totals from transactions
     */
    protected function updateSessionTotals(RegisterSession $session): void
    {
        $transactions = $session->transactions()
            ->where('status', 'completed')
            ->get();

        $session->update([
            'cash_sales' => $transactions->where('payment_method', 'cash')->sum('total'),
            'check_sales' => $transactions->where('payment_method', 'check')->sum('total'),
            'card_sales' => $transactions->whereIn('payment_method', ['credit_card', 'debit_card'])->sum('total'),
            'total_sales' => $transactions->sum('total'),
            'transaction_count' => $transactions->count(),
        ]);
    }

    /**
     * Generate unique transaction number
     */
    protected function generateTransactionNumber(): string
    {
        $date = date('ymd');
        $latest = POSTransaction::where('transaction_number', 'like', "T{$date}%")
            ->orderBy('transaction_number', 'desc')
            ->value('transaction_number');

        if ($latest && preg_match('/^T\d{6}-(\d{4})$/', $latest, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('T%s-%04d', $date, $nextSeq);
    }

    /**
     * Generate unique session number
     */
    protected function generateSessionNumber(): string
    {
        $date = date('ymd');
        $latest = RegisterSession::where('session_number', 'like', "S{$date}%")
            ->orderBy('session_number', 'desc')
            ->value('session_number');

        if ($latest && preg_match('/^S\d{6}-(\d{3})$/', $latest, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('S%s-%03d', $date, $nextSeq);
    }
}
```

### Controllers

#### POSController

**Location:** `app/Http/Controllers/POSController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\POSTransaction;
use App\Models\RegisterSession;
use App\Models\Register;
use App\Models\StockItem;
use App\Models\Material;
use App\Services\POSService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class POSController extends Controller
{
    public function __construct(
        protected POSService $posService
    ) {}

    /**
     * Display POS terminal
     */
    public function terminal(): Response
    {
        $activeSession = RegisterSession::where('cashier_id', auth()->id())
            ->whereNull('closed_at')
            ->with('register')
            ->first();

        if (!$activeSession) {
            return Inertia::render('POS/OpenRegister', [
                'registers' => Register::where('is_active', true)->get(),
            ]);
        }

        // Get quick-access stock items (remnants, frequently sold)
        $stockItems = StockItem::where('status', 'free')
            ->where('is_remnant', true)
            ->with('material.grade')
            ->limit(50)
            ->get();

        $materials = Material::where('is_active', true)
            ->with('grade')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('POS/Terminal', [
            'session' => $activeSession,
            'stockItems' => $stockItems,
            'materials' => $materials,
        ]);
    }

    /**
     * Open register session
     */
    public function openSession(Request $request): RedirectResponse
    {
        $request->validate([
            'register_id' => 'required|exists:registers,id',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $session = $this->posService->openRegisterSession(
            $request->register_id,
            $request->opening_cash
        );

        return redirect()
            ->route('pos.terminal')
            ->with('success', 'Register opened successfully');
    }

    /**
     * Create transaction
     */
    public function createTransaction(Request $request): RedirectResponse
    {
        $activeSession = RegisterSession::where('cashier_id', auth()->id())
            ->whereNull('closed_at')
            ->firstOrFail();

        $transaction = $this->posService->createTransaction(
            $request->all(),
            $activeSession
        );

        return redirect()
            ->route('pos.transaction', $transaction)
            ->with('success', 'Transaction created');
    }

    /**
     * Complete transaction
     */
    public function completeTransaction(Request $request, POSTransaction $transaction): RedirectResponse
    {
        $request->validate([
            'payment_method' => 'required|in:cash,check,credit_card,debit_card,account,other',
            'amount_tendered' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $this->posService->completeTransaction(
            $transaction,
            $request->payment_method,
            $request->amount_tendered,
            $request->payment_reference
        );

        return redirect()
            ->route('pos.terminal')
            ->with('success', 'Transaction completed');
    }

    /**
     * Close register session
     */
    public function closeSession(Request $request): RedirectResponse
    {
        $request->validate([
            'closing_cash_counted' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_reference' => 'nullable|string|max:100',
        ]);

        $session = RegisterSession::where('cashier_id', auth()->id())
            ->whereNull('closed_at')
            ->firstOrFail();

        $this->posService->closeRegisterSession(
            $session,
            $request->closing_cash_counted,
            $request->deposit_amount,
            $request->deposit_reference
        );

        return redirect()
            ->route('dashboard')
            ->with('success', 'Register closed successfully');
    }

    /**
     * Transaction history
     */
    public function transactions(): Response
    {
        $transactions = POSTransaction::with(['customer', 'cashier'])
            ->withCount('items')
            ->latest()
            ->paginate(25);

        return Inertia::render('POS/Transactions', [
            'transactions' => $transactions,
        ]);
    }
}
```

### Routes

**Location:** `routes/web.php`

```php
// POS Routes (add to existing routes)
Route::middleware('auth')->group(function () {
    // ... existing routes ...

    // POS Terminal
    Route::get('/pos', [POSController::class, 'terminal'])->name('pos.terminal');
    Route::post('/pos/open-session', [POSController::class, 'openSession'])
        ->name('pos.open-session');
    Route::post('/pos/close-session', [POSController::class, 'closeSession'])
        ->name('pos.close-session');

    // Transactions
    Route::post('/pos/transactions', [POSController::class, 'createTransaction'])
        ->name('pos.transactions.create');
    Route::get('/pos/transactions', [POSController::class, 'transactions'])
        ->name('pos.transactions.index');
    Route::get('/pos/transactions/{transaction}', [POSController::class, 'showTransaction'])
        ->name('pos.transactions.show');
    Route::post('/pos/transactions/{transaction}/complete', [POSController::class, 'completeTransaction'])
        ->name('pos.transactions.complete');
    Route::post('/pos/transactions/{transaction}/void', [POSController::class, 'voidTransaction'])
        ->name('pos.transactions.void');

    // Transaction items
    Route::post('/pos/transactions/{transaction}/items', [POSTransactionItemController::class, 'store'])
        ->name('pos.transaction-items.store');
    Route::delete('/pos/transaction-items/{item}', [POSTransactionItemController::class, 'destroy'])
        ->name('pos.transaction-items.destroy');
});
```

---

## Integration Points

### Integration with Existing Modules

#### 1. Customer Module
- **Estimating**: Quotes link to `customers` table
- **POS**: Transactions can link to customers or be anonymous

#### 2. Project Module
- **Estimating**: Convert accepted quotes to projects via `EstimatingService::convertToProject()`
- Quote maintains reference via `project_id` foreign key

#### 3. Inventory Module
- **Estimating**: Quote line items reference `materials` table for cost lookups
- **POS**: Transaction items deduct from `stock_items` in real-time
- POS sales create `stock_movements` for audit trail

#### 4. Material Catalog
- Both modules heavily use `materials` and `grades` tables
- Material pricing drives cost calculations

#### 5. BOM System
- Future: Import BOM structure into quote line items for complex estimates

#### 6. Reporting Module
- Add quote analytics: win/loss ratio, average quote value, conversion rate
- Add POS reports: daily sales, cashier performance, inventory turnover

### Database Relationships

```
customers
  ├── quotes (1:many)
  └── pos_transactions (1:many)

quotes
  ├── quote_line_items (1:many)
  ├── quote_attachments (1:many)
  ├── quote_activities (1:many)
  ├── project (1:1, after conversion)
  └── parent_quote (self-referencing for revisions)

quote_line_items
  ├── material (many:1)
  └── vendor (many:1, for subcontractor items)

pos_transactions
  ├── pos_transaction_items (1:many)
  ├── customer (many:1, nullable)
  ├── cashier (many:1 -> users)
  └── register_session (many:1)

pos_transaction_items
  ├── stock_item (many:1, nullable)
  └── material (many:1, nullable)

register_sessions
  ├── register (many:1)
  ├── cashier (many:1 -> users)
  └── pos_transactions (1:many)
```

---

## Implementation Roadmap

### Phase 1: Estimating Foundation (Week 1-2)

**Backend:**
1. Create migration `create_estimating_tables.php`
2. Create models: `Quote`, `QuoteLineItem`, `QuoteAttachment`, `QuoteActivity`
3. Create `EstimatingService` with core methods
4. Create `QuoteController` (CRUD operations)
5. Add routes to `routes/web.php`
6. Create form request validators: `StoreQuoteRequest`, `UpdateQuoteRequest`

**Frontend:**
7. Create pages: `Quotes/Index.vue`, `Quotes/Create.vue`, `Quotes/Show.vue`, `Quotes/Edit.vue`
8. Create components: `QuoteLineItemTable.vue`, `QuoteTotalsCard.vue`

**Testing:**
9. Create factories for `Quote` and `QuoteLineItem`
10. Write feature tests for quote CRUD
11. Write unit tests for `EstimatingService`

### Phase 2: Estimating Advanced Features (Week 3)

1. Implement quote revision workflow
2. Add quote approval system
3. Add quote-to-project conversion
4. Create email templates for quote sending
5. Add PDF generation for quotes
6. Implement quote templates
7. Add quote activity logging

### Phase 3: POS Foundation (Week 4-5)

**Backend:**
1. Create migration `create_pos_tables.php`
2. Create models: `POSTransaction`, `POSTransactionItem`, `RegisterSession`, `Register`, `POSPreset`
3. Create `POSService` with core methods
4. Create `POSController` (terminal and transactions)
5. Add routes for POS operations

**Frontend:**
6. Create pages: `POS/Terminal.vue`, `POS/OpenRegister.vue`, `POS/Transactions.vue`
7. Create components: `POSCart.vue`, `POSPayment.vue`, `POSItemGrid.vue`
8. Implement barcode scanner integration

**Testing:**
9. Create factories for POS models
10. Write feature tests for POS workflows
11. Test inventory deduction logic

### Phase 4: POS Advanced Features (Week 6)

1. Implement register session close-out with variance reporting
2. Add receipt printing (PDF and thermal)
3. Create POS presets for quick sales
4. Add customer search and selection
5. Implement return/refund workflow
6. Add transaction void functionality
7. Create end-of-day reconciliation reports

### Phase 5: Integration & Reporting (Week 7)

1. Connect Estimating to Project conversion workflow
2. Link POS to inventory movement tracking
3. Create reports:
   - Quote pipeline report
   - Win/loss analysis
   - Daily POS sales summary
   - Cashier reconciliation report
4. Add dashboard widgets for both modules
5. Implement email notifications

### Phase 6: Polish & Production (Week 8)

1. Add comprehensive validation
2. Implement permission guards (roles/policies)
3. Add bulk operations (bulk quote approval, etc.)
4. Performance optimization (query optimization, caching)
5. User acceptance testing
6. Documentation and training materials
7. Production deployment

---

## Technical Considerations

### Performance

- **Eager Loading**: Always use `->with()` for relationships to prevent N+1 queries
- **Pagination**: All list views should paginate (15-25 records per page)
- **Indexing**: Database indexes on frequently queried columns (quote_number, transaction_number, status, dates)
- **Caching**: Cache material catalog and pricing for POS terminal

### Security

- **Authorization**: Use Laravel Policies for access control
- **Validation**: Form Request classes for all input validation
- **CSRF Protection**: Inertia handles CSRF automatically
- **Soft Deletes**: Preserve audit trail, never hard delete financial records
- **User Tracking**: Record `created_by`, `updated_by`, `cashier_id` for accountability

### Data Integrity

- **Database Transactions**: Wrap all multi-step operations in `DB::transaction()`
- **Calculations**: Always recalculate totals server-side, never trust client
- **Dual Units**: Maintain both Imperial and Metric consistently
- **Audit Trail**: Quote activities and stock movements provide complete history

### User Experience

- **Responsive Design**: Tailwind CSS for mobile-friendly interfaces
- **Real-time Feedback**: Flash messages for all user actions
- **Keyboard Shortcuts**: POS terminal should support barcode scanner input
- **Autocomplete**: Customer and material search with typeahead
- **Validation Feedback**: Clear error messages on form validation

---

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── QuoteController.php
│   │   ├── QuoteLineItemController.php
│   │   ├── QuoteAttachmentController.php
│   │   ├── POSController.php
│   │   └── POSTransactionItemController.php
│   └── Requests/
│       ├── StoreQuoteRequest.php
│       ├── UpdateQuoteRequest.php
│       ├── StorePOSTransactionRequest.php
│       └── CompletePOSTransactionRequest.php
├── Models/
│   ├── Quote.php
│   ├── QuoteLineItem.php
│   ├── QuoteAttachment.php
│   ├── QuoteActivity.php
│   ├── QuoteTemplate.php
│   ├── POSTransaction.php
│   ├── POSTransactionItem.php
│   ├── RegisterSession.php
│   ├── Register.php
│   └── POSPreset.php
└── Services/
    ├── EstimatingService.php
    └── POSService.php

database/
├── migrations/
│   ├── 2026_01_13_000001_create_estimating_tables.php
│   └── 2026_01_13_000002_create_pos_tables.php
└── seeders/
    ├── QuoteSeeder.php
    ├── RegisterSeeder.php
    └── POSPresetSeeder.php

resources/js/
└── Pages/
    ├── Quotes/
    │   ├── Index.vue
    │   ├── Create.vue
    │   ├── Show.vue
    │   └── Edit.vue
    └── POS/
        ├── Terminal.vue
        ├── OpenRegister.vue
        ├── Transactions.vue
        └── ShowTransaction.vue

routes/
└── web.php (add new routes)

tests/
├── Feature/
│   ├── QuoteTest.php
│   ├── QuoteConversionTest.php
│   ├── POSTransactionTest.php
│   └── RegisterSessionTest.php
└── Unit/
    ├── EstimatingServiceTest.php
    └── POSServiceTest.php
```

---

## Next Steps

1. **Review Architecture**: Stakeholder review of database schema and workflows
2. **Approve Design**: Sign-off on business logic and UI mockups
3. **Begin Development**: Start with Phase 1 (Estimating Foundation)
4. **Iterative Testing**: Unit and feature tests throughout development
5. **User Acceptance Testing**: Engage end users for feedback
6. **Production Deployment**: Staged rollout with training

---

## Notes

- Both modules follow existing SteelFlow MRP patterns for consistency
- Service layer handles all business logic (controllers are thin)
- Inertia.js provides SPA experience without separate API
- Dual-unit weight tracking (Imperial/Metric) maintained throughout
- Soft deletes preserve audit trail for financial records
- Database transactions ensure data integrity
- Comprehensive activity logging for compliance and debugging

---

**Document End**
