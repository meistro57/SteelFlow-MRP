# SteelFlow MRP - ERP Gaps & Business Management Roadmap

This document addresses the **"business management operational layers"** missing from the current SteelFlow-MRP roadmap. While the current system excels at operational steel fabrication (nesting, heat certs, CAD integration), it lacks the comprehensive business management features found in commercial ERP/MRP systems.

**Document Purpose:** Plan the evolution from a Manufacturing Operations System to a full Enterprise Resource Planning (ERP) platform for steel fabrication.

---

## Gap Analysis Summary

| Feature Category | Commercial Standard | Current SteelFlow | Gap Severity | Priority |
|------------------|---------------------|-------------------|--------------|----------|
| **Accounting/Finance** | Full AP/AR & GL or 2-way sync | Costing & Pricing only | **Critical** | **Immediate** |
| **Production Scheduling** | Finite Capacity & Gantt Charts | Execution Tracking only | **High** | **High** |
| **Quality Control** | NCRs, Inspections, RMAs | Heat Certs & Mill Data | **High** | **High** |
| **Advanced Procurement** | RFQs & Vendor Ratings | POs & Receiving | **Medium** | **Medium** |
| **Workforce Management** | Time & Attendance by Job | Labor Standards only | **Medium** | **Medium** |
| **Engineering Changes** | Hold Management & ECNs | Revision tracking planned | **Medium** | **Medium** |
| **Machine Maintenance** | CMMS with downtime | None | **Low** | **Low** |

---

## Phase 9: Financial Integration & Invoicing
**Priority: CRITICAL | Timeline: 3-4 months**

### Business Value
- **Order-to-Cash Cycle:** Complete the revenue cycle from quote → shipment → invoice → payment
- **Purchase-to-Pay Cycle:** Track vendor invoices and payments against Purchase Orders
- **Financial Visibility:** Real-time project profitability and cash flow tracking

### Database Schema Additions

#### New Tables
```
invoices
├── id (PK)
├── project_id (FK)
├── customer_id (FK)
├── invoice_number (unique)
├── invoice_date
├── due_date
├── subtotal_amount
├── tax_amount
├── total_amount
├── paid_amount
├── status (draft|sent|partial|paid|overdue|void)
├── terms (net30|net60|cod|etc)
├── notes
├── timestamps

invoice_line_items
├── id (PK)
├── invoice_id (FK)
├── load_id (FK) [optional - links to shipped assemblies]
├── description
├── quantity
├── unit_price
├── tax_rate
├── line_total
├── timestamps

vendor_invoices
├── id (PK)
├── purchase_order_id (FK)
├── vendor_id (FK)
├── invoice_number
├── invoice_date
├── due_date
├── amount
├── paid_amount
├── status (pending|approved|paid|disputed)
├── received_date
├── approval_user_id (FK)
├── timestamps

payments
├── id (PK)
├── invoice_id (FK) [or vendor_invoice_id]
├── payment_type (customer_payment|vendor_payment)
├── amount
├── payment_date
├── payment_method (check|wire|card|ach)
├── reference_number
├── notes
├── timestamps

tax_rates
├── id (PK)
├── region (state/province)
├── tax_type (sales|vat|gst)
├── rate (decimal)
├── effective_date
├── timestamps
```

#### Schema Modifications
```sql
-- Add invoicing fields to projects
ALTER TABLE projects ADD COLUMN billing_type ENUM('time_material', 'fixed_price', 'progress_billing');
ALTER TABLE projects ADD COLUMN retention_percentage DECIMAL(5,2) DEFAULT 0;
ALTER TABLE projects ADD COLUMN tax_exempt BOOLEAN DEFAULT false;

-- Add payment tracking to purchase_orders
ALTER TABLE purchase_orders ADD COLUMN invoiced_amount DECIMAL(12,2) DEFAULT 0;
ALTER TABLE purchase_orders ADD COLUMN paid_amount DECIMAL(12,2) DEFAULT 0;
```

### Services Required

#### `InvoicingService`
```php
class InvoicingService
{
    // Invoice Generation
    public function createInvoiceFromLoad(Load $load): Invoice;
    public function createProgressInvoice(Project $project, float $percentComplete): Invoice;
    public function addLineItem(Invoice $invoice, array $lineData): InvoiceLineItem;

    // Tax Calculation
    public function calculateTax(Invoice $invoice): float;
    public function applyRetention(Invoice $invoice): void;

    // Payment Processing
    public function recordPayment(Invoice $invoice, float $amount, array $paymentData): Payment;
    public function markPaid(Invoice $invoice): void;

    // Reports
    public function getAgedReceivables(int $days = 30): Collection;
    public function getUnpaidInvoices(): Collection;
}
```

