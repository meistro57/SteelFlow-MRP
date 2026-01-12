# SteelFlow MRP - Core Reference

Modern, web-based steel fabrication management system designed as a replacement for legacy FabTrol.

---

## Quick Reference Commands

### Backend (Laravel)

```bash
# Docker environment
docker compose up -d              # Start containers
docker compose down               # Stop containers
docker compose logs -f            # View all logs
docker compose exec app bash      # Access app shell

# Artisan commands (run inside container)
php artisan migrate               # Run migrations
php artisan migrate:fresh --seed  # Reset and seed database
php artisan db:seed               # Seed database
php artisan test                  # Run tests
php artisan tinker                # Interactive REPL
php artisan route:list            # List all routes
php artisan ide-helper:generate   # Generate IDE helpers
```

### Frontend (Vue.js)

```bash
npm run dev    # Development server with hot reload
npm run build  # Production build
npm run lint   # Lint JavaScript/Vue files
```

---

## Core Business Rules

### Weight Calculations
- **Dual Units**: Always store both Imperial (`weight_lbs`) and Metric (`weight_kg`)
- **Formula**: `Weight = Unit Weight x Length x Quantity`
- **Conversions**:
  - 1 ft = 0.3048 m
  - 1 lb = 0.453592 kg
  - 1 kg = 2.20462 lbs

### Data Integrity
- **Transactions**: All imports and critical operations use database transactions
- **Soft Deletes**: Data is marked as deleted, not removed (audit trail)
- **Audit Trail**: All inventory movements tracked with timestamps and user

### Performance Guidelines
- Use eager loading (`with()`) for related models
- Paginate large datasets
- Cache reference data (materials, grades)

---

## Status Workflows

### Stock Item Status
```
free -> assigned -> committed -> used
  |                     |
  +------ return <------+
```
- `free`: Available in warehouse
- `assigned`: Reserved for nesting/production
- `committed`: Confirmed for specific job
- `used`: Cut and consumed

### Assembly Status
```
not_started -> in_progress -> complete -> shipped -> delivered
```

### Nesting Status
```
draft -> approved -> verified -> confirmed
```

### Purchase Order Status
```
draft -> sent -> partial -> received -> closed
```

### Production Batch Status
```
created -> released -> in_progress -> complete
```

### Project Status
```
bidding -> awarded -> active -> on_hold -> complete -> archived
```

---

## Project Structure

### Models (52 total)

#### Project Management
| Model | Description | Key Fields |
|-------|-------------|------------|
| `Project` | Main job/contract | job_number, name, status, ship_date, contract_weight |
| `Phase` | Project phases | project_id, name, sequence |
| `Lot` | Lots within phases | phase_id, name |
| `Customer` | Customer info | name, contact_email, address |

#### BOM & Engineering
| Model | Description | Key Fields |
|-------|-------------|------------|
| `Assembly` | High-level assemblies | mark, weight, assembly_type, main_member_type/size/grade |
| `Part` | Individual parts | part_mark, material, type, size, grade, length, weight |
| `AssemblyInstance` | Assembly instances in project | assembly_id, lot_id, sequence |
| `PartInstance` | Part instances in assembly | part_id, assembly_instance_id, quantity |
| `Drawing` | Engineering drawings | name, file_path, file_type (pdf/dwg/dxf/jpg/png), revised_at |

#### Material & Inventory
| Model | Description | Key Fields |
|-------|-------------|------------|
| `Material` | Material catalog | type, size, grade, unit_weight, price |
| `Grade` | Material grades | name, specification |
| `StockItem` | Warehouse inventory | material_id, status, heat_number, length, cost |
| `StockMovement` | Inventory audit trail | stock_item_id, type, quantity, user_id, notes |
| `ReceivingRecord` | PO receipt tracking | purchase_order_line_id, quantity, heat_number |
| `Vendor` | Supplier information | name, contact_email, address |

#### Procurement
| Model | Description | Key Fields |
|-------|-------------|------------|
| `PurchaseOrder` | PO management | vendor_id, status, order_date, total |
| `PurchaseOrderLine` | PO line items | purchase_order_id, material_id, quantity, unit_price |

