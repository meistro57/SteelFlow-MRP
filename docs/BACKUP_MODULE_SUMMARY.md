# Backup Module - Quick Reference

**Status:** Architecture Complete ✅
**Created:** 2026-01-09
**Ready for Implementation**

---

## Quick Start

### 1. Create Module
```bash
docker compose exec app php artisan module:make Backup
```

### 2. Enable Module
Add to `modules_statuses.json`:
```json
{
    "Core": true,
    "Inventory": true,
    "Backup": true
}
```

### 3. Create Key Services
```bash
# Create services in Modules/Backup/Services/
- BackupService.php (main orchestrator)
- DatabaseBackupService.php (mysqldump operations)
- ExportService.php (CSV/JSON exports)
- RestoreService.php (restore operations)
- StorageService.php (file management)
```

### 4. Create Models
```bash
# Create models in Modules/Backup/Models/
- Backup.php (main backup records)
- BackupSchedule.php (scheduled backups)
- DataExport.php (export tracking)
```

### 5. Run Migrations
```bash
docker compose exec app php artisan migrate
```

---

## Key Features

✅ **Database Backups** - Full MySQL dumps with gzip compression
✅ **Selective Exports** - Export projects/inventory/BOM to CSV/JSON
✅ **Restore System** - Safe restore with validation and rollback
✅ **Automated Scheduling** - Daily/weekly/monthly backups
✅ **Cloud Storage** - Optional S3/Azure sync
✅ **Admin Security** - Auth + admin middleware on all routes
✅ **Audit Trail** - Full tracking with HasAuditFields trait

---

## Implementation Timeline

| Phase | Duration | Description |
|-------|----------|-------------|
| 1. Core Infrastructure | 2 days | Module setup, providers, config |
| 2. Database Layer | 1 day | Migrations, models, relationships |
| 3. Core Services | 2 days | Backup, database, storage services |
| 4. Restore & Export | 1 day | Restore validation, export formats |
| 5. Commands & Scheduling | 1 day | Artisan commands, scheduler |
| 6. Jobs & Events | 1 day | Queue jobs, event listeners |
| 7. Notifications | 1 day | Email templates, notifications |
| 8. Controllers & Validation | 2 days | CRUD controllers, form requests |
| 9. Frontend UI | 3 days | Vue pages and components |
| 10. Cloud Storage (optional) | 2 days | S3/Azure integration |
| 11. Testing & Docs | 1 day | Unit/feature tests, documentation |

**Total: ~16 days** (11 days without cloud storage)

---

## Module Structure Audit Results

### ✅ Good Patterns Found
- Service layer architecture is consistent
- Dependency injection properly implemented
- Database transactions used correctly
- Audit trails working well
- Namespace conventions consistent

### ⚠️ Issues Found & Recommendations

#### Issue 1: Composer.json Autoload Mismatch
**Fix:** Update `/Modules/Inventory/composer.json` line 20:
```json
"Modules\\Inventory\\": ""  // Change from "app/"
```

#### Issue 2: Missing composer.json in Core
**Fix:** Create `/Modules/Core/composer.json` with proper PSR-4 autoload

#### Issue 3: Incomplete module.json Metadata
**Fix:** Add descriptions and keywords to all module.json files

#### Issue 4: Config Suggests app/ Folder
**Fix:** Update `/config/modules.php` line 119:
```php
'app_folder' => '',  // Change from 'app/'
```

### Run After Fixes
```bash
docker compose exec app composer dump-autoload
```

---

## Artisan Commands

```bash
# Create backup
php artisan backup:database [--type=database] [--compress] [--notify]

# Clean old backups
php artisan backup:cleanup [--days=30] [--dry-run]

# Verify backup integrity
php artisan backup:verify [backup-id] [--all]
```

---

## Routes

All routes protected by `auth` and `admin` middleware:

