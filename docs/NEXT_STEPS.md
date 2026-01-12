# SteelFlow MRP - Next Steps

**Date:** January 12, 2026
**Status:** Ready for Implementation

---

## Overview

This document outlines the prioritized next steps for SteelFlow MRP development based on the current roadmap analysis. Tasks are organized by urgency and business impact.

---

## Immediate Priorities (This Sprint)

### 1. Complete Shipping Module UI
**Business Value:** HIGH - Enables revenue recognition and customer delivery tracking

**Tasks:**
- [ ] Build Load Builder page (`resources/js/Pages/Shipping/LoadBuilder.vue`)
- [ ] Implement drag-and-drop assembly-to-load assignment
- [ ] Create BOL (Bill of Lading) generation with PDF export
- [ ] Add load weight validation (truck capacity checks)
- [ ] Create shipping document print templates

**Files to create:**
```
app/Http/Controllers/LoadController.php (enhance existing)
resources/js/Pages/Shipping/LoadBuilder.vue
resources/js/Pages/Shipping/BillOfLading.vue
resources/js/Components/LoadItemTable.vue
```

**Estimated effort:** 3-4 days

---

### 2. KISS/XSR Import UI
**Business Value:** HIGH - Unblocks new project onboarding

**Tasks:**
- [ ] Create file upload page with drag-and-drop
- [ ] Add import preview (parsed BOM structure)
- [ ] Implement validation display (errors/warnings)
- [ ] Create import progress indicator
- [ ] Add import history/log viewer

**Files to create:**
```
app/Http/Controllers/ImportController.php
resources/js/Pages/Import/Index.vue
resources/js/Pages/Import/Preview.vue
resources/js/Components/ImportProgress.vue
```

**Estimated effort:** 2-3 days

---

### 3. Production Reports Dashboard
**Business Value:** HIGH - Management visibility into shop operations

**Tasks:**
- [ ] Create production summary widgets
- [ ] Add assembly status breakdown chart
- [ ] Build department throughput report
- [ ] Create labor efficiency metrics
- [ ] Add project progress tracking

**Files to create/modify:**
```
resources/js/Pages/Reports/Production.vue
resources/js/Components/Charts/ProductionChart.vue
resources/js/Components/Widgets/DepartmentThroughput.vue
app/Http/Controllers/ReportController.php (enhance)
```

**Estimated effort:** 2 days

---

## High Priority (This Month)

### 4. NCR (Non-Conformance Report) UI
**Business Value:** HIGH - Quality control visibility and tracking

**Tasks:**
- [ ] Create NCR list page with filters (status, severity, date range)
- [ ] Build NCR creation form with barcode scan auto-fill
- [ ] Implement NCR detail view with corrective actions
- [ ] Add photo upload for defect documentation
- [ ] Create NCR assignment workflow

**Files to create:**
```
app/Http/Controllers/NCRController.php
resources/js/Pages/Quality/NCRIndex.vue
resources/js/Pages/Quality/NCRCreate.vue
resources/js/Pages/Quality/NCRShow.vue
```

**Estimated effort:** 3-4 days

---

### 5. Invoicing UI Foundation
**Business Value:** HIGH - Complete order-to-cash cycle

**Tasks:**
- [ ] Create invoice list page
- [ ] Build invoice creation from Load
- [ ] Implement line item editing
- [ ] Add tax calculation display
- [ ] Create invoice PDF template
- [ ] Add email send functionality

**Files to create:**
```
app/Http/Controllers/InvoiceController.php
resources/js/Pages/Invoicing/Index.vue
resources/js/Pages/Invoicing/Create.vue
resources/js/Pages/Invoicing/Show.vue
resources/js/Pages/Invoicing/Print.vue
```

**Estimated effort:** 4-5 days

---

### 6. Advanced Purchasing Reports
**Business Value:** MEDIUM-HIGH - Procurement analytics and cost control

**Tasks:**
- [ ] Create vendor performance dashboard
- [ ] Add material cost trending
- [ ] Build PO status summary report
- [ ] Implement spending by project report
- [ ] Create lead time analysis

**Estimated effort:** 2-3 days

---

## Technical Debt (Parallel Track)

