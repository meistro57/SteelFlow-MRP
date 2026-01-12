<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\ApplicationForPayment;
use Modules\Finance\Models\ApplicationLineItem;
use Modules\Finance\Models\ScheduleOfValue;
use Modules\Finance\Models\VendorInvoice;
use Modules\Finance\Models\VendorInvoiceLine;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceLineItem;
use Modules\Finance\Models\Payment;
use App\Models\Load;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceService
{
    /**
     * Create an invoice from a delivered Load.
     */
    public function createInvoiceFromLoad(Load $load): Invoice
    {
        return DB::transaction(function () use ($load) {
            $project = $load->project;
            $customer = $project->customer;

            $invoice = Invoice::create([
                'project_id' => $project->id,
                'customer_id' => $customer->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'subtotal_amount' => 0,
                'total_amount' => 0,
                'status' => 'draft',
            ]);

            $subtotal = 0;
            // Assuming Load has items or we bill based on total weight
            // For now create a single line item for the entire load
            $line = InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'load_id' => $load->id,
                'description' => "Billing for Load #{$load->load_number} - project: {$project->name}",
                'quantity' => 1,
                'unit_price' => $load->total_weight_lbs * 1.50, // Example logic
                'line_total' => $load->total_weight_lbs * 1.50,
            ]);

            $subtotal += $line->line_total;

            $invoice->update([
                'subtotal_amount' => $subtotal,
                'total_amount' => $subtotal, // apply tax later
            ]);

            return $invoice;
        });
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        return 'INV-' . strtoupper(Str::random(8));
    }

    /**
     * Record a customer payment.
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = Payment::create(array_merge($data, [
                'invoice_id' => $invoice->id,
                'payment_type' => 'customer_payment',
            ]));

            $invoice->increment('paid_amount', $data['amount']);

            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partial']);
            }

            return $payment;
        });
    }

    /**
     * Perform Three-Way Match for a Vendor Invoice.
     */
    public function performThreeWayMatch(VendorInvoice $invoice, float $tolerance = 10.00): array
    {
        $results = [];
        $totalPOAmount = 0;
        $totalReceivedAmount = 0;
        $matched = true;

        foreach ($invoice->lines as $line) {
            $poLine = $line->poLine;
            $receivedQty = $poLine->receivingRecords()->sum('quantity_received');
            
            $poAmount = $line->quantity * $poLine->unit_price;
            $receivedAmount = $receivedQty * $poLine->unit_price;
            $invoiceAmount = $line->extended_price;

            $variance = abs($invoiceAmount - $receivedAmount);
            
            $lineStatus = $variance <= $tolerance ? 'matched' : 'variance';
            if ($lineStatus === 'variance') {
                $matched = false;
            }

            $results[] = [
                'po_line_id' => $poLine->id,
                'po_qty' => $poLine->quantity,
                'received_qty' => $receivedQty,
                'invoice_qty' => $line->quantity,
                'po_price' => $poLine->unit_price,
                'invoice_price' => $line->unit_price,
                'variance' => $variance,
                'status' => $lineStatus,
            ];
        }

        $invoice->update([
            'match_status' => $matched ? 'matched' : 'variance'
        ]);

        return [
            'invoice_id' => $invoice->id,
            'status' => $invoice->match_status,
            'lines' => $results
        ];
    }

    /**
     * Create a new Application for Payment (Progress Billing).
     */
    public function createApplication(int $projectId, array $data): ApplicationForPayment
    {
        return DB::transaction(function () use ($projectId, $data) {
            $application = ApplicationForPayment::create([
                'project_id' => $projectId,
                'application_number' => $data['application_number'],
                'application_date' => $data['application_date'],
                'retainage_rate' => $data['retainage_rate'] ?? 10.00,
                'status' => 'draft',
                // other fields initialized to zero
                'total_value' => 0,
                'percent_complete' => 0,
                'retainage_amount' => 0,
                'current_payment_due' => 0,
            ]);

            $totalEarned = 0;
            $totalScheduledValue = 0;

            foreach ($data['lines'] as $lineData) {
                $sov = ScheduleOfValue::findOrFail($lineData['sov_id']);
                $percentComplete = $lineData['percent_complete'];
                
                $earned = $sov->scheduled_value * ($percentComplete / 100);
                $previousEarned = $this->getPreviousEarned($sov->id, $application->id);
                
                ApplicationLineItem::create([
                    'application_id' => $application->id,
                    'sov_id' => $sov->id,
                    'percent_complete' => $percentComplete,
                    'total_earned' => $earned,
                    'previous_earned' => $previousEarned,
                    'current_earned' => $earned - $previousEarned,
                ]);

                $totalEarned += $earned;
                $totalScheduledValue += $sov->scheduled_value;
            }

            $retainageAmount = $totalEarned * ($application->retainage_rate / 100);
            $previousPayments = ApplicationForPayment::where('project_id', $projectId)
                ->where('id', '<', $application->id)
                ->where('status', 'approved')
                ->sum('current_payment_due');

            $application->update([
                'total_value' => $totalScheduledValue,
                'percent_complete' => ($totalScheduledValue > 0) ? ($totalEarned / $totalScheduledValue) * 100 : 0,
                'retainage_amount' => $retainageAmount,
                'previous_payments' => $previousPayments,
                'current_payment_due' => $totalEarned - $retainageAmount - $previousPayments,
            ]);

            return $application;
        });
    }

    protected function getPreviousEarned(int $sovId, int $currentAppId): float
    {
        return ApplicationLineItem::where('sov_id', $sovId)
            ->whereHas('application', function ($query) use ($currentAppId) {
                $query->where('id', '<', $currentAppId)
                    ->where('status', 'approved');
            })
            ->sum('current_earned');
    }
}
