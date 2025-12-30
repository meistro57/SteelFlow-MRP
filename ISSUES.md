# Known Issues & Future Enhancements

These items represent identified "issues" or missing functionalities based on the project plan and current logic implementation.

## 🔴 High Priority / Imminent
- [ ] **KISS Importer Completion**: Implement full field mapping for `SHP` and `DET` records and handle `MTR` (bill of materials) lines.
- [ ] **Microsoft 365 OAuth UI**: Create the login view and connect the `login/microsoft` route to the frontend.
- [ ] **BOM Extension Trigger**: Add a post-import trigger to automatically run `BOMExtensionService` after a successful KISS file import.

## 🟡 Medium Priority
- [ ] **XSR Importer**: Implement the logic skeleton for XSR format imports.
- [ ] **Decimal Length Support**: Implement `parseLength` in `KissImporter` to handle `FT-IN-16` format common in the steel industry.
- [ ] **Drawing Revision Workflow**: Implement the service logic to handle file uploads and revision incrementing for Drawings.
- [ ] **Linear Nesting UI**: Create the visual layout for displaying nesting solutions on stock bars.

## 🟢 Low Priority / Polish
- [ ] **Soft Deletes Implementation**: Ensure all models (Parts, Assemblies, etc.) utilize the `SoftDeletes` trait correctly.
- [ ] **Search Indexing**: Implement Meilisearch indexing via Laravel Scout for fast Part/Mark lookups.
- [ ] **Report Templates**: Create the actual Blade templates for PDF generation (BOM, Load Lists).

---
*Tracked as of December 2024*
