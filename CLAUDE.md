# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SteelFlow MRP is a **Manufacturing Resource Planning system** for steel fabrication, built as a modern replacement for legacy FabTrol systems. It combines Laravel 11 backend with Vue.js 3 + Inertia.js frontend for a web-native, high-performance platform.

**Tech Stack:** Laravel 11 (PHP 8.4+), Vue.js 3, Inertia.js, Pinia, Tailwind CSS, MySQL 8.0, Redis, Meilisearch, Docker

**Current Stage:** Foundation complete with full backend services. Active development on controllers and UI to connect backend logic to frontend.

## Development Commands

### Docker Environment
```bash
docker compose up -d                    # Start all containers
docker compose down                     # Stop containers
docker compose logs -f                  # View logs
docker compose exec app bash            # Access app shell
docker compose exec app php artisan ... # Run Artisan commands
```

### Backend (Laravel)
```bash
# Inside container (docker compose exec app bash)
php artisan migrate                     # Run migrations
php artisan migrate:fresh --seed        # Reset and seed database
php artisan db:seed                     # Seed database only
php artisan test                        # Run PHPUnit tests
php artisan tinker                      # Interactive REPL
php artisan route:list                  # List all routes
php artisan ide-helper:generate         # Generate IDE helpers

# Code quality
composer lint                           # Run Laravel Pint
composer lint:check                     # Check without fixing
composer analyse                        # Run PHPStan analysis
composer test                           # Run tests
composer quality                        # Run all quality checks
```

### Frontend (Vue.js)
```bash
npm run dev                             # Start Vite dev server with HMR
npm run build                           # Production build
npm run lint                            # ESLint check
npm run lint:fix                        # ESLint fix
```

### Running Tests
```bash
# Run full test suite
docker compose exec app php artisan test

# Run specific test
docker compose exec app php artisan test --filter=ProjectTest

# Run tests with coverage
docker compose exec app php artisan test --coverage
```

### Filament Admin Panel
```bash
# Access the admin panel
# URL: http://localhost/admin (or your configured domain)

# Default admin credentials (from seeder)
# Email: admin@steelflow.local
# Password: password

# Create new Filament resources
php artisan make:filament-resource ModelName

# Create new Filament pages
php artisan make:filament-page PageName

# Create new Filament widgets
php artisan make:filament-widget WidgetName

# Build Filament assets
npm run build
php artisan filament:assets
```

**Filament Configuration:**
- Panel Provider: `app/Providers/Filament/AdminPanelProvider.php`
- Resources: `app/Filament/Resources/`
- Pages: `app/Filament/Pages/`
- Widgets: `app/Filament/Widgets/`
- Custom Theme: `resources/css/filament/admin/theme.css`

**Access Control:**
- Users with `role = 'admin'`, `'manager'`, or `'supervisor'` can access the admin panel
- Implement in User model via `canAccessPanel()` method
- Uses FilamentUser interface

## Architecture Overview

### Inertia.js Data Flow
This project uses **Inertia.js**, which provides SPA-like navigation without building a separate API:

```
Request → Laravel Route → Controller → Service Layer → Eloquent Models
                              ↓
                    inertia('PageName', ['props' => $data])
                              ↓
                    Vue Page Component (receives props)
                              ↓
                    User Interaction (form submit/navigation)
                              ↓
                    Inertia POST/PUT/DELETE → Back to Laravel
```

**Key Points:**
- Controllers return `inertia('PageName', $props)` instead of JSON responses
- Vue components receive data as props from the server
- Use `<Link>` component for navigation (no page reload)
- Forms submit back to Laravel routes using Inertia's form helper
- No separate API endpoints needed

### Service Layer Pattern
Business logic lives in `app/Services/`, NOT in controllers. Controllers are thin HTTP handlers.

**Available Services:**
- `BOMExtensionService`: Weight calculations, assembly explosion, instance generation
- `InventoryService`: Stock movements (receive, assign, commit, use, return, adjust)
- `NestingService`: Nesting workflows and stock allocation
- `ProductionService`: Shop floor execution and status tracking
- `ShippingService`: Load building and weight rollups
- `ReportingService`: Dashboard metrics and reports
- `ReferenceDataService`: Material catalog lookups
- `LabelService`: ZPL code generation for Zebra printers
- `WeightCalculator`: Dual-unit weight calculations
- `KissImporter` / `XsrImporter`: CAD file parsers

### Model Relationships
29 Eloquent models with rich relationships. Always use eager loading to prevent N+1 queries:

```php
// Good - eager load relationships
$projects = Project::with(['phases.lots', 'customer'])->get();

// Bad - causes N+1 queries
$projects = Project::all();
foreach ($projects as $project) {
    $project->customer->name; // Separate query for each project!
}
```

### Database Transactions
Wrap multi-step operations in transactions for data integrity:

```php
DB::transaction(function () {
    // Create records
    // Update related data
    // Perform calculations
});
```

Critical for: BOM operations, inventory movements, nesting, production workflows.

## Key Business Rules

### Weight Calculations
- **Always maintain dual units**: Store both `weight_lbs` and `weight_kg`
- Use `WeightCalculator` service for consistency
- Formula: `Weight = Unit Weight × Length × Quantity`
- Conversions: 1 lb = 0.453592 kg, 1 ft = 0.3048 m

### Status Workflows
Understanding status flows is critical for implementing features:

**Stock Item:** `free → assigned → committed → used` (with return path)
**Assembly:** `not_started → in_progress → complete → shipped → delivered`
**Nesting:** `draft → approved → verified → confirmed`
**Purchase Order:** `draft → sent → partial → received → closed`
**Production Batch:** `created → released → in_progress → complete`
**Project:** `bidding → awarded → active → on_hold → complete → archived`

