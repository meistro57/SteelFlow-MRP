# Backup Module Architecture Plan

**Date:** 2026-01-09
**Status:** Architecture Complete - Ready for Implementation
**Module Type:** Laravel Module (nwidart/laravel-modules)

---

## Executive Summary

This document provides a comprehensive architectural plan for implementing a **Backup Module** in SteelFlow MRP. The module follows established patterns found in the existing Inventory and Core modules, ensuring consistency and maintainability.

### Key Features

- **Database Backups** - Full MySQL dumps with compression
- **Selective Data Exports** - Export projects, inventory, BOM data to CSV/JSON
- **Restore Functionality** - Safe restore with validation and rollback
- **Automated Scheduling** - Daily/weekly/monthly backup schedules
- **Storage Management** - Local storage + optional cloud (S3/Azure)
- **Admin-Only Access** - Secured with authentication and authorization
- **Audit Trail** - Full tracking of all backup/restore operations

---

## Module Structure

```
Modules/Backup/
├── Console/Commands/          # Artisan commands
│   ├── BackupDatabaseCommand.php
│   ├── BackupCleanupCommand.php
│   └── BackupVerifyCommand.php
├── Events/                    # Event classes
│   ├── BackupCreated.php
│   ├── BackupFailed.php
│   └── BackupRestored.php
├── Http/
│   ├── Controllers/
│   │   ├── BackupController.php
│   │   ├── ExportController.php
│   │   └── RestoreController.php
│   └── Requests/              # Form validation
│       ├── CreateBackupRequest.php
│       ├── CreateExportRequest.php
│       ├── RestoreBackupRequest.php
│       └── UpdateBackupSettingsRequest.php
├── Jobs/                      # Queue jobs
│   ├── CreateBackupJob.php
│   ├── CleanupOldBackupsJob.php
│   ├── VerifyBackupIntegrityJob.php
│   └── ExportDataJob.php
├── Models/                    # Eloquent models
│   ├── Backup.php
│   ├── BackupSchedule.php
│   └── DataExport.php
├── Notifications/             # Email notifications
│   ├── BackupSuccessNotification.php
│   └── BackupFailureNotification.php
├── Providers/                 # Service providers
│   ├── BackupServiceProvider.php
│   ├── EventServiceProvider.php
│   └── RouteServiceProvider.php
├── Services/                  # Business logic
│   ├── BackupService.php
│   ├── DatabaseBackupService.php
│   ├── ExportService.php
│   ├── RestoreService.php
│   └── StorageService.php
├── config/config.php          # Module configuration
├── database/
│   ├── factories/             # Model factories for testing
│   ├── migrations/            # Database migrations
│   │   ├── 2026_01_10_000001_create_backups_table.php
│   │   ├── 2026_01_10_000002_create_backup_schedules_table.php
│   │   └── 2026_01_10_000003_create_data_exports_table.php
│   └── seeders/               # Database seeders
├── resources/
│   └── views/emails/          # Email templates
│       ├── backup-success.blade.php
│       └── backup-failure.blade.php
├── routes/
│   ├── api.php                # API routes
│   └── web.php                # Web routes
├── tests/
│   ├── Feature/               # Feature tests
│   └── Unit/                  # Unit tests
├── composer.json              # Module dependencies
├── module.json                # Module metadata
├── package.json               # Frontend dependencies
└── vite.config.js             # Vite configuration
```

---

## Service Layer Architecture

### 1. BackupService (Main Orchestrator)

**Responsibilities:**
- Orchestrate backup creation using DatabaseBackupService
- Manage backup schedules and execution
- Apply retention policies and cleanup
- Coordinate with StorageService for file management

**Key Methods:**
```php
public function createBackup(array $options): Backup;
public function listBackups(array $filters = []): Collection;
public function deleteBackup(Backup $backup): bool;
public function downloadBackup(Backup $backup): string;
public function executeScheduledBackups(): int;
public function applyRetentionPolicy(): int;
public function verifyBackupIntegrity(Backup $backup): bool;
```