```
GET    /backups                    - List all backups
GET    /backups/create             - Backup creation form
POST   /backups                    - Create new backup
GET    /backups/{id}               - View backup details
DELETE /backups/{id}               - Delete backup
GET    /backups/{id}/download      - Download backup file
POST   /backups/{id}/verify        - Verify integrity

GET    /backups/schedules          - List schedules
POST   /backups/schedules          - Create schedule
PUT    /backups/schedules/{id}     - Update schedule
DELETE /backups/schedules/{id}     - Delete schedule

GET    /backups/exports            - List exports
POST   /backups/exports            - Create export
GET    /backups/exports/{id}/download - Download export

GET    /backups/restore/{id}       - Restore preview
POST   /backups/restore/{id}       - Execute restore
POST   /backups/restore/{id}/validate - Validate before restore
```

---

## Configuration

Add to `.env`:
```bash
BACKUP_DISK=local
BACKUP_PATH=backups
BACKUP_RETENTION_DAYS=30
BACKUP_COMPRESSION=true
BACKUP_COMPRESSION_METHOD=gzip
BACKUP_TIMEOUT=3600
MYSQLDUMP_PATH=/usr/bin/mysqldump
MYSQL_PATH=/usr/bin/mysql
BACKUP_ADMIN_EMAILS=admin@example.com
```

---

## Database Schema

### backups
- Primary backup records
- Tracks: file path, size, checksum, status, metadata
- Relations: belongsTo(User, BackupSchedule)

### backup_schedules
- Automated backup scheduling
- Tracks: frequency, time, retention, notifications
- Relations: hasMany(Backup)

### data_exports
- Selective data exports
- Tracks: export type, format, record count
- Relations: belongsTo(User)

---

## Testing

### Unit Tests
```bash
docker compose exec app php artisan test --filter=BackupServiceTest
docker compose exec app php artisan test --filter=DatabaseBackupServiceTest
docker compose exec app php artisan test --filter=ExportServiceTest
docker compose exec app php artisan test --filter=RestoreServiceTest
```

### Feature Tests
```bash
docker compose exec app php artisan test --filter=BackupControllerTest
docker compose exec app php artisan test --filter=ExportControllerTest
docker compose exec app php artisan test --filter=RestoreControllerTest
```

---

## Security Checklist

✅ All routes use `auth` and `admin` middleware
✅ Backups stored outside public directory
✅ Download requires admin authentication
✅ Restore requires validation before execution
✅ Automatic restore points created before restore
✅ Rate limiting on backup creation (5/hour)
✅ Audit trail via HasAuditFields trait
✅ File checksums for integrity verification

---

## Integration Points

### Dashboard (ReportingService)
Add backup status widget showing:
- Last backup timestamp
- Total backups count
- Storage used
- Next scheduled backup

### Inventory Module
Add export method:
```php
public function exportInventoryForBackup(): array
```

### Project Module
Add BOM export support via ExportService

---

## Documentation

📄 **Full Architecture:** `docs/BACKUP_MODULE_ARCHITECTURE.md` (46 pages)
📄 **Structure Audit:** `docs/MODULE_STRUCTURE_AUDIT.md` (detailed analysis)
📄 **Quick Reference:** This file

---

## Success Criteria

- ✅ Backups complete in < 5 minutes
- ✅ 100% backup integrity verification
- ✅ Restore completes in < 10 minutes
- ✅ Zero data loss on restore
- ✅ Automated daily backups running
- ✅ Email notifications on failures
- ✅ < 10GB storage for 30 days retention

---

## Next Steps

1. **Review architecture documents**
2. **Fix module structure issues** (composer.json, config)
3. **Create Backup module** using `php artisan module:make Backup`
4. **Follow implementation phases** (16-day timeline)
5. **Run tests at each phase**
6. **Deploy to production** after full testing

---

## Questions?

Reference the detailed architecture document for:
- Complete service method signatures
- Database schema with all fields
- Full configuration options
- Detailed security considerations
- Step-by-step implementation guide

**Architecture Document:** `/home/user/SteelFlow-MRP/docs/BACKUP_MODULE_ARCHITECTURE.md`