#### Nesting/Optimization
| Model | Description | Key Fields |
|-------|-------------|------------|
| `Nesting` | Cutting layouts | type (linear/plate), efficiency, kerf_allowance |
| `NestingBar` | Bars/plates in nesting | nesting_id, stock_item_id, upf_price_id, length, remnant_length |
| `NestingPart` | Parts on bars | nesting_bar_id, part_instance_id, position, length |

#### Legacy & UPF (Universal Product File)
| Model | Description | Key Fields |
|-------|-------------|------------|
| `MaterialType` | Base material sections | type, title, description, pkey |
| `MaterialGrade` | Grade definitions | material_type_id, grade_name, pkey |
| `UpfPrice` | **MASTER PRICE TABLE** | type, size, grade_name, unit_price, weight_per_foot, filekey |
| `LaborRate` | Operation rates | operation_code, rate_per_hour, setup_time_minutes |
| `AutoHandling` | Handling costs | handling_code, cost_per_ton, minimum_charge |
| `UpfStockItem` | Legacy stock tracking | upf_price_id, location, quantity_on_hand |
| `UpfPurchaseOrder` | Legacy PO headers | po_number, vendor_name, total_amount |
| `SystemConfig` | Sequence management | next_filekey, next_orderkey, last_pkey |

#### Production & Shop Floor
| Model | Description | Key Fields |
|-------|-------------|------------|
| `ProductionBatch` | Work order grouping | batch_number, status, project_id |
| `Department` | Department organization | name, code |
| `WorkArea` | Shop floor cells | department_id, name, badge_code |
| `PartWorkArea` | Part routing | part_instance_id, work_area_id, sequence, status, hours |
| `TimeEntry` | Labor tracking | employee_id, work_area_id, hours, quantity |
| `Employee` | Shop floor workforce | name, badge_number, department_id |

#### Shipping & Logistics
| Model | Description | Key Fields |
|-------|-------------|------------|
| `Load` | Shipping loads | load_number, status, bol_number, carrier, weights |
| `LoadItem` | Load items | load_id, assembly_instance_id, weight |
| `ShippingDocument` | Packing lists/BOLs | load_id, type, file_path |

#### System
| Model | Description | Key Fields |
|-------|-------------|------------|
| `User` | Application users | name, email, azure_id, settings (JSON) |

---

### Services

| Service | Purpose |
|---------|---------|
| `BOMExtensionService` | Weight cascade, instance sync, assembly explosion |
| `InventoryService` | Stock movements (receive, assign, commit, use, return, adjust) |
| `KissImporter` | Parse KISS CAD format files |
| `XsrImporter` | Parse XSR CAD format files |
| `FabTrolImporter` | Parse FabTrol UPF CSV files |
| `LabelService` | Generate ZPL code for Zebra printers |
| `NestingService` | Nesting workflow and stock allocation |
| `ProductionService` | Shop floor execution and status tracking |
| `ReportingService` | Dashboard metrics, BOM reports, inventory valuation |
| `ReferenceDataService` | Material catalog, grade lookup, conversions |
| `ShippingService` | Load building, weight rollups, shipping status |
| `WeightCalculator` | Dual-unit weight calculations |
| `UpfService` | Bulk UPF operations (Rename/Copy Type) |
| `PricingService` | UPF-based cost calculations |
| `StockService` | UPF inventory management |
| `KeyGenerationService` | FabTrol PKEY/FILEKEY generation |

---

### Controllers

| Controller | Routes | Purpose |
|-----------|--------|---------|
| `PurchaseOrderController` | `/purchase-orders` | PO management |
| `ReceivingController` | `/inventory/receiving` | Material receiving |
| `AssemblyController` | `/projects/{project}/assemblies` | Assembly management |
| `PartController` | `/assemblies/{assembly}/parts` | Part management |
| `RoutingController` | `/production/routing` | Production routing |
| `TimeEntryController` | `/production/time-entries` | Labor tracking |
| `NestingController` | `/nestings/{nesting}` | Nesting visualization |
| `AuthController` | `/login`, OAuth callback | Microsoft 365 authentication |
| `ReportController` | `/dashboard`, `/reports/*` | Dashboard and reports |
| `ProductionController` | `/scan` | Barcode scanning interface |
| `DrawingController` | `/drawings/{id}` | Drawing management |
| `LabelController` | `/labels/*` | ZPL label generation |
| `SettingsController` | `/settings` | User preferences |