#### `AccountingIntegrationService`
```php
class AccountingIntegrationService
{
    // QuickBooks Online Integration
    public function pushInvoiceToQBO(Invoice $invoice): bool;
    public function pushVendorInvoiceToQBO(VendorInvoice $vendorInvoice): bool;
    public function syncPayments(): void;

    // Generic Export (CSV/JSON)
    public function exportInvoicesToCSV(Carbon $startDate, Carbon $endDate): string;
    public function exportAPToCSV(Carbon $startDate, Carbon $endDate): string;
}
```

### Controllers & UI Requirements

#### Controllers
- `InvoiceController` - CRUD for customer invoices
- `VendorInvoiceController` - AP invoice management
- `PaymentController` - Payment recording and tracking
- `AccountingReportController` - AR aging, AP aging, cash flow

#### Vue Pages
```
resources/js/Pages/Invoicing/
├── Index.vue              # Invoice list with filters (unpaid, overdue, etc.)
├── Create.vue             # New invoice wizard (select load or manual entry)
├── Edit.vue               # Edit draft invoices
├── Show.vue               # Invoice detail with payment history
├── Print.vue              # Printable invoice template

resources/js/Pages/VendorInvoices/
├── Index.vue              # AP invoice list
├── Create.vue             # Enter vendor invoice against PO
├── Approve.vue            # Approval workflow

resources/js/Pages/Accounting/
├── Dashboard.vue          # AR/AP summary dashboard
├── AgedReceivables.vue    # Aging report (30/60/90 days)
├── CashFlow.vue           # Cash flow projection
```

### Integration Points
- **Shipping Module:** Auto-generate invoice from Load when marked "Delivered"
- **Projects:** Support progress billing milestones (e.g., 25% at start, 50% at mid-point)
- **QuickBooks/Xero API:** 2-way sync for invoices and payments
- **Email Service:** Send invoices as PDF via email

### Implementation Checklist
- [ ] Database migrations for invoices, payments, tax rates
- [ ] InvoicingService with tax calculation and retention logic
- [ ] AccountingIntegrationService with QuickBooks OAuth
- [ ] Invoice PDF template generation
- [ ] Email delivery system for invoices
- [ ] AR/AP reporting dashboards
- [ ] Payment recording UI
- [ ] Tax rate management (Filament admin)
- [ ] Overdue invoice alerts (scheduled job)

---

## Phase 10: Production Scheduling & Capacity Planning
**Priority: HIGH | Timeline: 3-4 months**

### Business Value
- **Realistic Delivery Dates:** Calculate project completion based on available machine/labor capacity
- **Bottleneck Identification:** Highlight overloaded resources before they become critical
- **Resource Optimization:** Balance workload across machines and shifts

### Database Schema Additions

#### New Tables
```
machines
├── id (PK)
├── name (e.g., "Saw #1", "Laser Table A")
├── machine_type (saw|laser|drill|press_brake|welder|grinder)
├── location_id (FK)
├── hourly_capacity (parts/hour or ft/hour)
├── status (operational|maintenance|down)
├── notes
├── timestamps

machine_capabilities
├── id (PK)
├── machine_id (FK)
├── material_type (e.g., "HSS", "Plate")
├── max_length_ft
├── max_thickness_in
├── timestamps

work_orders
├── id (PK)
├── assembly_id (FK)
├── production_batch_id (FK)
├── operation_sequence (1, 2, 3...)
├── operation_type (cut|drill|weld|grind|paint)
├── machine_id (FK)
├── assigned_user_id (FK)
├── estimated_hours
├── actual_hours
├── scheduled_start
├── scheduled_end
├── actual_start
├── actual_end
├── status (pending|scheduled|in_progress|complete|on_hold)
├── priority
├── timestamps

machine_schedules
├── id (PK)
├── machine_id (FK)
├── work_order_id (FK)
├── scheduled_start
├── scheduled_end
├── status (scheduled|in_progress|complete|cancelled)
├── timestamps

maintenance_schedules
├── id (PK)
├── machine_id (FK)
├── maintenance_type (routine|repair|calibration)
├── scheduled_date
├── estimated_downtime_hours
├── completed_date
├── performed_by_user_id (FK)
├── notes
├── timestamps
```