### 2. DatabaseBackupService (MySQL Operations)

**Responsibilities:**
- Execute mysqldump commands
- Compress backup files (gzip/zip)
- Calculate checksums for integrity
- Verify dump files

**Key Methods:**
```php
public function createDatabaseDump(string $outputPath): bool;
public function compressBackup(string $filePath): string;
public function calculateChecksum(string $filePath): string;
public function verifyDump(string $filePath): bool;
```

### 3. ExportService (Selective Exports)

**Responsibilities:**
- Export projects, inventory, BOM data
- Generate CSV/JSON/Excel formats
- Apply filters and transformations
- Create export manifests

**Key Methods:**
```php
public function exportProjects(array $projectIds, string $format = 'csv'): DataExport;
public function exportInventory(array $filters, string $format = 'csv'): DataExport;
public function exportBOM(int $projectId, string $format = 'json'): DataExport;
public function exportAllData(string $format = 'json'): DataExport;
```

### 4. RestoreService (Safe Restore Operations)

**Responsibilities:**
- Validate backups before restore
- Create pre-restore snapshots
- Execute restore operations
- Rollback on failure

**Key Methods:**
```php
public function restoreFromBackup(Backup $backup, array $options): bool;
public function createRestorePoint(): Backup;
public function validateBackupBeforeRestore(Backup $backup): array;
public function rollbackRestore(Backup $restorePoint): bool;
```

### 5. StorageService (File Management)

**Responsibilities:**
- Store backups locally
- Sync to cloud storage (S3/Azure)
- Manage storage metrics
- Cleanup temporary files

**Key Methods:**
```php
public function storeBackup(string $filePath, array $metadata): string;
public function retrieveBackup(string $storagePath): string;
public function syncToCloud(Backup $backup): bool;
public function getStorageUsage(): array;
```

---

## Database Schema

### backups Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| backup_id | varchar(50) | Unique identifier (e.g., BKP-20260110-143022) |
| type | enum | 'full', 'database', 'selective' |
| status | enum | 'pending', 'in_progress', 'completed', 'failed' |
| file_path | varchar(500) | Local storage path |
| cloud_path | varchar(500) | Cloud storage path (nullable) |
| file_size_bytes | bigint | Uncompressed file size |
| compressed_size_bytes | bigint | Compressed file size |
| checksum | varchar(64) | SHA256 checksum |
| database_size_bytes | bigint | Database size at backup time |
| compression_method | enum | 'gzip', 'zip', 'none' |
| retention_days | int | Retention period (nullable = forever) |
| expires_at | timestamp | Auto-deletion date |
| notes | text | User notes |
| metadata | json | Tables, record counts, etc. |
| error_message | text | Error details if failed |
| backup_duration_seconds | int | Time taken to complete |
| backup_schedule_id | bigint | FK to backup_schedules (nullable) |
| created_by | bigint | FK to users |
| updated_by | bigint | FK to users |
| verified_at | timestamp | Last integrity check |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Update timestamp |
| deleted_at | timestamp | Soft delete timestamp |

**Indexes:**
- `(status, created_at)` - For status filtering
- `expires_at` - For cleanup operations

### backup_schedules Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(100) | Schedule name |
| type | enum | 'full', 'database', 'selective' |
| frequency | enum | 'daily', 'weekly', 'monthly' |
| frequency_value | json | Day of week/month |
| time_to_run | time | Execution time (e.g., '02:00') |
| is_active | boolean | Enable/disable |
| retention_days | int | Retention period |
| compression_method | enum | 'gzip', 'zip' |
| sync_to_cloud | boolean | Auto-sync to cloud |
| notify_on_success | boolean | Send success notifications |
| notify_on_failure | boolean | Send failure notifications |
| notification_emails | json | Email addresses |
| options | json | Additional options |
| last_run_at | timestamp | Last execution |
| next_run_at | timestamp | Next scheduled execution |
| created_by | bigint | FK to users |
| updated_by | bigint | FK to users |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Update timestamp |
| deleted_at | timestamp | Soft delete timestamp |

