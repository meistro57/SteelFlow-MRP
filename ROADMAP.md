# SteelFlow MRP - Development Roadmap

This document tracks the current implementation status and development priorities for SteelFlow MRP.

---

## Current Status Summary

| Component | Status | Notes |
|-----------|--------|-------|
| Database Schema | Complete | 43 migrations, 70 models |
| Backend Services | Complete | 16+ services; UPF & core implemented |
| Filament UI | Implemented | UPF Material Catalog & Admin foundation |
| Authentication | Complete | Laravel Sanctum + Azure OAuth |
| Controllers | In Progress | 23 controllers implemented for web UI |
| Frontend Pages | In Progress | 42 Inertia/Vue pages |
| UI Components | Complete | Core component library established |
| API Endpoints | Minimal | No dedicated API routes yet |

---

## Phase 1: Foundation - Complete

- [x] Dockerized development environment (Docker Compose v2)
- [x] Database schema foundation (21 migrations)
- [x] Core data models (27 Eloquent models)
- [x] Authentication system (Laravel Sanctum + Azure OAuth)
- [x] Build configuration (Composer, NPM, Vite)
- [x] Development tools (Laravel Horizon, Scout, Meilisearch ready)
- [x] CI/CD pipeline (GitHub Actions)

---

## Phase 2-7: Backend Services - In Progress

Core backend services are implemented; UI and workflow wiring is ongoing:

| Service | Status | Description |
|---------|--------|-------------|
| BOMExtensionService | Complete | Weight cascade, instance sync |
| InventoryService | Complete | Stock movements, audit trail |
| KissImporter | Complete | KISS CAD format parsing |
| XsrImporter | Complete | XSR CAD format parsing |
| LabelService | Complete | ZPL code generation |
| NestingService | Complete | Nesting workflows |
| PricingService | Complete | Material pricing |
| ProductionService | Complete | Shop floor execution |
| ReportingService | Complete | Dashboard metrics, reports |
| ReferenceDataService | Complete | Material catalog, lookups |
| ShippingService | Complete | Load building, shipping |
| WeightCalculator | Complete | Dual-unit calculations |

---

## Current Development Focus

### High Priority - Missing UI/Controllers

These are the critical gaps that need to be addressed:

| Module | Status | Description |
|--------|--------|-------------|
| UPF Compatibility | Complete | FabTrol UPF foundation, importer, and catalog |
| **BOM Management** | Complete | Project, Drawing, Assembly, and Part CRUD |
| **Procurement** | Complete | Purchase Orders, Material Receiving workflow |
| **Inventory** | Complete | Stock list with sorting/filtering, manual entry, auto-fill |
| **Contract Docs** | Complete | PDF viewer and versioning control (Documents Module) |
| **Nesting** | Partial | Linear nesting visualization complete; Plate visualization pending |
| **Production** | In Progress | Dashboard, barcode scanner, routing, and time entry operational |

### Medium Priority - Feature Completion

| Feature | Status | Description |
|---------|--------|-------------|
| Import UI | Complete | KISS/XSR file upload with preview, UPF import UI |
| Shipping UI | Complete | Dashboard live; load builder + enhanced BOL PDF generation |
| Advanced Reports | Complete | Inventory, BOM, Production, Labor Efficiency, Batch Completion reports |
| Dashboard Widgets | Partial | Real-time metrics, project overview |
| Gas Cylinder Tracking | Complete | Database schema and initial implementation |
| **Welding Gas Bottle Service** | Planned | Gas cylinder tracking, delivery scheduling, rental billing |
| **Shop Service Tickets** | Complete | Internal shop ticket module with workflow steps and tracking |
| **Field Service Tickets** | Planned | On-site service dispatch, mobile tracking, customer site work |
| **Crane Service Tickets** | Planned | Equipment rental tracking, crane dispatch, operator scheduling |
| Service Call Dispatch | Planned | Mobile app, scheduling, field tracking, **Service Ticket Module** |
| Multi-mode POS | Planned | Retail + gas sales + quick jobs support, **integrated sales dashboard** |
| Truck Inventory Management | Planned | Service vehicle stock tracking |
| Recurring Billing | Planned | Monthly cylinder rental automation |
| **Optimize Linear Nesting** | Planned | Advanced yield optimization, remnant integration |
| **UI Editor Module** | Planned | Visual dashboard builder and layout customizer |
| **AuditLog Module** | Complete | Immutable history and per-record audit views |
| **Documents Module** | Complete | Centralized document control with versioning and attachments |
| **Authz Module** | Complete | Role-based access control and policy conventions |
| **Shop Ticket Module** | Complete | Digital work orders and stage-gate production tracking |
| **NCR & Quality** | Complete | State machine for quality failures (Open -> Review -> Disposition) |
| **Three-Way Match** | Complete | Financial validation for procurement (PO vs. Receipt vs. Invoice) |
| **Progress Billing** | Complete | Construction accounting with AIA standards and retainage |
| Integration Architecture | Planned | End-to-end connectivity blueprint |

