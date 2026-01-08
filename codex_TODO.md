# codex_TODO.md
# SteelFlow MRP TODO (Laravel-first, no nonsense)

## Purpose
This file tracks the actionable TODOs required to deliver the SteelFlow MRP Feature Bundle. The list below is organised by build order and includes clear, Laravel-native tasks.

## TODO (Build Order)

### 1) Core Foundation (Modules + Core/Authz/AuditLog)
- [x] Scaffold modular architecture (domain modules) and a shared `Core` module for traits/enums/helpers/UI components.
- [x] Define status pipeline conventions and reusable status helpers in `Core`.
- [ ] Establish audit-first data conventions (created_by/updated_by + immutable history hooks).
- [ ] Build `Authz` module: roles, permissions, policy conventions, Filament integration.
- [ ] Build `AuditLog` module: record timeline, immutable history, per-record audit views.

### 2) Filament Shell + Permissions
- [ ] Stand up Filament panel with role-based access control and default dashboard.
- [ ] Implement UI conventions: dense tables, saved filters, keyboard-friendly actions, dark-mode readiness.

### 3) Documents Module
- [ ] Create Documents module with tagging, versioning, metadata, and record attachment support.
- [ ] Add “document control” view for centralised access and permissions.

### 4) PdfCenter (One PDF end-to-end)
- [ ] Implement Blade-based PDF template pipeline.
- [ ] Generate first PDF (e.g., shop ticket) and auto-attach to Documents.

### 5) ModelViewer (GLB demo)
- [ ] Build in-app GLB/GLTF viewer with permission gating and embedded viewer UI.

### 6) Dashboards + Kanban (WorkBoards)
- [ ] Create KPI widgets and per-role dashboards (WIP, late jobs, inventory alerts).
- [ ] Implement Kanban workflow board (detailing → QA → released) with status tracking.

### 7) Scheduling
- [ ] Add calendar views for deliveries, inspections, pour dates, and crane days.

### 8) Business Modules (Phase 2+)
- [ ] Implement Jobs module (backed by Documents attachments).
- [ ] Add Inventory, Purchasing, Production, Shipping, Reporting, Integrations (Fabtrol/CNC/import/export).

## MVP Done Checklist
- [ ] Modules system running
- [ ] Filament panel live
- [ ] Documents attach to a Job
- [ ] One PDF generated and stored
- [ ] One 3D model viewable in-app
- [ ] Audit trail visible
- [ ] Roles & permissions enforced
- [ ] One dashboard with real KPIs
- [ ] One Kanban workflow

## Notes
- Laravel 10/11, PHP 8.2+.
- Follow Laravel conventions; avoid reinventing solved problems.
- Permissions and audit are first-class across modules.
