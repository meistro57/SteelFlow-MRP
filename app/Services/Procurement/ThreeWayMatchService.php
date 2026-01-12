<?php

namespace App\Services\Procurement;

use App\Models\VendorInvoice;
use Illuminate\Support\Facades\Log;

class ThreeWayMatchService
{
    protected float $toleranceThreshold = 10.00;

    /**
     * Perform three-way match for an invoice against PO and Receipts.
     */
    public function performMatch(VendorInvoice $invoice): string
    {
        $po = $invoice->purchaseOrder;

        // Sum theoretical value of all receipts for this PO
        // PO Price * Receipt Qty
        $receiptTotalValue = 0;

        foreach ($po->lines as $line) {
            $qtyReceived = $line->receivingRecords()->sum('quantity_received');
            $lineValue = $qtyReceived * $line->unit_price;
            $receiptTotalValue += $lineValue;

            // Per-line status check
            $this->updateLineMatchStatus($line, $qtyReceived);
        }

        $variance = abs($invoice->amount - $receiptTotalValue);

        if ($variance <= $this->toleranceThreshold) {
            $status = 'matched';
        } else {
            $status = 'variance';
            Log::warning("Price Variance Detected for Invoice {$invoice->invoice_number}. Variance: {$variance}", [
                'invoice_id' => $invoice->id,
                'po_id' => $po->id,
                'invoice_amount' => $invoice->amount,
                'receipt_value' => $receiptTotalValue,
            ]);
        }

        $invoice->update(['match_status' => $status]);

        return $status;
    }

    protected function updateLineMatchStatus($line, $qtyReceived): void
    {
        if ($qtyReceived >= $line->quantity) {
            $line->update(['match_status' => 'received_full']);
        } elseif ($qtyReceived > 0) {
            $line->update(['match_status' => 'received_partial']);
        }
    }
}