**Indexes:**
- `is_active` - For active schedules
- `next_run_at` - For scheduler lookups

### data_exports Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| export_id | varchar(50) | Unique identifier (e.g., EXP-20260110-143022) |
| export_type | enum | 'projects', 'inventory', 'bom', 'full' |
| format | enum | 'csv', 'json', 'excel' |
| status | enum | 'pending', 'processing', 'completed', 'failed' |
| file_path | varchar(500) | Storage path |
| file_size_bytes | bigint | File size |
| record_count | int | Number of records exported |
| filters | json | Applied filters |
| metadata | json | Columns, relationships included |
| error_message | text | Error details if failed |
| expires_at | timestamp | Auto-deletion date (7 days default) |
| created_by | bigint | FK to users |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Update timestamp |
| deleted_at | timestamp | Soft delete timestamp |

**Indexes:**
- `(status, created_at)` - For status filtering
- `expires_at` - For cleanup operations

---

## Routes

All routes are protected by `auth` and `admin` middleware:

```php
Route::middleware(['auth', 'admin'])->prefix('backups')->name('backups.')->group(function () {
    // Backup CRUD
    Route::get('/', [BackupController::class, 'index'])->name('index');
    Route::get('/create', [BackupController::class, 'create'])->name('create');
    Route::post('/', [BackupController::class, 'store'])->name('store');
    Route::get('/{backup}', [BackupController::class, 'show'])->name('show');
    Route::delete('/{backup}', [BackupController::class, 'destroy'])->name('destroy');
    Route::get('/{backup}/download', [BackupController::class, 'download'])->name('download');
    Route::post('/{backup}/verify', [BackupController::class, 'verify'])->name('verify');

    // Schedules
    Route::get('/schedules', [BackupController::class, 'schedules'])->name('schedules.index');
    Route::post('/schedules', [BackupController::class, 'storeSchedule'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [BackupController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [BackupController::class, 'destroySchedule'])->name('schedules.destroy');

    // Settings
    Route::get('/settings', [BackupController::class, 'settings'])->name('settings');
    Route::put('/settings', [BackupController::class, 'updateSettings'])->name('settings.update');

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::get('/create', [ExportController::class, 'create'])->name('create');
        Route::post('/', [ExportController::class, 'store'])->name('store');
        Route::get('/{export}/download', [ExportController::class, 'download'])->name('download');
        Route::delete('/{export}', [ExportController::class, 'destroy'])->name('destroy');
    });

    // Restore
    Route::prefix('restore')->name('restore.')->group(function () {
        Route::get('/{backup}', [RestoreController::class, 'show'])->name('show');
        Route::post('/{backup}', [RestoreController::class, 'store'])->name('store');
        Route::post('/{backup}/validate', [RestoreController::class, 'validate'])->name('validate');
    });
});
```

---

## Artisan Commands

### backup:database
```bash
php artisan backup:database [--type=database] [--compress] [--notify]
```
Creates a database backup with optional compression and notifications.

### backup:cleanup
```bash
php artisan backup:cleanup [--days=30] [--dry-run]
```
Cleans up old backups based on retention policy.

### backup:verify
```bash
php artisan backup:verify [backup-id] [--all]
```
Verifies backup integrity by checking checksums and restorability.

---

## Configuration (config/config.php)

