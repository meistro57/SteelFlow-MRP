<!-- docs/bundled_tasks_plan.md -->
# Bundled Implementation Plan

This plan consolidates the earlier workstreams into three high-level tasks while retaining actionable steps. Each section contains a single task-stub for implementation tracking.

## Task 1: Welding Gas Bottle Rental Service
:::task-stub{title="Implement welding gas bottle rental service"}
1. **Model and data foundations**: Define rental entities (`Customer`, `BottleType`, `BottleUnit`, `RentalContract`, `RentalEvent`, `InspectionRecord`) with statuses for bottle availability and contract lifecycle; enforce uniqueness and soft-deletes; add migrations/seeds.
2. **Workflows and validations**: Build application service for checkout, swap, return, lost/damaged flows with stock validation, customer standing checks, transactional updates, and domain events; include reservation/timeout logic.
3. **Billing and invoicing**: Implement recurring rental charges, deposits, late fees, and tax-aware invoicing with scheduled billing jobs and deposit refund/forfeit rules.
4. **Compliance and tracking**: Schedule inspections, record outcomes, block rentals on safety holds, support QR/serial lookup, and add dashboards/exports for overdue or held units.
5. **Customer communications**: Add notification templates for lifecycle events, wire to domain events with idempotency, and expose portal endpoints for active rentals, balances, and service requests.
:::

## Task 2: Retail Point of Sale (POS) with Stock Adjustment
:::task-stub{title="Implement POS with inventory integration"}
1. **POS and inventory model**: Create `Product`, `Variant` (if needed), `PriceBook`, `TaxRule`, `InventoryItem`, `StockLevel`, `StockAdjustment`, `POSOrder`, `POSPayment` with SKU/barcode constraints and migrations.
2. **Checkout and stock handling**: Build POS service to create orders, reserve stock per location, process payments (cash/gateway), and post sale adjustments atomically; add refund/return flow with reverse adjustments.
3. **Pricing, tax, discounts**: Implement price calculation with tax rules, discounts, coupons, and loyalty credits; ensure tax-inclusive/exclusive totals and rounding rules; receipt displays with tax breakdowns.
4. **Inventory visibility**: Provide queries/dashboards for on-hand/reserved/available stock, reconciliation job for physical counts, audit trails for adjustments, and low-stock alerts with reorder suggestions.
5. **POS UI and peripherals**: Build front-end for scanning/search, cart edits, barcode input, printable/email receipts with QR lookup, and note/plan any offline queue strategy.
:::

## Task 3: Cross-Cutting Concerns
:::task-stub{title="Implement security, audit, testing, and ops readiness"}
1. **Roles and audit**: Define roles/permissions for rental and POS operations; log state-changing actions with actor, timestamps, and diffs; ensure secure payment data handling.
2. **Testing strategy**: Add unit/integration/concurrency tests for pricing, taxes, rental billing, and stock adjustments; provide fixture factories for common entities.
3. **Deployment and operations**: Document environment variables and secrets management; supply migrations/seeds for rollout; set up monitoring for payments/stock/jobs; include backup/restore strategy.
:::