### Services Required

#### `SchedulingService`
```php
class SchedulingService
{
    // Capacity Planning
    public function calculateMachineCapacity(Machine $machine, Carbon $startDate, Carbon $endDate): float;
    public function getAvailableSlots(Machine $machine, int $hoursNeeded, Carbon $startDate): Collection;

    // Work Order Scheduling
    public function scheduleWorkOrder(WorkOrder $workOrder, Machine $machine, Carbon $startTime): bool;
    public function autoScheduleBatch(ProductionBatch $batch): Collection;

    // Bottleneck Analysis
    public function identifyBottlenecks(Carbon $startDate, Carbon $endDate): Collection;
    public function getMachineUtilization(Machine $machine, string $period = 'week'): float;

    // What-If Analysis
    public function simulateSchedule(Project $project, Carbon $targetDelivery): array;
}
```

#### `MaintenanceService`
```php
class MaintenanceService
{
    public function scheduleMaintenance(Machine $machine, Carbon $date, int $hours): MaintenanceSchedule;
    public function markMachineDown(Machine $machine, string $reason): void;
    public function markMachineOperational(Machine $machine): void;
    public function getUpcomingMaintenance(int $days = 7): Collection;
}
```

### Controllers & UI Requirements

#### Controllers
- `MachineController` - Machine CRUD and status management
- `SchedulingController` - Work order scheduling
- `CapacityPlanningController` - Reports and visualizations
- `MaintenanceController` - Machine maintenance tracking

#### Vue Pages
```
resources/js/Pages/Scheduling/
├── GanttChart.vue         # Drag-and-drop Gantt chart (use dhtmlx-gantt or similar)
├── CapacityDashboard.vue  # Machine utilization heatmap
├── BottleneckReport.vue   # Overloaded machines/resources
├── WhatIf.vue             # "What if we start this job on X date?"

resources/js/Pages/Machines/
├── Index.vue              # Machine list with status indicators
├── Show.vue               # Machine detail with schedule timeline
├── Maintenance.vue        # Maintenance schedule calendar

resources/js/Pages/WorkOrders/
├── Index.vue              # Work order queue (sortable by priority/due date)
├── Dispatch.vue           # Assign work orders to machines/operators
```

### Integration Points
- **Production Module:** Work orders generated from Production Batches
- **Nesting Module:** Auto-schedule saw time based on nesting output
- **BOM Module:** Extract operation sequences from assemblies
- **Maintenance:** Block machine capacity during scheduled downtime

### Implementation Checklist
- [ ] Machines and capabilities database schema
- [ ] Work order generation from production batches
- [ ] SchedulingService with capacity calculations
- [ ] Gantt chart UI component integration
- [ ] Machine utilization dashboard
- [ ] Maintenance scheduling CRUD
- [ ] Auto-scheduling algorithm (basic greedy or bin-packing)
- [ ] Bottleneck alerts (scheduled job)
- [ ] Mobile-friendly work order dispatch view

---

## Phase 11: Quality Control & Non-Conformance Management
**Priority: HIGH | Timeline: 2-3 months**

### Business Value
- **Defect Tracking:** Immediate visibility when parts are cut/welded incorrectly
- **Root Cause Analysis:** Identify patterns (e.g., "Saw #2 produces 10% more scrap than Saw #1")
- **Customer Returns:** Handle RMAs and warranty claims systematically

### Database Schema Additions

#### New Tables
```
non_conformance_reports (NCRs)
├── id (PK)
├── ncr_number (unique, auto-generated)
├── project_id (FK)
├── assembly_id (FK) [optional]
├── part_id (FK) [optional]
├── stock_item_id (FK) [optional]
├── reported_by_user_id (FK)
├── reported_date
├── problem_description
├── defect_type (dimension|material|finish|weld|other)
├── severity (minor|major|critical)
├── root_cause
├── corrective_action
├── status (open|under_review|resolved|closed)
├── resolved_by_user_id (FK)
├── resolved_date
├── cost_impact (scrap cost + rework labor)
├── timestamps

inspection_plans
├── id (PK)
├── name (e.g., "Final Weld Inspection")
├── inspection_type (receiving|in_process|final|shipping)
├── applicable_to (material_type or operation_type)
├── timestamps

inspection_checklist_items
├── id (PK)
├── inspection_plan_id (FK)
├── sequence
├── description (e.g., "Check weld bead uniformity")
├── pass_criteria
├── timestamps

inspections
├── id (PK)
├── inspection_plan_id (FK)
├── inspectable_type (Assembly|StockItem|Load)
├── inspectable_id
├── performed_by_user_id (FK)
├── inspection_date
├── status (pending|in_progress|passed|failed|conditional)
├── notes
├── timestamps

inspection_results
├── id (PK)
├── inspection_id (FK)
├── checklist_item_id (FK)
├── result (pass|fail|na)
├── measurement_value (optional numeric value)
├── notes
├── timestamps

rmas (Return Merchandise Authorizations)
├── id (PK)
├── rma_number (unique)
├── customer_id (FK)
├── project_id (FK)
├── issue_date
├── reason (defect|wrong_item|damaged_in_transit|other)
├── status (pending|approved|received|refunded|replaced)
├── resolution_notes
├── credit_amount
├── timestamps

rma_items
├── id (PK)
├── rma_id (FK)
├── assembly_id (FK)
├── quantity
├── description
├── timestamps
```

