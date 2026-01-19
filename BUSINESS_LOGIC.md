# SteelFlow MRP - Business Logic Layer

## Overview
While the core SteelFlow-MRP handles physical production (Shape -> Nest -> Cut), the Business Logic Layer provides financial validation, quality control, and complex construction billing.

**Status:** The following enterprise modules are fully implemented and integrated.

## 1. Non-Conformance Reports (NCR) & Remediation
**The Logic of Mistakes.** (Implemented via `NCR` Module)

When a part is cut incorrectly or fails inspection, the system tracks the loss and triggers the remake workflow.

### The Workflow
- **Trigger:** Shop user flags a `ProductionItem` as "FAILED."
- **Quarantine:** The specific `Material` (Inventory) used is locked.
- **Disposition:** Manager review with three possible outcomes:
    - **SCRAP:** Material is written off. Cost moves to "Scrap Expense."
    - **REWORK:** An extra operation (e.g., "Grind") is added to the routing.
    - **USE AS IS:** Engineering approval overrides the failure.

### State Machine
- **Object:** `NCR`
- **States:** `OPEN` -> `UNDER_REVIEW` -> `DISPOSITIONED` -> `CLOSED`

---

## 2. The "Three-Way Match" (Procurement)
**The Logic of Spending.** (Implemented via `ThreeWayMatchService`)

Ensures financial integrity by validating that invoices match what was ordered and received.

### The Constraint
An invoice cannot be approved for payment unless:
`PO_Price * Receipt_Qty ≈ Invoice_Total`

### The Workflow
1. **PO Creation:** User orders material (e.g., 10,000 lbs of W10x49).
2. **Goods Receipt (GR):** Truck arrives. User records quantity and mandatory **Heat Number** / **Mill Cert PDF**.
3. **Invoice Entry:** Vendor invoice is recorded in the system.
4. **The Match:** `ThreeWayMatchService` compares PO vs. GR vs. Invoice.
    - If variance exceeds the threshold (e.g., $10.00), manager approval is required.

---

## 3. Progress Billing & Retainage (Construction Accounting)
**The Logic of Getting Paid.** (Implemented via `Finance` Module)

Supports industry-standard AIA billing by "Percent Complete" rather than simple piece-count.

### The Concepts
- **Schedule of Values (SOV):** Contract breakdown into billable buckets.
- **Application for Payment:** Monthly calculation of earned value.
- **Retainage:** Automated withholding (typically 10%) until project completion.

### Calculation Formula
`Current Due = (Total Earned % * Total Value) - Previous Payments - Retainage`

---

## 4. Integration & Hooks
The system generates events and audit trails for all business logic actions:
- **Goods Receipt Approved:** Triggers liability for payment.
- **Shipment Sent:** Updates project "Percent Complete" for billing.
- **NCR Scrap:** Triggers Inventory write-off and remake demand.
