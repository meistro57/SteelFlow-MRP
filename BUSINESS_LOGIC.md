BUSINESS_LOGIC.mdOverviewContext: While the core SteelFlow-MRP handles the physical production (Shape -> Nest -> Cut), we are missing the Business Logic Layer that handles financial validation, quality failures, and complex billing.Goal: Implement the following three modules to close the gap between "Shop Tool" and "Enterprise Software."1. Non-Conformance Reports (NCR) & RemediationThe Logic of Mistakes. When a part is cut wrong, we cannot simply delete it. We must track the loss and trigger a remake.The WorkflowTrigger: Shop user flags a Production_Item as "FAILED."Quarantine: The specific Material_ID (Inventory) used for that item is locked. It cannot be used for other jobs.Disposition (Manager Review):SCRAP: Material is written off. Cost moves to "Scrap Expense."REWORK: An extra operation (e.g., "Grind") is added to the routing.USE AS IS: Engineering approval overrides the fail.Developer Logic (State Machine)Object: NCR_EventStates: OPEN -> UNDER_REVIEW -> DISPOSITIONED -> CLOSEDPseudo-Code for "Scrap" Action:Pythondef execute_scrap_disposition(ncr_id):
    ncr = get_ncr(ncr_id)
    
    # 1. Financial Hit
    create_gl_entry(debit="Expense_Scrap", credit="Asset_Inventory", amount=ncr.cost)
    
    # 2. Inventory Adjustment
    inventory_item = ncr.linked_material
    inventory_item.status = "SCRAPPED" 
    inventory_item.qty_on_hand = 0
    
    # 3. Production Demand (The "Remake" Loop)
    original_part = ncr.linked_part
    new_part = clone_part(original_part)
    new_part.status = "AWAITING_NESTING" # Puts it back in the queue to be cut again
    new_part.priority = "HIGH" # Rush the remake
2. The "Three-Way Match" (Procurement)The Logic of Spending. We cannot pay an invoice unless we are sure we ordered it AND received it.The ConstraintA user cannot mark an Invoice as "Approved for Payment" unless:$$PO_{Price} \times Receipt_{Qty} \approx Invoice_{Total}$$The WorkflowPO Creation: User orders 10,000 lbs of W10x49.Goods Receipt (GR): Truck arrives. User counts beams. CRITICAL: User MUST enter Heat_Number and Mill_Cert_PDF to save the receipt.Invoice Entry: Vendor sends bill for $5,000.The Match: System compares PO vs. GR vs. Invoice.Database Schema UpdatesTable: Purchase_Order_Lineqty_ordered (float)price_per_unit (currency)Table: Goods_Receipt_Lineqty_received (float)heat_number (string) [REQUIRED]po_line_id (FK)Table: Vendor_Invoiceinvoice_amount (currency)match_status (enum: MATCHED, VARIANCE, UNMATCHED)Validation Logic:If (Invoice.amount - (PO.price * GR.qty)) > Tolerance_Threshold ($10.00):Trigger Alert: "Price Variance Detected. Manager Approval Required."3. Progress Billing & Retainage (Construction Accounting)The Logic of Getting Paid. We rarely get paid 100% on delivery. We bill by "Percent Complete" (AIA Billing standards).The ConceptWe don't invoice for "1 Beam." We invoice for "30% of the Steel Budget." We also purposefully withhold 10% (Retainage) until the building is done.The WorkflowSchedule of Values (SOV): The total contract is broken down into buckets (e.g., "Anchor Bolts", "Columns", "Beams").Application for Payment: At end of month, we calculate % complete of those buckets.Retainage Calculation: System automatically holds back 10%.Developer LogicObject: Application_For_PaymentCalculation Formula:$$CurrentDue = (TotalCompleted \% \times TotalValue) - PreviousPayments - Retainage$$Pseudo-Code:Pythondef calculate_invoice_line(sov_item, percent_complete_now):
    total_earned = sov_item.total_value * percent_complete_now
    
    # Calculate Retainage (The money the client holds back)
    retainage_amount = total_earned * sov_item.project.retainage_rate (e.g., 0.10)
    
    # What did we already bill them?
    prior_billed = get_prior_billings(sov_item)
    
    # The actual check amount to ask for
    billable_now = total_earned - retainage_amount - prior_billed
    
    return billable_now
4. Integration Points (The "Hooks")Do not build a full Accounting Ledger. Build "Webhooks" or "Export CSVs" for these events:On Goods_Receipt_Approved:Push data to Quickbooks/Xero (Bill to Pay).On Shipment_Sent:Update Project "Percent Complete" (triggers billing availability).On NCR_Scrap:Push Journal Entry (Inventory Asset Credit / COGS Debit).