### Services Required

#### `QualityControlService`
```php
class QualityControlService
{
    // NCR Management
    public function createNCR(array $ncrData): NonConformanceReport;
    public function assignForReview(NonConformanceReport $ncr, User $reviewer): void;
    public function resolveNCR(NonConformanceReport $ncr, string $correctiveAction): void;

    // Scrap Handling
    public function scrapStockItem(StockItem $item, NonConformanceReport $ncr): void;
    public function scrapAssembly(Assembly $assembly, NonConformanceReport $ncr): void;

    // Inspection Workflow
    public function performInspection(InspectionPlan $plan, $inspectable): Inspection;
    public function recordChecklistResult(Inspection $inspection, ChecklistItem $item, string $result): void;
    public function failInspection(Inspection $inspection, string $reason): NonConformanceReport;

    // Analytics
    public function getDefectTrend(string $period = 'month'): array;
    public function getNCRsByMachine(): Collection;
    public function calculateScrapRate(Project $project): float;
}
```

#### `RMAService`
```php
class RMAService
{
    public function createRMA(Customer $customer, array $items, string $reason): RMA;
    public function approveRMA(RMA $rma, User $approver): void;
    public function receiveReturnedItems(RMA $rma): void;
    public function issueCredit(RMA $rma, float $amount): void;
    public function replaceItems(RMA $rma): void;
}
```

### Controllers & UI Requirements

#### Controllers
- `NCRController` - Non-conformance report CRUD
- `InspectionController` - Inspection plans and execution
- `RMAController` - Customer return management
- `QualityReportController` - Defect analytics

#### Vue Pages
```
resources/js/Pages/Quality/
├── NCRIndex.vue           # NCR list (filterable by status, severity, date)
├── NCRCreate.vue          # Quick NCR entry (barcode scan to auto-fill part)
├── NCRShow.vue            # NCR detail with photo upload, corrective actions
├── InspectionPlans.vue    # Manage inspection checklists
├── PerformInspection.vue  # Mobile-friendly checklist interface
├── DefectDashboard.vue    # Defect trends by type, machine, operator
├── ScrapReport.vue        # Scrap cost by project/material

resources/js/Pages/RMA/
├── Index.vue              # RMA list
├── Create.vue             # Customer return entry
├── Process.vue            # RMA approval and resolution workflow
```

### Integration Points
- **Inventory Module:** Automatically mark scrapped items as "used" with negative impact
- **Production Module:** Block assemblies from shipping if inspection fails
- **BOM Module:** Generate replacement material requirements from NCRs
- **Accounting Module:** Track scrap cost impact on project profitability

### Implementation Checklist
- [ ] NCR database schema and model
- [ ] Inspection plans and checklist schema
- [ ] QualityControlService with scrap handling
- [ ] RMA workflow database and service
- [ ] NCR quick-entry UI (mobile-optimized)
- [ ] Inspection checklist mobile UI
- [ ] Defect analytics dashboard
- [ ] Photo upload for NCRs (integrate with Laravel Media Library)
- [ ] Email notifications for NCR assignment
- [ ] Scrap cost tracking integration

---

## Phase 12: Advanced Workforce Management
**Priority: MEDIUM | Timeline: 2-3 months**

### Business Value
- **Job Costing Accuracy:** Track actual labor hours vs. estimates per job
- **Skill-Based Routing:** Ensure high-precision work goes to certified welders
- **Payroll Integration:** Export time data for payroll processing

