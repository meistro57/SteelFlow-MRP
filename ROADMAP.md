# SteelFlow MRP - Development Roadmap

This document tracks the current implementation status and development priorities for SteelFlow MRP.

---

## Current Status Summary

| Component | Status | Notes |
|-----------|--------|-------|
| Database Schema | Complete | 31 migrations, 52 models |
| Backend Services | Complete | 16+ services; UPF & core implemented |
| Filament UI | Implemented | UPF Material Catalog & Admin foundation |
| Authentication | Complete | Laravel Sanctum + Azure OAuth |
| Controllers | In Progress | 21 controllers implemented for web UI |
| Frontend Pages | In Progress | 35 Inertia/Vue pages |
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
| **Contract Docs** | Needed | PDF viewer and versioning control |
| **Nesting** | Partial | Linear nesting visualization complete; Plate visualization pending |
| **Production** | In Progress | Dashboard, barcode scanner, routing, and time entry operational |

### Medium Priority - Feature Completion

| Feature | Status | Description |
|---------|--------|-------------|
| Import UI | Needed | KISS/XSR file upload and preview |
| Shipping UI | Partial | Dashboard live; load builder + BOL generation pending |
| Advanced Reports | Partial | Inventory + BOM reports live; purchasing/production reports pending |
| Dashboard Widgets | Partial | Real-time metrics, project overview |
| Gas Cylinder Tracking | Planned | Database schema, tracking logic, rental billing |
| Service Call Dispatch | Planned | Mobile app, scheduling, field tracking, **Service Ticket Module** |
| Multi-mode POS | Planned | Retail + gas sales + quick jobs support, **integrated sales dashboard** |
| Truck Inventory Management | Planned | Service vehicle stock tracking |
| Recurring Billing | Planned | Monthly cylinder rental automation |
| **Optimize Linear Nesting** | Planned | Advanced yield optimization, remnant integration |
| **UI Editor Module** | Planned | Visual dashboard builder and layout customizer |
| **AuditLog Module** | In Progress | Immutable history and per-record audit views |
| **Documents Module** | In Progress | Centralized document control with versioning and attachments |
| **Authz Module** | In Progress | Role-based access control and policy conventions |
| **Shop Ticket Module** | In Progress | Digital work orders and stage-gate production tracking |
| **NCR & Quality** | Planned | State machine for quality failures (Open -> Review -> Disposition) |
| **Three-Way Match** | Planned | Financial validation for procurement (PO vs. Receipt vs. Invoice) |
| **Progress Billing** | Planned | Construction accounting with AIA standards and retainage |
| Integration Architecture | Planned | End-to-end connectivity blueprint |

### Low Priority - Polish & Optimization

| Item | Status | Description |
|------|--------|-------------|
| Error Handling | Partial | Comprehensive validation feedback |
| Performance | Pending | Query optimization, caching |
| Mobile UI | Pending | Tablet/mobile responsiveness |
| User Preferences | Partial | Dashboard layouts, saved filters |
| API Documentation | Pending | OpenAPI/Swagger specs |

---

## Phase 8: Estimating Module - Planned

The estimating module is a future enhancement:

- [ ] Database schema design
- [ ] Bid & revision management
- [ ] Material takeoff engine
- [ ] Labor standard application
- [ ] Proposal & quote generation (PDF)
- [ ] Bid-to-project conversion

See [docs/ESTIMATING_PLAN.md](docs/ESTIMATING_PLAN.md) for detailed planning.

---

<<<<<<< Updated upstream
## Phases 9-14: Enterprise Resource Planning (ERP) Features - Planned

To evolve from a Manufacturing Operations System to a full ERP platform, the following business management layers are planned:

### Phase 9: Financial Integration & Invoicing (Priority: CRITICAL)
- Customer invoicing (Order-to-Cash cycle)
- Accounts Payable & Receivable
- Tax management and calculation
- Payment tracking
- QuickBooks/Xero integration

### Phase 10: Production Scheduling & Capacity Planning (Priority: HIGH)
- Finite capacity planning
- Gantt chart scheduling
- Machine utilization tracking
- Bottleneck identification
- Maintenance scheduling (CMMS)

### Phase 11: Quality Control & Non-Conformance (Priority: HIGH)
- Non-Conformance Reports (NCR)
- Inspection workflows and checklists
- Scrap tracking and root cause analysis
- RMA (Return Merchandise Authorization)
- Quality metrics dashboard

### Phase 12: Advanced Workforce Management (Priority: MEDIUM)
- Time & attendance by job
- Skill matrices and certifications
- Labor variance analysis (actual vs. estimated)
- Payroll integration