### 7. Test Coverage Improvement
**Goal:** 80% test coverage on all controllers

**Tasks:**
- [ ] Add feature tests for ShippingController
- [ ] Add feature tests for ProductionController
- [ ] Add feature tests for NestingController
- [ ] Add unit tests for NestingService
- [ ] Add integration tests for BOM workflows

**Priority:** Run in parallel with feature development

---

### 8. Performance Optimization
**Goal:** All list pages load in < 200ms

**Tasks:**
- [ ] Audit eager loading on all controllers
- [ ] Add Redis caching for Material catalog
- [ ] Add Redis caching for Grade lookups
- [ ] Optimize inventory search queries
- [ ] Add database indexes for common queries

**Priority:** Address before scaling

---

## Infrastructure Tasks

### 9. Add Health Check Endpoint
**Purpose:** Enable monitoring and load balancer checks

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
    ]);
});
```

---

### 10. Create CHANGELOG.md
**Purpose:** Track releases and breaking changes

```markdown
# Changelog

## [Unreleased]

### Added
- NCR module foundation
- Invoicing UI pages

### Changed
- ...

### Fixed
- ...
```

---

## Future Planning (Next Quarter)

### API Foundation
- Design RESTful API structure
- Implement API authentication (Sanctum tokens)
- Create API documentation (OpenAPI/Swagger)
- Build rate limiting

### Estimating Module
- Database migrations
- Quote CRUD operations
- Line item management
- Quote-to-project conversion

### Point of Sale
- Register session management
- Transaction processing
- Inventory deduction
- Receipt generation

---

## Sprint Backlog Template

### Sprint XX: [Date Range]

**Goals:**
1. Complete Shipping Load Builder
2. Implement Import UI
3. Add 10 feature tests

**Capacity:** X story points

| Task | Owner | Status | Points |
|------|-------|--------|--------|
| Load Builder UI | - | Not Started | 5 |
| BOL Generation | - | Not Started | 3 |
| Import Upload | - | Not Started | 3 |
| Import Preview | - | Not Started | 3 |
| Feature tests | - | Not Started | 2 |

**Risks:**
- None identified

**Dependencies:**
- None

---

## Quick Reference: Key Files

### Controllers to Create
```
app/Http/Controllers/LoadController.php (enhance)
app/Http/Controllers/ImportController.php
app/Http/Controllers/NCRController.php
app/Http/Controllers/InvoiceController.php
```

### Vue Pages to Create
```
resources/js/Pages/Shipping/LoadBuilder.vue
resources/js/Pages/Shipping/BillOfLading.vue
resources/js/Pages/Import/Index.vue
resources/js/Pages/Import/Preview.vue
resources/js/Pages/Quality/NCRIndex.vue
resources/js/Pages/Quality/NCRCreate.vue
resources/js/Pages/Quality/NCRShow.vue
resources/js/Pages/Invoicing/Index.vue
resources/js/Pages/Invoicing/Create.vue
resources/js/Pages/Invoicing/Show.vue
resources/js/Pages/Reports/Production.vue
```

### Routes to Add
```php
// Shipping
Route::get('/shipping/load-builder', [LoadController::class, 'builder']);
Route::post('/shipping/loads/{load}/bol', [LoadController::class, 'generateBOL']);

// Import
Route::get('/import', [ImportController::class, 'index']);
Route::post('/import/upload', [ImportController::class, 'upload']);
Route::post('/import/preview', [ImportController::class, 'preview']);
Route::post('/import/execute', [ImportController::class, 'execute']);

// Quality/NCR
Route::resource('ncrs', NCRController::class);
Route::post('/ncrs/{ncr}/assign', [NCRController::class, 'assign']);
Route::post('/ncrs/{ncr}/resolve', [NCRController::class, 'resolve']);

// Invoicing
Route::resource('invoices', InvoiceController::class);
Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);
```

---

## Definition of Done

Each task is complete when:
- [ ] Code implemented and working
- [ ] Feature tests passing
- [ ] Code reviewed (if applicable)
- [ ] Documentation updated
- [ ] No PHPStan errors at level 5
- [ ] ESLint passes on frontend code

---

## Contact

For questions about priorities, contact the project lead.

---

*Last updated: January 12, 2026*