### Database Schema Additions

#### New Tables
```
employee_skills
├── id (PK)
├── user_id (FK)
├── skill_type (saw_operator|welder|grinder|painter|forklift)
├── certification_level (trainee|journeyman|master|inspector)
├── certification_number
├── certified_date
├── expiration_date
├── timestamps

time_entries
├── id (PK)
├── user_id (FK)
├── work_order_id (FK) [or production_batch_id]
├── clock_in
├── clock_out
├── break_duration_minutes
├── regular_hours
├── overtime_hours
├── hourly_rate
├── labor_cost
├── notes
├── timestamps

shift_schedules
├── id (PK)
├── user_id (FK)
├── shift_type (day|swing|night)
├── scheduled_start
├── scheduled_end
├── location_id (FK)
├── timestamps
```

#### Schema Modifications
```sql
-- Add skill requirements to work orders
ALTER TABLE work_orders ADD COLUMN required_skill VARCHAR(50);
ALTER TABLE work_orders ADD COLUMN required_certification_level VARCHAR(20);

-- Add labor tracking to assemblies
ALTER TABLE assemblies ADD COLUMN estimated_labor_hours DECIMAL(6,2);
ALTER TABLE assemblies ADD COLUMN actual_labor_hours DECIMAL(6,2);
ALTER TABLE assemblies ADD COLUMN labor_cost DECIMAL(10,2);
```

### Services Required

#### `WorkforceService`
```php
class WorkforceService
{
    // Time Tracking
    public function clockIn(User $user, WorkOrder $workOrder): TimeEntry;
    public function clockOut(TimeEntry $entry): void;
    public function calculateLaborCost(TimeEntry $entry): float;

    // Skill Management
    public function assignSkill(User $user, string $skillType, string $level, ?Carbon $expiration): EmployeeSkill;
    public function getCertifiedEmployees(string $skillType, string $minLevel = 'journeyman'): Collection;
    public function getExpiringCertifications(int $days = 30): Collection;

    // Labor Variance
    public function calculateLaborVariance(Assembly $assembly): float; // Actual vs Estimated
    public function getLaborEfficiency(User $user, string $period = 'week'): float;
}
```

### Controllers & UI Requirements

#### Controllers
- `TimeEntryController` - Clock in/out and time sheet management
- `EmployeeSkillController` - Skill certification tracking
- `LaborReportController` - Labor variance and efficiency reports

#### Vue Pages
```
resources/js/Pages/Workforce/
├── TimeClockKiosk.vue     # Barcode scan to clock in/out (full-screen kiosk mode)
├── TimeSheets.vue         # Weekly time sheet view (manager approval)
├── SkillMatrix.vue        # Employee skills grid (rows=employees, cols=skills)
├── CertificationAlerts.vue # Expiring certifications dashboard
├── LaborVariance.vue      # Project-level labor cost vs. estimate

resources/js/Pages/MyTime/
├── Index.vue              # Employee self-service time sheet
```

### Integration Points
- **Production Module:** Auto-assign work orders to qualified employees
- **Scheduling Module:** Respect shift schedules when auto-scheduling
- **Accounting Module:** Export labor cost to accounting system
- **Payroll Systems:** CSV/API export to QuickBooks Time, ADP, etc.

### Implementation Checklist
- [ ] Employee skills database schema
- [ ] Time entry with clock in/out workflow
- [ ] WorkforceService with labor cost calculations
- [ ] Time clock kiosk UI (barcode scanner integration)
- [ ] Skill matrix management (Filament admin)
- [ ] Labor variance reporting
- [ ] Certification expiration alerts (scheduled job)
- [ ] Payroll export functionality (CSV format)
- [ ] Mobile time entry app (PWA)

---

## Phase 13: Supply Chain Enhancement (Advanced Procurement)
**Priority: MEDIUM | Timeline: 2 months**

### Business Value
- **Cost Savings:** Competitive bidding across multiple vendors
- **Vendor Accountability:** Track on-time delivery and quality metrics
- **Supply Continuity:** Identify unreliable vendors before they impact production

### Database Schema Additions

