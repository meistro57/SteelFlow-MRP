# SteelFlow MRP - Development Roadmap

This document tracks the current implementation status and development priorities for SteelFlow MRP.

---

## Current Status Summary

| Component | Status | Notes |
|-----------|--------|-------|
| Database Schema | Complete | 21 migrations, 27 models |
| Backend Services | In Progress | Core services implemented; wiring and UI integration ongoing |
| Authentication | Complete | Laravel Sanctum + Azure OAuth |
| Controllers | In Progress | 11 controllers implemented for web UI |
| Frontend Pages | In Progress | 21 Inertia/Vue pages (projects, drawings, customers, production, shipping, reports) |
| UI Components | Partial | Core components built |
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
| **BOM Management** | Partial | Project & drawing CRUD exist; assembly/part CRUD needed |
| **Procurement** | Needed | Purchase Orders, Material Receiving |
| **Inventory** | Needed | Stock list with sorting/filtering, manual entry |
| **Nesting** | Needed | Linear/plate nesting visualization |
| **Production** | Partial | Dashboard, barcode scanner operational; routing/time entry pending |

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
| **Shop Ticket Module** | In Progress | Digital work orders and stage-gate production tracking |
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

## Implementation Priorities

### Immediate (Next Sprint)

1. **BOM Management Interface**
   - AssemblyController with part management
   - Vue pages for assembly/part views
   - Tie project view to assembly/part workflows

2. **Procurement Module**
   - PurchaseOrderController
   - Vendor management
   - Material receiving workflow

### Short-term

3. **Inventory Dashboard**
   - Stock tracking interface
   - Movement history views
   - Multi-location support

4. **Nesting Interface**
   - Visual nesting editor
   - Cut list generation
   - Remnant management

### Medium-term

5. **Complete Production Tracking**
   - Full barcode scanning app
   - Work area routing interface
   - Labor time entry

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

### January 9, 2026

**Status Review:**
- Project and drawing CRUD UIs are available
- Production dashboard and barcode scan pages are live
- Shipping dashboard has load metrics and sortable lists
- Inventory and project BOM reports are available

---

*Last updated: January 2026*
