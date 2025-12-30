# Known Issues & Future Enhancements

These items represent identified "issues" or missing functionalities based on the project plan and current logic implementation.

## 🔴 High Priority / Imminent
- [x] **KISS Importer Improvement**: Implemented `FT-IN-16` format parsing and integrated automatic BOM extension.
- [x] **Microsoft 365 OAuth UI**: Created the login view and connected the routes.
- [x] **BOM Extension Trigger**: Integrated automatic project extension after KISS/XSR imports.

## 🟡 Medium Priority
- [x] **XSR Importer**: Fully implemented XSR format imports with field mapping and BOM extension.
- [x] **Drawing Management**: Implemented file storage and revision tracking for Drawings.
- [x] **Label Printing**: Added ZPL generation service for parts and inventory.
- [x] **Mobile Scanning**: Added WebRTC-based barcode scanning component for shop floor tracking.
- [x] **Linear Nesting UI**: Created `NestingVisualizer` component for displaying bar nesting solutions.

## 🟢 Low Priority / Polish
- [x] **Soft Deletes Implementation**: Added `SoftDeletes` trait to all core models and created a migration.
- [x] **Search Indexing**: Integrated Laravel Scout indexing for Project, Assembly, and Part models.
- [x] **Report Templates**: Created Blade templates for BOM and Load List PDF generation.

---
*Tracked as of December 2024*