#### New Tables
```
request_for_quotes (RFQs)
├── id (PK)
├── rfq_number (unique)
├── project_id (FK) [optional]
├── requested_by_user_id (FK)
├── due_date
├── status (draft|sent|received|awarded|cancelled)
├── timestamps

rfq_line_items
├── id (PK)
├── rfq_id (FK)
├── material_description
├── shape
├── grade
├── quantity
├── unit
├── notes
├── timestamps

vendor_quotes
├── id (PK)
├── rfq_id (FK)
├── vendor_id (FK)
├── quote_date
├── valid_until
├── total_amount
├── lead_time_days
├── notes
├── status (pending|accepted|rejected)
├── timestamps

vendor_quote_line_items
├── id (PK)
├── vendor_quote_id (FK)
├── rfq_line_item_id (FK)
├── unit_price
├── line_total
├── timestamps

vendor_performance
├── id (PK)
├── vendor_id (FK)
├── period_start
├── period_end
├── total_orders
├── on_time_deliveries
├── late_deliveries
├── defect_rate
├── average_lead_time_days
├── rating (1-5 stars)
├── timestamps
```

#### Schema Modifications
```sql
-- Add performance tracking to vendors
ALTER TABLE vendors ADD COLUMN on_time_delivery_rate DECIMAL(5,2);
ALTER TABLE vendors ADD COLUMN defect_rate DECIMAL(5,2);
ALTER TABLE vendors ADD COLUMN preferred_vendor BOOLEAN DEFAULT false;

-- Add RFQ reference to purchase orders
ALTER TABLE purchase_orders ADD COLUMN rfq_id INT UNSIGNED;
ALTER TABLE purchase_orders ADD FOREIGN KEY (rfq_id) REFERENCES request_for_quotes(id);
```

### Services Required

#### `RFQService`
```php
class RFQService
{
    public function createRFQ(array $lineItems, array $vendorIds): RequestForQuote;
    public function sendToVendors(RequestForQuote $rfq, array $vendorIds): void;
    public function recordVendorQuote(RequestForQuote $rfq, Vendor $vendor, array $quoteData): VendorQuote;
    public function compareQuotes(RequestForQuote $rfq): array; // Side-by-side comparison
    public function awardToVendor(VendorQuote $quote): PurchaseOrder;
}
```

#### `VendorPerformanceService`
```php
class VendorPerformanceService
{
    public function calculateOnTimeRate(Vendor $vendor, Carbon $periodStart, Carbon $periodEnd): float;
    public function calculateDefectRate(Vendor $vendor, Carbon $periodStart, Carbon $periodEnd): float;
    public function updatePerformanceMetrics(Vendor $vendor): VendorPerformance;
    public function rankVendors(string $materialType = null): Collection;
    public function getVendorScorecard(Vendor $vendor): array;
}
```

### Controllers & UI Requirements

#### Controllers
- `RFQController` - RFQ creation and management
- `VendorQuoteController` - Quote entry and comparison
- `VendorPerformanceController` - Vendor scorecards

#### Vue Pages
```
resources/js/Pages/RFQ/
├── Index.vue              # RFQ list
├── Create.vue             # RFQ wizard (add line items, select vendors)
├── Show.vue               # RFQ detail with quote comparison table
├── CompareQuotes.vue      # Side-by-side quote grid (price, lead time, total)

resources/js/Pages/Vendors/
├── Performance.vue        # Vendor scorecard dashboard
├── Ranking.vue            # Vendor ranking by material type
```

### Integration Points
- **Procurement Module:** Convert awarded RFQ to Purchase Order
- **Quality Module:** Link NCRs to vendor performance (defect rate)
- **Receiving Module:** Track delivery dates to calculate on-time rate

### Implementation Checklist
- [ ] RFQ database schema
- [ ] Vendor performance tracking schema
- [ ] RFQService with email distribution
- [ ] Quote comparison UI
- [ ] Vendor performance calculation (scheduled job)
- [ ] Vendor scorecard dashboard
- [ ] Email templates for RFQ distribution
- [ ] Award-to-PO conversion workflow

---

## Phase 14: Engineering Change Management (ECM/ECN)
**Priority: MEDIUM | Timeline: 2 months**

### Business Value
- **Production Safety:** Prevent fabricating to obsolete drawings
- **Audit Trail:** Track who approved changes and when
- **Impact Analysis:** Identify which assemblies are affected by a drawing revision

### Database Schema Additions

