<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Load;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Services\FinanceService;

class InvoiceController extends Controller
{
    public function __construct(
        protected FinanceService $financeService,
    ) {}

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): Response
    {
        $query = Invoice::with(['project', 'customer']);

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('project', function ($q) use ($search) {
                        $q->where('job_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Calculate summary stats
        $stats = [
            'total_invoices' => Invoice::count(),
            'draft' => Invoice::where('status', 'draft')->count(),
            'sent' => Invoice::where('status', 'sent')->count(),
            'partial' => Invoice::where('status', 'partial')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'total_outstanding' => Invoice::whereIn('status', ['sent', 'partial'])
                ->sum('total_amount') - Invoice::whereIn('status', ['sent', 'partial'])->sum('paid_amount'),
        ];

        $invoices = $query->orderBy('invoice_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Finance::Invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
            'statuses' => $this->getStatuses(),
        ]);
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request): Response
    {
        $projects = Project::with('customer')
            ->where('status', 'active')
            ->orderBy('job_number')
            ->get();

        $loads = null;
        if ($request->filled('project_id')) {
            $loads = Load::where('project_id', $request->project_id)
                ->where('status', 'delivered')
                ->whereDoesntHave('invoiceLineItems')
                ->get();
        }

        return Inertia::render('Finance::Invoices/Create', [
            'projects' => $projects,
            'loads' => $loads,
            'selectedProjectId' => $request->project_id,
        ]);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'load_id' => 'required|exists:loads,id',
        ]);

        try {
            $load = Load::findOrFail($validated['load_id']);

            if ($load->status !== 'delivered') {
                return redirect()
                    ->back()
                    ->with('error', 'Only delivered loads can be invoiced.');
            }

            $invoice = $this->financeService->createInvoiceFromLoad($load);

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to create invoice: '.$e->getMessage());
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'project.customer',
            'lines.shippingLoad',
            'payments',
            'createdBy',
        ]);

        return Inertia::render('Finance::Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Update the specified invoice status.
     */
    public function updateStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,partial,paid,cancelled',
        ]);

        $invoice->update(['status' => $validated['status']]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice status updated successfully.');
    }

    /**
     * Record a payment for an invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->financeService->recordPayment($invoice, $validated);

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'paid' || $invoice->status === 'partial') {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete invoices with payments.');
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Get available invoice statuses.
     */
    protected function getStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'sent' => 'Sent',
            'partial' => 'Partial Payment',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ];
    }
}
