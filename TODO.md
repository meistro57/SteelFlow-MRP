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
- [ ] Fix Inventory module autoload path mismatch in `Modules/Inventory/composer.json` (PSR-4 root should be `"Modules\\Inventory\\": ""`).
- [ ] Add missing `Modules/Core/composer.json` with proper PSR-4 autoload.
- [ ] Complete metadata in all `module.json` files (descriptions, keywords).
- [ ] Update `config/modules.php` to set `app_folder` to an empty string.
- [ ] Run `composer dump-autoload` after the above changes.

## Near-Term Delivery (Roadmap Priorities)
- [ ] **BOM Management UI**: assembly/part CRUD flows and wiring to existing project/drawing views.
- [ ] **Procurement**: PurchaseOrder controller/UI + vendor management + material receiving workflow.
- [ ] **Inventory Dashboard**: stock list, filter/sort, movement history, multi-location support.
- [ ] **Nesting Interface**: linear/plate visualisation, cut lists, remnant management.
- [ ] **Production Tracking**: barcode scanning completion, routing interface, time entry UI.
- [ ] **Shipping**: load builder UI, BOL/packing list generation, delivery confirmation.

## Backup Module (Planned)
- [ ] Create Backup module and enable it in `modules_statuses.json`.
- [ ] Add core services: BackupService, DatabaseBackupService, ExportService, RestoreService, StorageService.
- [ ] Add models: Backup, BackupSchedule, DataExport.
- [ ] Add migrations for backups, backup_schedules, data_exports.
- [ ] Implement artisan commands for backup/create/verify/cleanup.
- [ ] Build UI: backups list/detail, schedules, exports, restore flow.
- [ ] Add notifications for backup completion/failure.
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