#### New Tables
```
engineering_change_notices (ECNs)
├── id (PK)
├── ecn_number (unique)
├── project_id (FK)
├── drawing_id (FK)
├── requested_by_user_id (FK)
├── requested_date
├── reason (design_error|customer_request|material_substitution|other)
├── impact_description
├── status (draft|under_review|approved|rejected|implemented)
├── approved_by_user_id (FK)
├── approved_date
├── implemented_date
├── timestamps

ecn_affected_assemblies
├── id (PK)
├── ecn_id (FK)
├── assembly_id (FK)
├── action_required (rework|scrap|hold|none)
├── timestamps

production_holds
├── id (PK)
├── ecn_id (FK) [optional - can be manual hold too]
├── assembly_id (FK)
├── applied_by_user_id (FK)
├── applied_date
├── hold_reason
├── released_by_user_id (FK)
├── released_date
├── status (active|released)
├── timestamps
```

#### Schema Modifications
```sql
-- Add revision tracking to drawings
ALTER TABLE drawings ADD COLUMN revision_number VARCHAR(10) DEFAULT 'A';
ALTER TABLE drawings ADD COLUMN superseded_by_id INT UNSIGNED;
ALTER TABLE drawings ADD FOREIGN KEY (superseded_by_id) REFERENCES drawings(id);

-- Add hold status to assemblies
ALTER TABLE assemblies ADD COLUMN on_hold BOOLEAN DEFAULT false;
ALTER TABLE assemblies ADD COLUMN hold_reason TEXT;
```

### Services Required

#### `ECNService`
```php
class ECNService
{
    public function createECN(Drawing $drawing, User $requester, string $reason, string $impact): EngineeringChangeNotice;
    public function identifyAffectedAssemblies(EngineeringChangeNotice $ecn): Collection;
    public function applyHold(Assembly $assembly, EngineeringChangeNotice $ecn, string $reason): ProductionHold;
    public function releaseHold(ProductionHold $hold, User $releaser): void;
    public function approveECN(EngineeringChangeNotice $ecn, User $approver): void;
    public function implementECN(EngineeringChangeNotice $ecn): void; // Supersede old drawing, release holds
}
```

### Controllers & UI Requirements

#### Controllers
- `ECNController` - Engineering change notice workflow
- `ProductionHoldController` - Hold management

#### Vue Pages
```
resources/js/Pages/ECN/
├── Index.vue              # ECN list (filterable by status)
├── Create.vue             # ECN creation wizard
├── Review.vue             # Approval workflow with affected assemblies grid
├── ImpactAnalysis.vue     # Show which assemblies/parts are affected

resources/js/Pages/Production/
├── HoldsIndex.vue         # All active holds dashboard
├── ReleaseHold.vue        # Hold release form
```

### Integration Points
- **BOM Module:** Flag assemblies when their drawing is superseded
- **Production Module:** Block work orders for assemblies on hold
- **Contract Docs Module:** Link ECNs to revised PDF drawings

### Implementation Checklist
- [ ] ECN database schema
- [ ] Production hold tracking schema
- [ ] ECNService with impact analysis
- [ ] ECN approval workflow UI
- [ ] Hold alert on production dashboard
- [ ] Automated hold application when ECN is approved
- [ ] Email notifications for ECN approvals
- [ ] Drawing supersession logic

---

## Implementation Priority Matrix

### Immediate (Next 3-6 Months)
**Must-Have for Commercial Viability**

1. **Phase 9: Financial Integration** (3-4 months)
   - **Rationale:** Cannot operate a business without invoicing customers and paying vendors
   - **Quick Win:** Basic invoice generation from loads (1 month)
   - **Full Implementation:** QuickBooks integration (3 months)

2. **Phase 11: Quality Control** (2-3 months)
   - **Rationale:** Shop floor *will* have defects; need immediate way to handle them
   - **Quick Win:** Basic NCR entry (2 weeks)
   - **Full Implementation:** Inspection workflows and RMA (3 months)

### High Priority (6-12 Months)
**Significant Competitive Advantage**

3. **Phase 10: Production Scheduling** (3-4 months)
   - **Rationale:** Customers demand delivery dates; need capacity planning to commit
   - **Quick Win:** Basic work order queue (1 month)
   - **Full Implementation:** Gantt chart and auto-scheduling (4 months)

4. **Phase 12: Workforce Management** (2-3 months)
   - **Rationale:** Accurate job costing requires time tracking by job
   - **Quick Win:** Time clock kiosk (3 weeks)
   - **Full Implementation:** Labor variance analysis (3 months)

### Medium Priority (12-18 Months)
**Industry Best Practices**

5. **Phase 13: Supply Chain Enhancement** (2 months)
   - **Rationale:** Large shops benefit from vendor competition; smaller shops less so
   - **Quick Win:** Vendor performance dashboard (2 weeks)
   - **Full Implementation:** RFQ workflow (2 months)

