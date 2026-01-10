<!-- TODO.md -->
# SteelFlow MRP TODO

## Sources Reviewed
- `README.md`
- `ROADMAP.md`
- `codex_TODO.md`
- `docs/BACKUP_MODULE_SUMMARY.md`
- `docs/BACKUP_MODULE_ARCHITECTURE.md`
- `docs/GUI_MANAGER.md`
- `docs/MODULE_STRUCTURE_AUDIT.md`
- `docs/ESTIMATING_PLAN.md`

## Installed/Enabled Modules
- **Core** (enabled)
- **Inventory** (enabled)

## Immediate Hygiene (Module System)
- [x] Fix Inventory module autoload path mismatch in `Modules/Inventory/composer.json` (PSR-4 root should be `"Modules\\Inventory\\": ""`).
- [x] Add missing `Modules/Core/composer.json` with proper PSR-4 autoload.
- [x] Complete metadata in all `module.json` files (descriptions, keywords).
- [x] Update `config/modules.php` to set `app_folder` to an empty string.
- [x] Run `composer dump-autoload` after the above changes.

## Near-Term Delivery (Roadmap Priorities)
- [x] **BOM Management UI**: assembly/part CRUD flows and wiring to existing project/drawing views.
  - [x] Added CustomerResource to Filament.
  - [x] Added ProjectResource to Filament (with Assemblies and Drawings relation managers).
  - [x] Added AssemblyResource to Filament (with Parts relation manager).
  - [x] Added PartResource to Filament.
  - [x] Added DrawingResource to Filament (with Assemblies relation manager).
- [x] **Procurement**: PurchaseOrder controller/UI + vendor management + material receiving workflow.
  - [x] Added VendorResource to Filament.
  - [x] Added PurchaseOrderResource to Filament (with Lines relation manager).
  - [x] Implement material receiving action on PO lines (via InventoryService).
- [x] **Inventory Dashboard**: stock list, filter/sort, movement history, multi-location support.
  - [x] Refactored StockItemResource into Filament.
  - [x] Added StockMovementResource for global audit history.
  - [x] Integrated MovementsRelationManager into StockItem view.
  - [x] Added multi-location filtering to stock list.
- [x] **Nesting Interface**: linear/plate visualisation, cut lists, remnant management.
  - [x] Added NestingResource to Filament.
  - [x] Implemented Approve/Confirm actions in NestingResource.
  - [x] Added BarsRelationManager for linear material tracking.
- [x] **Production Tracking**: barcode scanning completion, routing interface, time entry UI.
  - [x] Refactored ProductionItemResource.
  - [x] Added DepartmentResource and WorkAreaResource.
  - [x] Added TimeEntryResource for labor tracking.
  - [x] Added ProductionBatchResource for work order management.
- [x] **Shipping**: load builder UI, BOL/packing list generation, delivery confirmation.
  - [x] Added LoadResource to Filament.
  - [x] Added ItemsRelationManager for load building.
  - [x] Implemented Ship/Deliver actions in LoadResource.

## Backup Module
- [x] Create Backup module and enable it in `modules_statuses.json`.
- [x] Add core services: BackupService, DatabaseBackupService, ExportService, RestoreService, StorageService.
- [x] Add models: Backup, BackupSchedule, DataExport.
- [x] Add migrations for backups, backup_schedules, data_exports.
- [x] Implement artisan commands for backup/create/verify/cleanup/restore.
- [x] Build UI: backups list/detail, schedules, exports, restore flow.
  - [x] Created BackupResource with list/create/view/edit pages
  - [x] Created BackupScheduleResource with list/create/view/edit pages and Backups relation manager
  - [x] Created DataExportResource with list/create/view/edit pages
  - [x] Added restore action to BackupResource
  - [x] Added download actions for backups and exports
  - [x] Added quick-create actions in table headers
- [x] Add notifications for backup completion/failure.
  - [x] Created BackupCompleted and BackupFailed events
  - [x] Created DataExportCompleted and DataExportFailed events
  - [x] Created notification listeners for all events
  - [x] Integrated event dispatching in BackupService and ExportService
  - [x] Registered events and listeners in EventServiceProvider
- [ ] Implement optional cloud storage sync (S3/Azure).
- [ ] Add unit + feature tests for services and controllers.

## UI/Experience Enhancements
- [ ] **GUI Manager**: layout density toggle (compact/spacious).
- [ ] **GUI Manager**: sidebar collapse control.
- [ ] **GUI Manager**: configurable accent colours.
- [ ] **User Preferences**: saved dashboard layouts and filters.
- [ ] **Mobile Responsiveness**: tablet/mobile UI polish.

## Platform Hardening
- [ ] Add comprehensive API documentation (OpenAPI/Swagger).
- [ ] Implement feature tests for all controllers.
- [ ] Add E2E tests (Cypress/Playwright).
- [ ] Document model relationships diagram.
- [ ] Create database schema reference.
- [ ] Optimise queries and add caching for reference data.

## Future Modules (Codex + Roadmap)
- [ ] **Authz Module**: roles, permissions, policy conventions, Filament integration.
- [ ] **AuditLog Module**: immutable history, per-record audit views.
- [ ] **Filament Shell**: role-based access + default dashboard + dense UI conventions.
- [ ] **Documents Module**: tagging, versioning, attachment support.
- [ ] **PdfCenter**: Blade template pipeline and first PDF generation.
- [ ] **ModelViewer**: GLB/GLTF viewer with permissions.
- [ ] **Dashboards + Kanban**: KPIs and workflow boards.
- [ ] **Scheduling**: calendar views for deliveries, inspections, pours, crane days.
- [ ] **Estimating Module**: schema design, bid management, takeoff engine, quote PDFs.
