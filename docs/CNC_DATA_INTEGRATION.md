# CNC Data Integration (Modules/CncData)

This module reads plasma CNC programs into production data that estimating, scheduling and costing can use. It handles Hypertherm Phoenix EIA-274 programs as ProNest posts them for the XPR300. It is the server-side half of **CNC Bench**, the browser editor and previewer. Both run the same engine and produce the same JSON.

## The loop this is building toward

```
 detailing ──► nesting (ProNest) ──► CNC program ──► CncData analysis ──► schedule / cost
     ▲                                                     │
     │                                               actual runs
     │                                                     ▼
     └──────── estimating ◄── rate book ◄── calibration (actual ÷ estimate)
```

The rule is that no time or cost figure counts as validated until actual runs on the table back it up. Until then, every figure is labelled provisional.

## Phase 1: what's in this module

| Piece | Where | What it does |
|---|---|---|
| Engine | `Engine/` | Pure PHP with no Laravel. It parses the program and returns `steelflow.cnc-analysis/1`. It is a port of CNC Bench's parser. |
| Cut chart | `Engine/XprCutChart.php` | XPR mild steel O2 at 130, 170 and 300 A, in inches, from IM 809830 R1. It compares the program's feed and kerf with the chart, and gives the pierce delay and consumable part numbers. |
| Storage | `cnc_programs` | The file (stored once per SHA-256) plus material, process, lengths, pierces, estimate, check counts and the full analysis JSON. |
| Piece marks | `cnc_program_parts` | Links a program to piece marks. Each mark is matched to a `Part` (by `part_mark`) or an `Assembly` (by `mark`) in the program's project. |
| Actual runs | `cnc_runs` | Actual minutes, with the estimate frozen at the moment the run was logged, plus the variance. |
| Calibration | `CalibrationService` | Median of actual ÷ estimate for each process group (machine + amps + thickness). |
| Cost | `CncCostService`, `cnc_process_rates` | Pierces × $/pierce + arc minutes × $/arc-minute + machine hours × rate. The rates are the shop's own numbers. |
| Rate book | `ProductionRateService` | Minutes per foot of cut and seconds per pierce, measured from runs. This is what Estimating should read. |
| API | `routes/api.php` | `/api/cnc/...`, see below. |
| CLI | `cnc:analyze`, `cnc:import` | Analyse files or folders, or bulk-load the ProNest output folder. |

### Status flow

`pending → analyzed | flagged → approved → cut → archived`

- A program goes to **flagged** when the analysis has any `error` check, such as the torch on during a rapid, unpaired M07/M08, a cut off the plate, or a feed move with no F.
- Approving a flagged program needs `force`.
- Logging a completed run moves it to **cut**.

### Validation gate

A process group's estimate is **validated** when it has at least `min_runs` completed runs (default 5) within the last `window` runs (default 10). The spread of actual÷estimate, measured as MAD ÷ median, must also be at most `max_spread` (default 0.15).

Until then, `plannedMinutes()` returns the raw engine estimate, and cost results say `"validated": false`. All three settings are in `config.php`.

A run more than 25% off its estimate is returned with `variance_alert: true`.

## API

All routes are under `/api/cnc` and use the middleware in `config('cncdata.api_middleware')`, which defaults to the logged-in web session.

| Method | Path | Purpose |
|---|---|---|
| POST | `/analyze` | Stateless. Send `file` or `text` and get the analysis JSON back. Nothing is stored. |
| GET | `/programs` | List programs. Filters: `project_id`, `status`, `flagged`, `per_page`. |
| POST | `/programs` | Store and analyse. Send `file` or `text` + `program_name`, plus optional `project_id`, `machine_id`, `rapid_rate`. Returns 201 for a new program, or 200 if the same file is already stored for that project. |
| GET | `/programs/{id}` | Program with parts and runs. Add `?full=1` for the whole analysis. |
| POST | `/programs/{id}/reanalyze` | Re-read the stored file, for example after an engine or chart update. |
| POST | `/programs/{id}/approve` | Release to the table. Add `force=1` to approve a flagged program. |
| PUT | `/programs/{id}/parts` | Replace links from one source: `{source, parts: [{piece_mark, quantity, part_id?, assembly_id?}]}`. |
| POST | `/programs/{id}/runs` | Log an actual run: `actual_minutes`, plus optional `machine_id`, `started_at`, `finished_at`, `arc_on_minutes`, `pierce_count`, `result`, `source`. |
| GET | `/programs/{id}/cost` | Consumable and machine cost, marked provisional or validated. |
| GET | `/rates` | Rate book. Filters: `machine_id`, `amps`, `material`, `thickness`. |

**Token auth for a shop-floor agent.** A machine posting files from the plasma PC needs a token, not a session. The app has no token auth wired up yet. To add it: run `php artisan install:api`, add `HasApiTokens` to `User`, and set `api_middleware` to `['api', 'auth:sanctum']`.

## The analysis contract: `steelflow.cnc-analysis/1`