### Data Integrity
- **Soft Deletes**: Enable `SoftDeletes` trait on all models for audit trail
- **Audit Trail**: `StockMovement` model tracks all inventory changes with timestamps and user
- **Transactions**: All critical operations use `DB::transaction()`

## Code Style & Standards

### Laravel (Backend)
- Follow Laravel conventions (PSR-12 via Laravel Pint)
- Controllers are thin - business logic in Services
- Use Form Request classes for validation (`app/Http/Requests/`)
- Always eager load relationships with `->with()`
- Enable soft deletes on new models
- Use database transactions for multi-step operations

### Vue.js (Frontend)
- Vue 3 Composition API preferred
- Single quotes required (ESLint enforced)
- Semicolons required (ESLint enforced)
- File names match directory structure (`Pages/Projects/Index.vue` → `/projects`)
- Use Tailwind utility classes (don't write custom CSS unless necessary)
- Props flow from Laravel via Inertia - no separate API calls needed

### ESLint Rules
- Single quotes: `'hello'` not `"hello"`
- Semicolons required at end of statements
- No console.log in production (warn level)
- Unused variables with `_` prefix are ignored
- Vue 3 recommended rules enforced
- Globals: `route()` and `axios` available everywhere

## Important Files & Locations

### Backend Entry Points
- `routes/web.php` - All application routes (48 routes)
- `app/Providers/AppServiceProvider.php` - Service registration
- `config/app.php` - Core configuration

### Frontend Entry Points
- `resources/js/app.js` - Inertia.js + Pinia initialization
- `resources/views/app.blade.php` - Root Inertia view
- `resources/js/Pages/` - Full-page Vue components
- `resources/js/Components/` - Reusable Vue components

### Configuration
- `.env` - Environment variables (copy from `.env.example`)
- `config/database.php` - Database connections
- `config/scout.php` - Meilisearch configuration
- `config/services.php` - OAuth providers (Azure)
- `docker-compose.yml` - Container orchestration

### Documentation
- `README.md` - Project overview and setup
- `CRUSH.md` - Core business rules and model reference
- `ROADMAP.md` - Development status and priorities
- `docs/INSTALLATION.md` - Detailed setup guide

## Common Development Tasks

### Adding a New Feature with UI
1. **Create/update the Service** in `app/Services/` with business logic
2. **Create a Controller** in `app/Http/Controllers/` as thin HTTP handler
3. **Add routes** in `routes/web.php`
4. **Create Vue page** in `resources/js/Pages/`
5. **Return Inertia response** from controller: `inertia('PageName', ['data' => $data])`
6. **Use Inertia Link** in Vue for navigation: `<Link href="/path">`

### Adding a New Model
1. Create migration: `php artisan make:migration create_table_name`
2. Create model: `php artisan make:model ModelName`
3. Add `SoftDeletes` trait for audit trail
4. Define relationships in model
5. Add to Scout for search if needed: `use Searchable;`
6. Consider creating a factory and seeder for testing

### Modifying the Database
```bash
# Create a new migration
php artisan make:migration add_column_to_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset and reseed
php artisan migrate:fresh --seed
```

### Working with Vue Components
- Receive data via props from Laravel (via Inertia)
- Use `route()` helper for Laravel routes (provided by Ziggy)
- Submit forms back to Laravel using Inertia's form helper
- Use `<Link>` for navigation instead of `<a>` tags
- Access global state via Pinia stores if needed

## Development Notes

### Current Implementation Status
**Complete:** Database schema (31 migrations), 52 models, 16+ services, authentication, BOM Management (CRUD), Inventory Module, Procurement & Receiving, Linear Nesting visualization, Docker environment.

**In Progress:** Plate Nesting visualization, Receiving validation, PDF Labels, Shipping UI, Production routing details.

### CAD & Data Integration
Three importers parse fabrication files:
- `KissImporter`: Parses KISS format → BOM structure
- `XsrImporter`: Parses XSR format → BOM structure
- `FabTrolImporter`: Parses UPF CSV → Legacy Material Catalog

Both extract: assembly marks, part marks, materials, weights, quantities

### Authentication
- **Primary:** Microsoft 365 OAuth via Laravel Socialite
- **Secondary:** Laravel Sanctum for API tokens
- User model has `azure_id` field and JSON `settings` column

### Performance Considerations
- Use eager loading (`with()`) to prevent N+1 queries
- Paginate large datasets (projects, inventory, etc.)
- Cache reference data (materials, grades) via Redis
- Meilisearch handles fast search - use Scout's `search()` method

### Testing Strategy
- PHPUnit for backend tests (`tests/Feature/`, `tests/Unit/`)
- Test services independently from controllers
- Use factories for test data generation
- Test critical workflows with database transactions

## Tips for Success

1. **Read before modifying** - Never propose changes to code you haven't read
2. **Use services** - Business logic goes in `app/Services/`, not controllers
3. **Eager load relationships** - Always use `->with()` for related models
4. **Wrap in transactions** - Multi-step operations need `DB::transaction()`
5. **Dual units** - Always maintain both Imperial and Metric weights
6. **Inertia responses** - Return `inertia('Page', $props)`, not JSON
7. **Soft deletes** - Don't hard delete - mark as deleted for audit trail
8. **Docker workflow** - Run Artisan commands via `docker compose exec app`
9. **ESLint compliance** - Frontend code must pass `npm run lint`
10. **Test before commit** - Run `php artisan test` before committing