6. **Phase 14: Engineering Change Management** (2 months)
   - **Rationale:** Critical for complex jobs with frequent revisions
   - **Quick Win:** Manual production holds (1 week)
   - **Full Implementation:** ECN workflow with auto-holds (2 months)

---

## Recommended Phased Approach

### Stage 1: "Business Operations Baseline" (Months 1-6)
**Goal:** Enable basic business management (invoicing, quality tracking)

- Phase 9 (Financial) - Core invoicing only (no accounting integration yet)
- Phase 11 (Quality) - NCR workflow and scrap tracking

**Deliverables:**
- Generate invoices from shipments
- Email invoices to customers
- Record payments
- Create NCRs for defects
- Track scrap costs

**Resources Required:** 1 full-stack developer, 1 part-time QA tester

---

### Stage 2: "Production Optimization" (Months 7-12)
**Goal:** Optimize shop floor efficiency and delivery promises

- Phase 10 (Scheduling) - Capacity planning and work order dispatch
- Phase 12 (Workforce) - Time tracking and skill-based routing

**Deliverables:**
- Gantt chart for project schedules
- Machine utilization reports
- Time clock kiosk
- Labor cost by job

**Resources Required:** 1 full-stack developer, 1 UI/UX designer

---

### Stage 3: "Enterprise Maturity" (Months 13-18)
**Goal:** Match commercial MRP feature parity

- Phase 13 (Supply Chain) - RFQ and vendor performance
- Phase 14 (ECM) - Engineering change workflow
- Phase 9 (Continued) - QuickBooks/Xero integration

**Deliverables:**
- RFQ bidding process
- Vendor scorecards
- ECN approval workflow
- 2-way accounting sync

**Resources Required:** 1 backend developer, 1 integration specialist

---

## Success Metrics

### Phase 9 (Financial)
- **Invoicing Cycle Time:** From shipment to invoice sent < 24 hours
- **AR Aging:** 90% of invoices paid within 30 days
- **Integration Accuracy:** 100% of QuickBooks syncs successful

### Phase 10 (Scheduling)
- **On-Time Delivery:** 95% of projects delivered on or before promised date
- **Machine Utilization:** Increase from ~60% to 80% without overtime
- **Bottleneck Detection:** Identify capacity issues 2+ weeks in advance

### Phase 11 (Quality)
- **NCR Response Time:** NCR created within 1 hour of defect discovery
- **Scrap Rate:** Reduce scrap from industry avg (~3%) to <2%
- **First-Time Quality:** 95% of assemblies pass inspection on first attempt

### Phase 12 (Workforce)
- **Labor Variance:** Within ±10% of estimated labor hours
- **Time Entry Compliance:** 100% of employees clock in/out daily
- **Skill Coverage:** No jobs delayed due to lack of certified personnel

---

## Dependencies & Risks

### Technical Dependencies
- **Phase 9 → Phase 10:** Need invoice data for project profitability in scheduling
- **Phase 10 → Phase 12:** Work orders required for time tracking by job
- **Phase 11 → Phase 9:** Scrap costs must flow to accounting

### External Integrations
- **QuickBooks OAuth:** Requires Intuit developer account and app certification (~1 month)
- **Gantt Chart Library:** Recommend dhtmlx-gantt (commercial license ~$500)
- **Barcode Scanners:** USB HID mode scanners work out-of-box; Bluetooth requires pairing logic

### Risk Mitigation
- **Scope Creep:** Lock Phase 9 features to "core invoicing" only; defer custom payment terms to Phase 2
- **Integration Failures:** Build CSV export fallback for all accounting integrations
- **User Adoption:** Pilot NCR workflow with 1 shop supervisor before full rollout

---

## Next Steps

1. **Stakeholder Review:** Present this roadmap to executive team for prioritization
2. **Resource Allocation:** Assign developers to Phase 9 (Financial Integration)
3. **Database Design Sprint:** Finalize schema for invoices, payments, tax rates (Week 1)
4. **Service Layer Development:** Build InvoicingService (Weeks 2-4)
5. **UI Mockups:** Design invoice list, invoice detail, payment entry screens (Weeks 2-3)
6. **Integration Planning:** Evaluate QuickBooks vs. Xero vs. generic export (Week 4)

---

*Last Updated: January 12, 2026*
*Document Owner: Development Team*
*Review Cycle: Monthly*