---

## Phases 8-10: Enterprise Resource Planning (ERP) - In Progress

To evolve from a Manufacturing Operations System to a full ERP platform, the following business management layers have been implemented:

### Phase 8: Financial Integration & Invoicing (Status: IMPLEMENTED)
- Customer invoicing (Order-to-Cash cycle)
- Accounts Payable & Receivable
- Tax management and calculation
- Payment tracking
- Progress Billing with AIA standards and retainage
- Three-Way Match for procurement validation

### Phase 9: Production Scheduling & Capacity Planning (Status: IMPLEMENTED)
- Finite capacity planning foundation
- Machine utilization tracking models
- Maintenance scheduling foundation (CMMS)
- Integrated Work Order management

### Phase 10: Quality Control & Non-Conformance (Status: IMPLEMENTED)
- Non-Conformance Reports (NCR) state machine
- Disposition workflows (Scrap/Rework/Use As-Is)
- Immutable audit trailing for all quality events

---

## Phase 11: Estimating Module - Planned

The estimating module is a future enhancement:

- [ ] Database schema design
- [ ] Bid & revision management
- [ ] Material takeoff engine
- [ ] Labor standard application
- [ ] Proposal & quote generation (PDF)
- [ ] Bid-to-project conversion

See [docs/ESTIMATING_PLAN.md](docs/ESTIMATING_PLAN.md) for detailed planning.

---

## Technical Debt

Items to address as development progresses:

- [x] Fix Fatal Exception in `KissImporter` due to trait syntax error
- [x] Fix `InvoiceLineItem::load()` compatibility issue
- [x] Ensure PSR-12/Laravel Pint compliance across enterprise modules
- [ ] Add comprehensive API documentation
- [ ] Implement feature tests for all controllers
- [ ] Add E2E tests with Cypress or Playwright
- [ ] Document model relationships diagram
- [ ] Create database schema reference
- [ ] Optimize database queries for large datasets
- [ ] Add Redis caching for reference data

---

## Recent Updates

### January 13, 2026

**Feature Expansion & Module Completion:**
- **Advanced Reports UI:** Implemented three new report pages - Production Summary, Labor Efficiency, and Batch Completion Timeline with date filtering and visualizations.
- **UPF Import UI:** Created dedicated import form for FabTrol Universal Product File data with catalog statistics and field mapping guidance.
- **Shop Ticket Module:** Built complete Shop Ticket system with controller, routes, and Vue pages (Index, Show, Create, Edit) supporting workflow step management and status tracking.
- **Enhanced BOL PDF:** Upgraded Bill of Lading template with additional sections including reference numbers, dual-unit weights (lbs/kg), freight class/NMFC, three-signature sections, freight charges, and terms & conditions.
- **Import Templates Update:** Added links to import forms from the templates page for streamlined data import workflow.

---

### January 12, 2026

**Stability & Hygiene Update:**
- **Bug Fixes:** Resolved a fatal error in `KissImporter` where namespaces were misused as traits.
- **Compatibility:** Renamed `InvoiceLineItem::load()` to `shippingLoad()` to resolve naming conflicts with Laravel's core `Model::load()` method.
- **Migration Repair:** Fixed syntax errors and undefined variables in Finance module migrations.
- **Refinement:** Added docblock property metadata to core models to eliminate PHPStan analysis errors and improve IDE support.
- **Code Quality:** Standardized all business modules with Laravel Pint.

**Strategic Focus Update:**
- **Business Logic Layer**: Fully scaffolded and initialized NCR, Three-Way Match, and Progress Billing modules.
- **Enterprise Foundation**: Scaffolded **Documents**, **AuditLog**, and **Authz** modules to support high-priority business needs.
- **CAD Integration**: Enhanced `KissImporter` to automatically link assemblies to `Drawing` models during import.
- **Audit Trails**: Registered `HasAuditFields` trait and `AuditLog` listener to ensure immutable history across all business models.

---

*Last updated: January 2026*