```jsonc
{
  "schema": "steelflow.cnc-analysis/1",
  "units": "in",                      // lengths are in program units; times are in minutes
  "material": {"name": "Mild Steel", "thickness": 0.75, "sheet": {"x": 60, "y": 120}},
  "process":  {"label": "170Amp O2/Air [True Bevel] _True Hole_", "amps": 170, "plasma_gas": "O2", "shield_gas": "Air"},
  "lengths":  {"cut": 438.519, "mark": 179.9991, "travel": 0, "rapid": 450.9529},
  "pierces":  {"cut": 19, "true_hole": 8, "mark_starts": 70},
  "times_min":{"cut": 7.6343, "mark": 1.8, "travel": 0, "rapid": 0.7516, "dwell": 0.0083, "pierce_delay": 0.2533, "total": 10.4476},
  "cut_feeds": [{"feed": 21, "length": 25.7539}, {"feed": 36.3, "length": 4.7444}, {"feed": 65, "length": 408.0207}],
  "kerf_values": [0.127, 0.172],
  "extents": {"path": {...}, "cut": {...}},
  "chart": {"amps": 170, "speed": 65, "arc_volts": 137, "pierce_delay_s": 0.8, "kerf": 0.13, "feed_ok": true, "kerf_ok": true, "...": "..."},
  "warnings": [{"line": 12, "code": "RAPID_TORCH_ON", "severity": "error", "message": "..."}],
  "options": {"rapid_rate": 600}
}
```

Warning codes:

- **Errors:** `TORCH_ALREADY_ON`, `END_TORCH_ON`, `NO_MOTION_MODE`, `RAPID_TORCH_ON`, `NO_FEED`, `OFF_PLATE`
- **Warnings:** `TORCH_ALREADY_OFF`, `END_KERF_ON`, `ARC_RADIUS`, `NO_END`, `CHART_FEED`, `CHART_KERF`
- **Info:** `NO_UNITS`

### Keeping the two engines identical

- The fixtures in `tests/Fixtures/cnc/*.expected.json` were generated by the CNC Bench JS engine.
- `tests/Unit/CncData/CncEngineParityTest.php` fails if the PHP port drifts from them.
- When the contract changes, change both engines and regenerate the fixtures from the JS side (CNC Bench's `analyze()`). Bump the schema version if a field changes meaning.

## What a CNC file can't tell you (open items)

1. **Piece marks aren't in the program as text.** ProNest marks part numbers onto the plate as plasma strokes (for example `p1021`), which are geometry, not readable labels.
   - Today, links come from manual entry or the API.
   - The project is guessed when its job number appears in the file name.
   - Reliable linking needs ProNest's own output: a nest or part-list report exported next to each CNC file. **Needed: a sample of that report from Mark's ProNest 2023 setup.** It becomes a `nest_report` importer feeding `syncParts(..., 'nest_report')`.

2. **Actual machine time has no automatic source yet.** The `source` values on `cnc_runs` are ready: `manual | phoenix_log | xpr | time_entry`. What Phoenix 2+ 1.207 and the XPR web interface actually record (job start/stop, arc-on time, pierce counts) needs checking on the machine. **Needed: one exported Phoenix log or XPR report.** Until then, operators log runs manually.

3. **Consumable cost rates are the shop's to set.** The XPR manuals give no fixed electrode or nozzle life, so nothing is defaulted. The best source is the existing maintenance data: consumable changes in `MaintenancePartUsage` divided by arc minutes and pierces since the last change gives $/pierce and $/arc-minute per process. That's a phase 3 job once runs are being logged.

4. **The engine models feeds and rapids, not the machine's dynamics.**
   - It doesn't know about acceleration, IHS cycles, retract heights or corner slowdown. Calibration absorbs these per process group, which is why the gate exists.
   - The rapid rate is one global setting (600 in/min, from the ProNest machine config). A per-machine value would belong on `machines`.

5. **The cut charts are hand-typed.** They cover mild steel O2 at 130, 170 and 300 A, in inches. Some PDF columns span several rows, so confirm a value against the chart before acting on it.
   - Stainless and aluminum aren't covered yet; the CFG uses 40/80/130 A for aluminum.
   - True Hole and True Bevel have their own charts and aren't compared.

## Phases

| Phase | Scope | Depends on |
|---|---|---|
| 1 (this) | Engine, storage, checks, actual-vs-estimate gate, cost from rates, rate book, API, CLI | none |
| 2 | Nest-report import → automatic piece-mark links; Filament/Inertia screens (program list, checks, runs) | ProNest report sample |
| 3 | Automatic actual time (Phoenix/XPR logs); consumable rates derived from maintenance part usage | Log/report sample |
| 4 | Estimating reads `/api/cnc/rates` for plate work (min/ft cut, s/pierce by process); Scheduling books `plannedMinutes()` into `MachineSchedule` | Modules/Estimating |
| 5 | Detailing feedback: compare estimated vs actual by piece mark and assembly; surface which details cut slow (hole-heavy plates, small features) | Phases 2–4 |

## Running it

```bash
php artisan migrate                                   # creates the four cnc_* tables
php artisan cnc:analyze storage/app/samples/*.CNC     # table report; exit code 1 if any errors
php artisan cnc:analyze rip-01.CNC --json             # contract JSON
php artisan cnc:import "X:\\" --machine=1             # bulk load; skips files already stored
php artisan test --filter=CncData
```
