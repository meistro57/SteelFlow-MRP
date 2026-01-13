# SteelFlow MRP - Next Steps

**Date:** January 13, 2026
**Status:** Updated Based on Current Implementation

---

## Overview

SteelFlow MRP has reached a significant milestone with **70+ models, 25+ services, and 11 complete business modules**. The backend architecture is comprehensive and production-ready. The UI Editor Module now enables users to create custom dashboards with drag-and-drop widgets.

**Current Focus:** Bridging frontend gaps and completing user-facing workflows for optimal shop-floor and project management experiences.

---

## 🎯 Strategic Priorities (Q1 2026)

### 1. CAD Import UI (KISS/XSR)
**Business Value:** CRITICAL - Unblocks new project onboarding from engineering files
**Status:** Backend parsers complete; UI missing

**Why Now:** Projects cannot be efficiently created without a streamlined CAD import workflow. Manual BOM entry is too time-consuming for fabricators.

**Implementation Tasks:**
- [ ] Create upload page with drag-and-drop file handling (`resources/js/Pages/Import/Upload.vue`)
- [ ] Build preview page showing parsed BOM structure before import (`resources/js/Pages/Import/Preview.vue`)
- [ ] Add validation feedback (errors, warnings, duplicate detection)
- [ ] Implement progress indicator for large file imports
- [ ] Create import history log with rollback capability
- [ ] Add import controller (`app/Http/Controllers/ImportController.php`)

**Routes to Add:**
```php
Route::get('/import', [ImportController::class, 'index'])->name('import.index');
Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');
Route::get('/import/{import}/preview', [ImportController::class, 'preview'])->name('import.preview');
Route::post('/import/{import}/execute', [ImportController::class, 'execute'])->name('import.execute');
Route::get('/import/history', [ImportController::class, 'history'])->name('import.history');
```

**Estimated Effort:** 4-5 days
**Priority:** HIGH

---

### 2. Plate Nesting Visualization
**Business Value:** HIGH - Complete 2D cutting optimization for plate materials
**Status:** Linear nesting complete; plate nesting backend exists but no UI

**Why Now:** Plate materials represent significant cost in fabrication. Visualization enables operators to validate nesting before cutting, reducing waste.

**Implementation Tasks:**
- [ ] Create 2D canvas visualization component (`resources/js/Components/PlateNestingVisualizer.vue`)
- [ ] Implement SVG-based part placement rendering
- [ ] Add zoom, pan, and rotation controls
- [ ] Display material utilization metrics
- [ ] Build cut list export (PDF/CSV)
- [ ] Integrate with existing NestingController

**Estimated Effort:** 5-6 days
**Priority:** HIGH

---

### 3. Advanced Production Dashboard
**Business Value:** HIGH - Real-time shop floor visibility for management
**Status:** Basic dashboard exists; missing real-time metrics and analytics

**Why Now:** Management needs live visibility into shop operations to make informed scheduling and resource allocation decisions.

**Implementation Tasks:**
- [ ] Create production metrics widgets (throughput, cycle time, WIP)
- [ ] Add department status breakdown (capacity utilization)
- [ ] Build assembly progress tracking by project
- [ ] Implement labor efficiency report
- [ ] Add bottleneck analysis visualization
- [ ] Integrate with existing ReportingService metrics

**Files to Create:**
```
resources/js/Pages/Reports/ProductionDashboard.vue
resources/js/Components/Charts/ThroughputChart.vue
resources/js/Components/Widgets/DepartmentStatus.vue
resources/js/Components/Widgets/WIPSummary.vue
```

**Estimated Effort:** 3-4 days
**Priority:** HIGH

---

### 4. Shipping Module Completion
**Business Value:** HIGH - Complete order-to-cash cycle with load management
**Status:** Backend complete; load builder UI missing

**Why Now:** Without load builder, shipping coordination is manual and error-prone. BOL generation is essential for logistics.