### Phase 13: Supply Chain Enhancement (Priority: MEDIUM)
- Request for Quote (RFQ) workflow
- Vendor quote comparison
- Vendor performance ratings
- On-time delivery tracking

### Phase 14: Engineering Change Management (Priority: MEDIUM)
- Engineering Change Notices (ECN)
- Impact analysis for drawing revisions
- Production hold management
- Drawing supersession workflow

**See [docs/ERP_GAPS_ROADMAP.md](docs/ERP_GAPS_ROADMAP.md) for comprehensive planning, database schemas, services, UI requirements, and implementation timelines.**

**Gap Analysis Summary:**
- **Critical Gap:** Financial/Accounting (no invoicing or AP/AR)
- **High Gap:** Production scheduling (execution only, no planning)
- **High Gap:** Quality control (heat certs only, no defect workflow)
- **Medium Gap:** Advanced procurement (no RFQ or vendor ratings)

**Recommended Implementation Order:**
1. **Stage 1 (Months 1-6):** Phase 9 + Phase 11 - Business Operations Baseline
2. **Stage 2 (Months 7-12):** Phase 10 + Phase 12 - Production Optimization
3. **Stage 3 (Months 13-18):** Phase 13 + Phase 14 + Phase 9 Integration - Enterprise Maturity
=======
## Phase 9: Enterprise Business Logic - Planned

Bridging the gap between shop floor operations and enterprise management.

- [ ] **NCR & Remediation**: State machine for quality failures (Open -> Review -> Disposition).
- [ ] **Three-Way Match**: Financial validation for procurement (PO vs. Receipt vs. Invoice).
- [ ] **Progress Billing**: Construction accounting with AIA standards and retainage calculations.
- [ ] **Accounting Integration**: Webhooks and CSV exports for external GL synchronization.
>>>>>>> Stashed changes

---

## Implementation Priorities

### Immediate (Next Sprint)

1. **BOM Management Interface**
   - [x] AssemblyController with part management
   - [x] Vue pages for assembly/part views
   - [x] Tie project view to assembly/part workflows

2. **Procurement Module**
   - [x] PurchaseOrderController
   - [x] Vendor management (Filament)
   - [x] Material receiving workflow

3. **Inventory Dashboard**
   - [x] Stock tracking interface
   - [x] Movement history views (Filament)
   - [x] Multi-location support

4. **Nesting Interface**
   - [x] Linear visualization
   - [ ] Plate visualization
   - [ ] Cut list generation
   - [ ] Remnant management

5. **Complete Production Tracking**
   - [x] Full barcode scanning app
   - [x] Work area routing interface
   - [x] Labor time entry

6. **Contract Documents Module**
   - [ ] Database schema (documents, versions)
   - [ ] PDF viewing integration
   - [ ] Upload & versioning workflow
   - [ ] Document categorization by project/phase

6. **Shipping Module**
   - Load builder interface
   - BOL and packing list generation
   - Delivery confirmation

---

## Technical Debt

Items to address as development progresses:

- [ ] Add comprehensive API documentation
- [ ] Implement feature tests for all controllers
- [ ] Add E2E tests with Cypress or Playwright
- [ ] Document model relationships diagram
- [ ] Create database schema reference
- [ ] Optimize database queries for large datasets
- [ ] Add Redis caching for reference data

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on contributing to this project.

---

## Recent Updates

### January 12, 2026

**Strategic Focus Update:**
- **Business Logic Layer**: Defined requirements for NCR, Three-Way Match, and Progress Billing modules.
- **Enterprise Foundation**: Scaffolded **Documents**, **AuditLog**, and **Authz** modules to support high-priority business needs.
- **CAD Integration**: Enhanced `KissImporter` to automatically link assemblies to `Drawing` models during import.
- **Audit Trails**: Registered `HasAuditFields` trait and `AuditLog` listener to ensure immutable history across all business models.

### January 11, 2026

**Major Progress Update:**
- **BOM Management**: Completed Assembly and Part CRUD controllers and Vue interfaces.
- **Procurement**: Implemented full Purchase Order and Receiving workflow.
- **Inventory**: Developed comprehensive stock management with material auto-fill from UPF.
- **Nesting**: Linear nesting visualization implemented in Vue.
- **Production**: Routing and Time Entry backends and UIs are now functional.
- **Admin UI**: Filament resources expanded to cover nearly all models.

**Next Steps:**
- Plate Nesting 2D visualization.
- Over-receive validation in procurement.
- Label PDF generation.
- Barcode logic refinement.
- **Contract Documents**: PDF viewer and versioning control implementation.

---

*Last updated: January 2026*