```php
return [
    'name' => 'Backup',

    'storage' => [
        'disk' => env('BACKUP_DISK', 'local'),
        'path' => env('BACKUP_PATH', 'backups'),
        'temp_path' => storage_path('app/backups/temp'),
    ],

    'retention' => [
        'default_days' => env('BACKUP_RETENTION_DAYS', 30),
        'keep_minimum' => env('BACKUP_KEEP_MINIMUM', 5),
    ],

    'compression' => [
        'enabled' => env('BACKUP_COMPRESSION', true),
        'method' => env('BACKUP_COMPRESSION_METHOD', 'gzip'),
        'level' => env('BACKUP_COMPRESSION_LEVEL', 6),
    ],

    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'mysqldump_path' => env('MYSQLDUMP_PATH', '/usr/bin/mysqldump'),
        'mysql_path' => env('MYSQL_PATH', '/usr/bin/mysql'),
        'timeout' => env('BACKUP_TIMEOUT', 3600),
    ],

    'cloud' => [
        'enabled' => env('BACKUP_CLOUD_ENABLED', false),
        'driver' => env('BACKUP_CLOUD_DRIVER', 's3'),
        'auto_sync' => env('BACKUP_CLOUD_AUTO_SYNC', false),
    ],

    'notifications' => [
        'channels' => ['mail'],
        'admin_emails' => explode(',', env('BACKUP_ADMIN_EMAILS', '')),
    ],

    'exports' => [
        'expiry_days' => env('EXPORT_EXPIRY_DAYS', 7),
        'max_records_csv' => env('EXPORT_MAX_RECORDS_CSV', 50000),
    ],
];
```

---

## Implementation Sequence

### Phase 1: Core Infrastructure (Days 1-2)
1. Create module: `php artisan module:make Backup`
2. Set up Providers (BackupServiceProvider, RouteServiceProvider, EventServiceProvider)
3. Create configuration file
4. Add filesystem disk in config/filesystems.php
5. Register module in modules_statuses.json

### Phase 2: Database Layer (Days 2-3)
1. Create migrations for all tables
2. Create Eloquent models with relationships
3. Add HasAuditFields and HasStatusPipeline traits
4. Create model factories
5. Test migrations and models

### Phase 3: Core Services (Days 3-5)
1. Implement DatabaseBackupService
2. Implement StorageService
3. Implement BackupService
4. Add UUID tracking and logging
5. Write unit tests

### Phase 4: Restore & Export Services (Days 5-6)
1. Implement RestoreService
2. Implement ExportService
3. Add validation and safety checks
4. Write unit tests

### Phase 5: Commands & Scheduling (Day 7)
1. Create Artisan commands
2. Register in BackupServiceProvider
3. Set up Laravel Scheduler
4. Test command execution

### Phase 6: Jobs & Events (Days 7-8)
1. Create Queue jobs
2. Create event classes
3. Set up event listeners
4. Test with Horizon

### Phase 7: Notifications (Day 8)
1. Create notification classes
2. Create email templates
3. Integrate in services
4. Test email delivery

### Phase 8: Controllers & Validation (Days 9-10)
1. Create all controllers
2. Create Form Request classes
3. Add middleware
4. Return Inertia responses

### Phase 9: Frontend UI (Days 11-13)
1. Create Vue pages
2. Create Vue components
3. Add navigation links
4. Implement download functionality

### Phase 10: Cloud Storage (Optional, Days 14-15)
1. Add S3/Azure configuration
2. Implement cloud sync
3. Test upload/download
4. Add metrics

### Phase 11: Testing & Documentation (Days 15-16)
1. Write feature tests
2. Write unit tests
3. End-to-end testing
4. Update documentation

---

## Security Considerations

### Authentication & Authorization
- All routes protected by `auth` and `admin` middleware
- User model must have `isAdmin()` method returning true
- Additional checks in controller methods before sensitive operations

### Audit Trail
- All models use `HasAuditFields` trait
- Automatic tracking of `created_by` and `updated_by`
- Dispatches `ModelAuditEvent` for all operations

### File Security
- Backups stored outside public directory
- Private filesystem visibility
- Download requires authentication + admin role
- File access logged