**Implementation Tasks:**
- [ ] Build load builder with drag-and-drop assembly assignment
- [ ] Add truck capacity validation (weight limits)
- [ ] Create BOL (Bill of Lading) PDF generation
- [ ] Implement packing list templates
- [ ] Add delivery confirmation workflow
- [ ] Enhance existing ShippingController

**Files to Create:**
```
resources/js/Pages/Shipping/LoadBuilder.vue
resources/js/Pages/Shipping/BillOfLading.vue
resources/js/Components/LoadCapacityIndicator.vue
```

**Estimated Effort:** 3-4 days
**Priority:** MEDIUM-HIGH

---

### 5. Advanced Purchasing Reports
**Business Value:** MEDIUM-HIGH - Procurement analytics and cost control
**Status:** Basic inventory reports exist; purchasing analytics missing

**Why Now:** Cost control requires visibility into vendor performance, material pricing trends, and spending patterns.

**Implementation Tasks:**
- [ ] Create vendor performance dashboard (on-time delivery, quality)
- [ ] Build material cost trending charts (price history)
- [ ] Add PO status summary report (open/partial/complete)
- [ ] Implement spending by project report
- [ ] Create lead time analysis by vendor/material

**Estimated Effort:** 3 days
**Priority:** MEDIUM

---

## 🔧 Technical Debt & Quality

### API Foundation
**Priority:** MEDIUM - Enables mobile apps and integrations

**Tasks:**
- [ ] Design RESTful API structure following Laravel conventions
- [ ] Implement API authentication with Sanctum tokens
- [ ] Create API resource transformers for consistent responses
- [ ] Build rate limiting and throttling
- [ ] Generate OpenAPI/Swagger documentation
- [ ] Add API versioning (v1 namespace)

**Estimated Effort:** 5-7 days

---

### Test Coverage Improvement
**Goal:** 80% coverage on controllers and services

**Tasks:**
- [ ] Add feature tests for ImportController (CAD imports)
- [ ] Add feature tests for ShippingController (load builder)
- [ ] Add feature tests for UIEditorController (dashboard builder)
- [ ] Add unit tests for NestingService
- [ ] Add integration tests for BOM import workflows
- [ ] Add E2E tests with Pest Dusk for critical user journeys

**Priority:** Run in parallel with feature development
**Estimated Effort:** Ongoing (2-3 tests per feature)

---

### Performance Optimization
**Goal:** All list pages < 200ms load time

**Tasks:**
- [ ] Audit eager loading across all controllers (prevent N+1 queries)
- [ ] Add Redis caching for Material catalog lookups
- [ ] Add Redis caching for Grade reference data
- [ ] Optimize inventory search queries with database indexes
- [ ] Add pagination to all large datasets
- [ ] Implement query result caching for reports

**Priority:** Address before production deployment
**Estimated Effort:** 3-4 days

---

## 🚀 Future Enhancements (Q2 2026)

### Estimating Module
**Scope:** Complete bid management system
- Database schema design for quotes and revisions
- Material takeoff engine with labor standards
- Proposal generation (PDF templates)
- Quote-to-project conversion workflow
- Historical cost analysis for accurate bidding

**Estimated Effort:** 3-4 weeks
**Priority:** PLANNED

---

### Multi-Mode Point of Sale (POS)
**Scope:** Retail counter, gas cylinder sales, quick jobs
- Register session management
- Transaction processing with inventory deduction
- Receipt generation and printing
- Gas cylinder tracking (rental/exchange)
- Quick job invoicing for walk-in customers

**Estimated Effort:** 2-3 weeks
**Priority:** PLANNED

---

### Service Ticket Module
**Scope:** Field maintenance and service dispatch
- Service call creation and assignment
- Mobile-friendly technician interface
- Equipment history tracking
- Parts usage and labor time entry
- Service invoice generation

**Estimated Effort:** 2-3 weeks
**Priority:** PLANNED

---

## 📊 Progress Tracking

