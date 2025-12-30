# Known Issues & Future Enhancements

These items represent identified "issues" or missing functionalities based on the project plan and current logic implementation.

## 🔴 High Priority / Imminent
- [x] **KISS Importer Improvement**: Implemented `FT-IN-16` format parsing for accurate length calculations.
- [ ] **Microsoft 365 OAuth UI**: Create the login view and connect the `login/microsoft` route to the frontend.
- [ ] **BOM Extension Trigger**: Add a post-import trigger to automatically run `BOMExtensionService` after a successful KISS file import.

## 🟡 Medium Priority
- [x] **XSR Importer**: Fully implemented XSR format imports with field mapping.
- [x] **Drawing Management**: Implemented file storage and revision tracking for Drawings.
- [x] **Label Printing**: Added ZPL generation service for parts and inventory.
- [x] **Mobile Scanning**: Added WebRTC-based barcode scanning component for shop floor tracking.
- [ ] **Linear Nesting UI**: Create the visual layout for displaying nesting solutions on stock bars.

## 🟢 Low Priority / Polish
- [ ] **Soft Deletes Implementation**: Ensure all models (Parts, Assemblies, etc.) utilize the `SoftDeletes` trait correctly.
- [ ] **Search Indexing**: Implement Meilisearch indexing via Laravel Scout for fast Part/Mark lookups.
- [ ] **Report Templates**: Create the actual Blade templates for PDF generation (BOM, Load Lists).

---
*Tracked as of December 2024*