### Restore Safety
- Pre-restore validation prevents data loss
- Automatic restore point creation
- Rollback capability on failure
- Disk space checks before restore

### Rate Limiting
- Throttle backup creation: 5 requests per 60 minutes
- Prevents abuse and system overload

---

## Integration Points

### ReportingService
Add backup metrics to dashboard:
```php
'backup_status' => [
    'last_backup' => Backup::where('status', 'completed')->latest()->first(),
    'total_backups' => Backup::where('status', 'completed')->count(),
    'storage_used_gb' => round(Backup::sum('compressed_size_bytes') / 1024 / 1024 / 1024, 2),
]
```

### InventoryService
Export inventory for backups:
```php
public function exportInventoryForBackup(): array
{
    return StockItem::with(['material', 'reservedProject'])
        ->where('status', '!=', 'used')
        ->get()
        ->toArray();
}
```

---

## Testing Strategy

### Unit Tests
- BackupServiceTest
- DatabaseBackupServiceTest
- ExportServiceTest
- RestoreServiceTest
- StorageServiceTest

### Feature Tests
- BackupControllerTest
- ExportControllerTest
- RestoreControllerTest
- CommandTest

### Integration Tests
- End-to-end backup creation
- End-to-end restore with rollback
- Scheduled backup execution
- Retention policy enforcement

---

## Dependencies

### PHP Packages (composer.json)
```json
{
    "require": {
        "symfony/process": "^6.0",
        "league/flysystem-aws-s3-v3": "^3.0",
        "league/flysystem-azure-blob-storage": "^3.0"
    }
}
```

### Frontend Packages (package.json)
```json
{
    "dependencies": {
        "chart.js": "^4.0.0",
        "vue-chartjs": "^5.0.0"
    }
}
```

---

## Environment Variables

Add to `.env`:
```bash
# Backup Module Configuration
BACKUP_DISK=local
BACKUP_PATH=backups
BACKUP_RETENTION_DAYS=30
BACKUP_KEEP_MINIMUM=5
BACKUP_COMPRESSION=true
BACKUP_COMPRESSION_METHOD=gzip
BACKUP_COMPRESSION_LEVEL=6
BACKUP_TIMEOUT=3600

# MySQL Paths
MYSQLDUMP_PATH=/usr/bin/mysqldump
MYSQL_PATH=/usr/bin/mysql

# Cloud Storage (Optional)
BACKUP_CLOUD_ENABLED=false
BACKUP_CLOUD_DRIVER=s3
BACKUP_CLOUD_AUTO_SYNC=false

# Notifications
BACKUP_ADMIN_EMAILS=admin@example.com,ops@example.com

# Exports
EXPORT_EXPIRY_DAYS=7
EXPORT_MAX_RECORDS_CSV=50000
```

---

## Success Metrics

- ✅ All backups complete in < 5 minutes
- ✅ Backup integrity 100% verified
- ✅ Restore operations complete in < 10 minutes
- ✅ Zero data loss on restore
- ✅ Automated daily backups with 30-day retention
- ✅ Admin notifications on backup failure
- ✅ Storage usage < 10GB for 30 days of backups

---

## References

- **Inventory Module:** `/home/user/SteelFlow-MRP/Modules/Inventory/`
- **Core Module:** `/home/user/SteelFlow-MRP/Modules/Core/`
- **Module Configuration:** `/home/user/SteelFlow-MRP/config/modules.php`
- **Module Status:** `/home/user/SteelFlow-MRP/modules_statuses.json`

---

## Approval Checklist

- [ ] Architecture reviewed
- [ ] Database schema approved
- [ ] Security considerations validated
- [ ] Resource requirements confirmed
- [ ] Timeline approved
- [ ] Ready for implementation

---

**Document Version:** 1.0
**Last Updated:** 2026-01-09
**Next Review:** Upon implementation completion