### Completed Milestones ✅
- [x] Database schema (43 migrations, 70+ models)
- [x] Service layer (25+ services with comprehensive business logic)
- [x] Authentication & RBAC (Azure OAuth + Sanctum)
- [x] BOM Management (Projects, Assemblies, Parts, Drawings)
- [x] Inventory Module (Stock tracking with audit trail)
- [x] Procurement (Purchase Orders, Receiving, Vendors)
- [x] Linear Nesting visualization
- [x] Production tracking (Barcode scanning, routing)
- [x] Finance & Billing (Invoicing, Three-Way Match, Progress Billing)
- [x] Quality Control (NCR state machine)
- [x] Document Control (Versioned storage with CAD links)
- [x] Audit Trails (Immutable history per model)
- [x] Backup Module (Automated database/file protection)
- [x] UI Editor Module (Custom dashboards with widgets)
- [x] Import Templates (CSV exports for data imports)

### In Progress 🔄
- [ ] CAD Import UI (KISS/XSR)
- [ ] Plate Nesting visualization
- [ ] Advanced Production Dashboard
- [ ] Shipping load builder
- [ ] Purchasing analytics

### Planned 📅
- [ ] API Foundation
- [ ] Estimating Module
- [ ] Multi-mode POS
- [ ] Service Ticket Module
- [ ] Advanced Linear Nesting optimization

---

## 🎯 Sprint Recommendations

### Sprint 1 (2 weeks): CAD Import + Plate Nesting
**Goals:**
1. Complete CAD import workflow (KISS/XSR upload, preview, execute)
2. Implement plate nesting visualization with cut lists
3. Add 10 feature tests for import/nesting workflows

**Deliverables:**
- Functional CAD import UI with validation
- 2D plate nesting visualizer with material efficiency metrics
- Test coverage for critical import paths

---

### Sprint 2 (2 weeks): Production + Shipping
**Goals:**
1. Build advanced production dashboard with real-time metrics
2. Complete shipping load builder with BOL generation
3. Add purchasing analytics reports

**Deliverables:**
- Production dashboard with throughput/WIP/bottleneck analysis
- Shipping load builder with drag-and-drop and capacity validation
- Vendor performance and cost trending reports

---

### Sprint 3 (2 weeks): API + Testing + Performance
**Goals:**
1. Design and implement API v1 foundation
2. Improve test coverage to 70%+
3. Performance optimization pass

**Deliverables:**
- RESTful API with Sanctum authentication
- 70%+ test coverage on controllers
- Sub-200ms page load times with caching

---

## 📋 Definition of Done

Each feature is complete when:
- ✅ Code implemented and working in development environment
- ✅ Feature tests written and passing
- ✅ Manual testing completed (happy path + edge cases)
- ✅ PHPStan analysis passes at level 5 (no errors)
- ✅ ESLint passes on all Vue/JS code
- ✅ Laravel Pint formatting applied
- ✅ Documentation updated (inline comments + README if needed)
- ✅ Code reviewed (if team collaboration)
- ✅ Deployed to staging environment

---

## 🔗 Quick Reference

### Key Documentation
- `README.md` - Project overview and setup
- `ROADMAP.md` - Development status and milestones
- `CRUSH.md` - Core business rules and model reference
- `CLAUDE.md` - Development guidelines and commands
- `docs/ESTIMATING_PLAN.md` - Estimating module design

### Development Commands
```bash
# Backend
docker compose exec app php artisan test --filter=ImportTest
docker compose exec app php artisan test --coverage
vendor/bin/pint --dirty

# Frontend
npm run lint:fix
npm run build

# Full quality check
composer quality
```

---

## 💡 Strategic Notes

**Architecture Strengths:**
- Comprehensive service layer isolates business logic
- Modular design enables independent feature development
- Strong data integrity with transactions and audit trails
- Flexible UI customization via dashboard builder

**Current Gaps:**
- Frontend lags behind backend completeness (many services lack UI)
- No public API for mobile/third-party integrations
- Limited real-time data updates (Inertia v1 constraint)
- Test coverage needs improvement for production readiness

**Recommended Focus:**
- Prioritize user-facing workflows (CAD import, plate nesting)
- Complete existing features before adding new ones
- Build API foundation for future mobile apps
- Invest in test coverage and performance optimization

---

*Last updated: January 13, 2026*
