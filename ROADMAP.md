# SteelFlow MRP - Development Roadmap

This document tracks the current implementation status and development priorities for SteelFlow MRP.

---

## Current Status Summary

| Component | Status | Notes |
|-----------|--------|-------|
| Database Schema | Complete | 14 migrations, 29 models |
| Backend Services | Complete | All core services implemented |
| Authentication | Complete | Laravel Sanctum + Azure OAuth |
| Controllers | Partial | 6 of ~20 planned controllers |
| Frontend Pages | Partial | 4 main pages implemented |
| UI Components | Partial | Core components built |
| API Endpoints | Minimal | Focus has been on web UI |

---

## Phase 1: Foundation - Complete

- [x] Dockerized development environment (Docker Compose v2)
- [x] Database schema foundation (14 migrations)
- [x] Core data models (29 Eloquent models)
- [x] Authentication system (Laravel Sanctum + Azure OAuth)
- [x] Build configuration (Composer, NPM, Vite)
- [x] Development tools (Laravel Horizon, Scout, Meilisearch ready)
- [x] CI/CD pipeline (GitHub Actions)

---

## Phase 2-7: Backend Services - Complete

All core backend services have been implemented:

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
| **BOM Management** | Needed | Project/Assembly/Part CRUD interfaces |
| **Procurement** | Needed | Purchase Orders, Material Receiving |
| **Inventory** | Needed | Stock tracking, movements, locations |
| **Nesting** | Needed | Linear/plate nesting visualization |
| **Production** | Partial | Complete barcode tracking, work routing |

### Medium Priority - Feature Completion

| Feature | Status | Description |
|---------|--------|-------------|
| Import UI | Needed | KISS/XSR file upload and preview |
| Shipping UI | Needed | Load builder, BOL generation |
| Advanced Reports | Needed | BOM, purchasing, production reports |
| Dashboard Widgets | Partial | Real-time metrics, project overview |

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
   - ProjectController with CRUD operations
   - AssemblyController with part management
   - Vue pages for project/assembly/part views

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

*Last updated: January 2026*
