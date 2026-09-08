# SteelFlow-MRP Upgrade Plan

Author: Kepler · 2026-09-08 · Status: baseline green (149 tests passing)

## Current baseline

- PHP 8.4.24 (current, keep)
- Laravel framework 11.47.0
- Filament 4.5.3 · Inertia 1.3.4 · nwidart/laravel-modules 12.0.4
- Guzzle 7 · Scout 10 · Predis 2 · lavacharts 1.0.0 (abandoned)

## Key constraint

DrawingFlow (to be folded in as a module) is on **Laravel 12**, while SteelFlow
is on **Laravel 11**. Folding 12-code into an 11-app is a backward port, so the
11→12 bump must come **before** the DrawingFlow fold-in.

## Ordered plan

### Wave 0 — hygiene (done)
- Reset `steelflow_test` DB (fixed the "table already exists" cascade).
- Guarded the TestCase seeder with `Schema::hasTable` (unit tests no longer
  require migrations). Committed.

### Wave 1 — Laravel 11→12 (gentle, required)
- `composer require laravel/framework:^12.0 --with-all-dependencies`
- Bump the dev test stack as needed (Pest/PHPUnit/Larastan compatibility).
- Gate: full suite green (149 tests) + manual dashboard smoke test.

### Wave 2 — fold DrawingFlow → `Modules/DrawingFlow`
- Port models/controllers/migrations; drop its duplicate Customers/Users in
  favor of SteelFlow Core. One auth, one customers table.

### Wave 3 — `Modules/Estimating` (greenfield)
- Takeoff engine (BOM × material price + labor × rate × hours + markup).
- Quote model + PDF + convert-to-project. Reuses Materials, LaborRates,
  Parts/Assemblies, Customers, Projects, WeightCalculator.

### Wave 4 — the heavy majors
- Laravel 12→13
- Filament 4→5
- Inertia 1→3
- nwidart/laravel-modules 12→13
- Drop `khill/lavacharts` (abandoned) — migrate charts to a maintained lib

### Wave 5 — low-risk patch/minor batch (can be done anytime)
- horizon 5.43→5.48, sanctum 4.2→4.3, socialite 5.24→5.31, telescope 5.16→5.23,
  tinker, pint, sail, log-viewer, collision, model-states, ziggy,
  meilisearch-php, azure provider, ignition.

## Verification discipline

After every wave: `docker compose exec app php artisan test` (expect 149 green)
plus a manual smoke of the Filament dashboard + one happy-path flow. No wave
advances on red.