---

## Database Migrations (14 total)

| Migration | Tables |
|-----------|--------|
| `create_users_table` | users, sessions, password_resets |
| `create_cache_table` | cache, cache_locks |
| `create_jobs_table` | jobs, job_batches, failed_jobs |
| `add_azure_id_to_users_table` | users.azure_id |
| `create_projects_tables` | projects, phases, lots |
| `create_master_data_tables` | materials, grades, vendors, customers |
| `create_bom_tables` | assemblies, parts, instances, batches, loads |
| `create_drawings_table` | drawings |
| `create_procurement_tables` | purchase_orders, po_lines, stock_items, receiving_records, stock_movements |
| `create_nesting_tables` | nestings, nesting_bars, nesting_parts |
| `create_production_tracking_tables` | departments, work_areas, part_work_areas, time_entries |
| `create_shipping_tables` | loads, load_items, shipping_documents |
| `add_settings_to_users_table` | users.settings (JSON) |
| `add_soft_deletes_to_tables` | deleted_at timestamps |

---

## Data Migration (Legacy FabTrol)

### Process
1. **Extract**: FabTrol DBF files via `migration_manager.py`
2. **Transform**: SQLite intermediate database (`fabtrol_migration.db`)
3. **Load**: MySQL via `php artisan migrate:fabtrol`

### Source Data
- Location: `S:/Fabtrol/dev/`
- Format: dBase/FoxPro DBF files
- Target: MySQL `steelflow` database

---

## Frontend Architecture

### Vue Pages
| Page | Route | Description |
|------|-------|-------------|
| `Dashboard.vue` | `/` | Main dashboard with stats cards |
| `Production/Scan.vue` | `/scan` | Barcode/QR scanning interface |
| `Reports/Index.vue` | `/reports` | Reports dashboard |
| `Auth/Login.vue` | `/login` | Microsoft OAuth login |
| `Assemblies/Show.vue` | `/assemblies/{id}` | Assembly details |
| `Parts/Show.vue` | `/parts/{id}` | Part details |
| `PurchaseOrders/Index.vue` | `/purchase-orders` | PO list |
| `Nesting/Show.vue` | `/nesting/{id}` | Nesting visualization |
| `Inventory/Index.vue` | `/inventory` | Stock management |

### Vue Components
| Component | Purpose |
|-----------|---------|
| `AppLayout.vue` | Main layout wrapper |
| `ThemeToggle.vue` | Dark/light mode switch |
| `BarcodeScanner.vue` | QR/barcode scanning |
| `NestingVisualizer.vue` | Nesting layout display |
| `NavLink.vue` | Navigation links |

### State Management
- **Pinia** for Vue state management
- **Inertia.js** for SPA-like navigation

---

## Key Features

### Implemented
- Multi-project management (Projects -> Phases -> Lots -> Assemblies -> Parts)
- Dual-unit system (Imperial/Metric)
- CAD file integration (KISS/XSR parsers)
- Inventory tracking with full audit trail
- Procurement module (Purchase Orders & Receiving)
- Manual Inventory management with material auto-fill
- Heat number & mill certificate tracking
- Nesting optimization (linear visualization)
- Barcode/QR code scanning
- ZPL label generation for Zebra printers
- Dark/light theme (persisted to user profile)
- Microsoft 365 OAuth authentication
- Redis caching and queue management

### In Development
- Plate Nesting visualization (2D)
- Receiving validation (Over-receive prevention)
- PDF Label generation
- Advanced production tracking and routing UI
- Shipping module load building
- Advanced reporting

### Planned
- Contract Documents module (PDF viewer & version control)
- Estimating module (Phase 8)
- Optimize Linear Nesting (Advanced yield algorithms)
- Point of Sale (Retail & gas sales)
- UI Editor Module (Visual interface builder)
- Service Ticket & Shop Ticket modules
- API documentation
- Mobile app optimization

---

*Built for the Steel Industry*
