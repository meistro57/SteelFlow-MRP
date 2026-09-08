# SteelFlow Plan — unified

Authors: Kepler + Lewis · 2026-09-08 · supersedes UPGRADE_PLAN.md and MODULARIZATION_AND_ESTIMATING_PLAN.md

## Resolved sequencing (confirmed by Meistro)

**Upgrade to Laravel 12 → fold in DrawingFlow → continue the upgrade.**

Deciding fact: DrawingFlow is Laravel 12, SteelFlow is Laravel 11. Folding
12-code into an 11-app is a backward port that the later upgrade would then
re-touch. So: gentle 11→12 first, fold DrawingFlow at native 12, then the heavy
12→13 + Filament + inertia + nwidart majors.

### Final order

1. **Wave 1 — Laravel 11→12** (gentle; required by the DrawingFlow version mismatch)
2. **Wave 2 — fold DrawingFlow → `Modules/DrawingFlow`** (native 12, no down-port)
3. **Wave 3 — `Modules/Estimating`** (greenfield)
4. **Wave 4 — heavy majors**: Laravel 12→13, Filament 4→5, Inertia 1→3, nwidart 12→13, drop abandoned `khill/lavacharts`
5. **Wave 5 — patch/minor batch** (horizon, sanctum, socialite, telescope, tinker, pint, sail, log-viewer, collision, model-states, ziggy, meilisearch-php, azure provider, ignition)

### Version facts (checked 2026-09-08)

- PHP 8.4.24 in container (current, keep).
- laravel/framework: installed 11.47; latest 12 = v12.69.2, latest 13 = v13.31.0.
- nwidart/laravel-modules: installed 12.0.4; v13.0.0 available.
- khill/lavacharts is abandoned upstream — migrate away, don't upgrade.

## Wave 2 — fold DrawingFlow

DrawingFlow is a standalone Laravel-12 app (own artisan, own Customers/Projects/Users).
Its workflow models are the value:

- `DrawingRequest → DrawingSubmittal → SubmittalFile → SubmittalApproval → FabQueue`
- `PdfMarkup`, `PdfPageScale` — PDF markup/scale
- `CustomerWorkflow`, `ProjectAttachment`

Fold: scaffold `Modules/DrawingFlow`; port the 8 workflow models as module models;
drop DrawingFlow's own Customer/Project/User and re-point to SteelFlow Core (one
auth, one customers table); port the 6 services + controllers/routes/tests;
renumber migrations preserving timestamps.

**Workflow relationship (confirmed by Meistro):** DrawingFlow is the entire
shop-drawing **submittal → approval** stage. It hands its info to Production via
a **fab release** (its `FabQueue`), and that release **includes plate nests and
CNC data** — the full fabrication package, not just approved drawings. So the
fold must model that handoff: DrawingFlow emits a fab release (drawings + nests +
CNC) → Production consumes it as work orders/batches. `Drawing` stays the data
entity; DrawingFlow is the approval+nesting+CNC stage *upstream* of production;
the fab-release event is the seam between them.

## Wave 3 — Estimating (greenfield)

Front door: bid → takeoff → price → quote → convert to project.

Schema (first cut):
- `estimates` — id, project_id?, customer_id, status, revision, markup_pct, tax, valid_until, notes
- `estimate_lines` — estimate_id, seq, description, material, qty, unit, unit_material_cost, unit_labor_cost, unit_total, source
- `takeoffs` — estimate_id, source_type, source_id, generated_at (regenerable derived set)
- `labor_standards` — work_area_id, operation, rate, unit
- `quotes` — estimate_id, number, sent_at, accepted_at, pdf path
- `bid_to_project` — won estimate → new Project (copy lines via BOMExtensionService)

Reuses: Materials (price/lb), LaborRates, Parts/Assemblies, Customers, Projects, WeightCalculator.

## Not yet covered (flagged)

- Counter sales / POS
- Contract jobs (project-type flag + wire Progress Billing/retainage)
- Plate nesting (linear done, plate pending)

## Verification discipline

After every wave: `docker compose exec app php artisan test` — expect green
(baseline 149 passed). No wave advances on red.
