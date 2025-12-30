# SteelFlow MRP (FabTrol Replacement)

Modern, web-based steel fabrication management system replacing legacy FabTrol.

## Commands

### Backend (Laravel)
- **Start Env**: `docker-compose up -d`
- **Migrations**: `php artisan migrate`
- **Seed Data**: `php artisan db:seed`
- **Import Legacy**: `php artisan migrate:fabtrol all`
- **Test**: `php artisan test`
- **Generate Helpers**: `php artisan ide-helper:generate`

### Frontend (Vue.js)
- **Build**: `npm run build`
- **Dev**: `npm run dev`

## Core Concepts

### Business Rules
- **Weights**: Always store both Imperial (`lbs`) and Metric (`kg`).
- **Calculations**: Weight = Unit Weight × Length × Quantity.
- **Status Progression**: Use Events for transitions to trigger side effects/logs.
- **Performance**: Use eager loading and pagination for large datasets.

### Status Workflows
- **Stock**: `free` → `assigned` → `committed` → `used`
- **Assembly**: `not_started` → `in_progress` → `complete` → `shipped` → `delivered`
- **Nesting**: `draft` → `approved` → `verified` → `confirmed`

## Project Structure
- `app/Models/`: Projects, Phases, Lots, Assemblies, Parts, Materials, POs, StockItems, etc.
- `app/Services/`: Business logic (Import, Nesting, Production, Export, Pricing)
- `app/Http/Controllers/`: Project, BOM, Nesting, Purchasing, Inventory, Production, Shipping
- `database/migrations/`: Database schema (Phases 1-6)
- `resources/js/`: Vue 3 + Inertia components/pages

## Data Migration
Managed via `S:/Fabtrol/dev/migration_manager.py` (Extraction) and `php artisan migrate:fabtrol` (Transform & Load).
- Source: FabTrol DBF files
- Intermediate: SQLite (`fabtrol_migration.db`)
- Target: MySQL (`steelflow` database)
