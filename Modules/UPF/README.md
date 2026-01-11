# UPF (Universal Product File) Module

Replicates the FabTrol UPF system in Laravel to provide full backward compatibility and a robust material catalog foundation.

## Features
- **Legacy Compatibility**: Full support for PKEY, FILEKEY, and ORDERKEY sequencing.
- **Master Catalog**: Manage Material Types, Grades, and Master Prices.
- **Labor & Handling**: Detailed labor rates and auto-handling cost definitions per material type.
- **Integration**: Linked to core `parts`, `stock_items`, and `purchase_orders` via `upf_price_id`.
- **Importer**: High-speed CSV importer for legacy data.

## Service Layer
- `KeyGenerationService`: Manages unique legacy keys with database-level locking.
- `UpfService`: Handles bulk operations like **Rename Type** (cascades through 11 tables) and **Copy Type**.
- `PricingService`: Cost calculation engine based on UPF master rates.
- `StockService`: Specialized stock adjustments for UPF catalog items.
- `FabTrolImporter`: ETL logic for FabTrol data migrations.

## Administrative UI
Available in the Filament Admin Panel under the **"Material Setup"** group:
- **Material Types**: Sections like PLATE, BEAM, etc.
- **Material Grades**: Grade definitions (A36, A992).
- **UPF Price Master**: The master table for dimensional data and unit pricing.
- **Labor Rates**: Production operation costs.
- **Auto Handling**: Automatic cost application rules.

## CLI Commands
```bash
# Import legacy data
php artisan upf:import path/to/file.csv
```

## Testing
Module tests are located in `Modules/UPF/Tests`:
```bash
php artisan test Modules/UPF/Tests
```
