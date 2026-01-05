# FabTrol Inventory and Shapes Data Structure

## Raw Objects for Inventory Creation

### 1. SHAPES/MATERIALS MASTER DATA (Unit Price File - UPF)

#### FTPRICE_BASE.DBF (5.8MB)
**Location:** `/home/mark/Fabtrol/Unit/FTPRICE_BASE.DBF`

This is THE PRIMARY shapes database - the raw object that inventory can be created from.

**Key Fields:**
- TYPE - Material type (W-beams, Channels, Angles, Tubes, Plates, etc.)
- WTDESIG - Weight designation
- COSTDESIG - Cost designation  
- SAWKERF - Saw kerf allowance
- SAWKERFIN - Saw kerf finish
- AREA - Cross-sectional area
- INCLUDED - Whether included in system
- METRIC - Imperial/Metric flag
- WTPER - Weight per unit
- SURFPER - Surface area per unit
- COSTPER - Cost per unit
- CSTHISTORY - Cost history
- CATEGORY - Material category
- SURFPERM2 - Surface area per square meter
- LISTCODE - List code
- SERIAL - Serial/unique identifier
- SERIALSIZE - Serial size designation
- Mill dimensions (MILLLINEAR, MILLFT, MILLIN, MILLFRAC, MILLMILL)
- Length and width fields for different suppliers (LENG0-4, L0WIDTH0-2, etc.)

**Associated Files:**
- FTPRICE_BASE.CDX (2.4MB) - Compound index
- FTPRICE_BASE.FPT (5.2MB) - Memo fields

---

#### FTMATL_BASE.DBF (55KB)
**Location:** `/home/mark/Fabtrol/Unit/FTMATL_BASE.DBF`

Material TYPES master table (W-beams, C-channels, angles, tubes, plates, bars, etc.)

**Key Fields:**
- TYPE - Material type code (W, C, L, M, S, HSS, WT, etc.)
- TITLE - Full description
- MESSAGE - Notes/messages
- SAWKERF - Default saw kerf
- SAWKERFIN - Saw kerf finish
- WTDESIG - Weight designation format
- COSTDESIG - Cost designation format
- SAVEREM - Save remnants flag
- AREA - Area calculation method
- INCLUDED - Include in system
- Length/Width/Supplier fields (LENG0-4, L0WIDTH0-2, L0SUPP0-2, etc.)

**Associated Files:**
- FTMATL_BASE.CDX (7.5KB) - Index

---

#### FTGRADES_BASE.DBF (817 bytes)
**Location:** `/home/mark/Fabtrol/Unit/FTGRADES_BASE.DBF`

Material GRADES master table (A36, A572, A992, etc.)

**Known Grades:**
- **M** - Mild (Carbon Steel, 490.05 lb/ft³ / 7849.9 kg/m³)
- **H** - High Strength (490.05 lb/ft³ / 7849.9 kg/m³)
- **A** - Aluminum (169.32 lb/ft³ / 2712.3 kg/m³)
- **S** - Stainless (512.04 lb/ft³ / 8202.2 kg/m³)
- **C** - Cold Rolled (490.05 lb/ft³ / 7849.9 kg/m³)
- **B** - Bolts/Hardware (0.00 density)
- **P** - Paint (0.00 density)
- **I** - Inapplicable (0.00 density)

**Key Fields:**
- NAME - Grade name
- CODE - Single character code
- DENSITY - Density in imperial (lb/ft³)
- METRICDENS - Density in metric (kg/m³)
- PKEY - Primary key

**Associated Files:**
- FTGRADES_BASE.CDX (7.5KB) - Index

---

### 2. INVENTORY/STOCK DATA

#### FTSTKBID_BASE.DBF (25MB) ⭐ MAIN STOCK TABLE
**Location:** `/home/mark/Fabtrol/Unit/FTSTKBID_BASE.DBF`

This is the ACTIVE INVENTORY table - actual stock on hand.

**Key Fields:**
- TYPE - Material type (links to FTMATL_BASE)
- GRADE - Material grade (links to FTGRADES_BASE)
- GRADENAME - Grade name
- SIZE - Actual size/dimensions
- LENGTH - Piece length
- WEIGHT - Piece weight
- DATEAVAIL - Date available
- DATEUSED - Date used
- INVDATE - Inventory date
- HISTDATE - History date
- (Additional fields for stock status, location, heat certs, etc.)

**Associated Files:**
- FTSTKBID_BASE.CDX (4.5MB) - Compound index
- FTSTKBID_BASE.BAK (3.8MB) - Backup
- FTSTKBID_BASE.TBK (1.4KB) - Backup

---

#### FTSTKCHG_BASE.DBF (14KB)
**Location:** `/home/mark/Fabtrol/Unit/FTSTKCHG_BASE.DBF`

Stock changes/transactions log.

**Associated Files:**
- FTSTKCHG_BASE.CDX - Index

---

#### STKMOVE_BASE.DBF (7.4MB)
**Location:** `/home/mark/Fabtrol/Unit/STKMOVE_BASE.DBF`

Stock movements between locations/jobs.

**Associated Files:**
- STKMOVE_BASE.CDX - Index

---

#### STKAUDIT_BASE.DBF (36MB)
**Location:** `/home/mark/Fabtrol/Unit/STKAUDIT_BASE.DBF`

Stock audit trail - complete history of all stock transactions.

**Associated Files:**
- STKAUDIT_BASE.CDX - Index

---

#### STKREORD_BASE.DBF (23KB)
**Location:** `/home/mark/Fabtrol/Unit/STKREORD_BASE.DBF`

Stock reorder points and automatic reordering settings.

**Associated Files:**
- STKREORD_BASE.CDX - Index

---

#### SEL_STOCK_EVENT_LOG.DBF (7.1MB)
**Location:** `/home/mark/Fabtrol/Unit/SEL_STOCK_EVENT_LOG.DBF`

Stock event log for tracking stock selection/allocation events.

**Associated Files:**
- SEL_STOCK_EVENT_LOG.CDX (867KB) - Index

---

#### STKPEREA_BASE.DBF (12KB)
**Location:** `/home/mark/Fabtrol/Bids/STKPEREA_BASE.DBF`

Stock per area - stock allocated to specific workshop areas.

**Associated Files:**
- STKPEREA_BASE.CDX (7.5KB) - Index

---

### 3. AISC STANDARD SHAPES DATABASE (External Reference)

#### aisc-shapes-database-v16.0_a1085.xlsx (132KB)
**Location:** `/home/mark/Fabtrol/aisc-shapes-database-v16.0_a1085.xlsx`

AISC (American Institute of Steel Construction) standard shapes database - compact version.

#### aisc-shapes-database-v16.0h.xlsx (17MB)  
**Location:** `/home/mark/Fabtrol/aisc-shapes-database-v16.0h.xlsx`

AISC standard shapes database - complete/historical version with all shape properties.

**Purpose:**
- Reference data for standard structural steel shapes
- W-beams, S-beams, C-channels, angles, HSS, etc.
- Dimensions, section properties, weights
- Used to populate FTPRICE_BASE with standard shapes

---

## Data Flow: From Shapes to Inventory

```
┌─────────────────────────────────────────────────────────────┐
│  MASTER SHAPES DATA (Unit Price File)                      │
│                                                             │
│  FTPRICE_BASE.DBF ← FTMATL_BASE.DBF + FTGRADES_BASE.DBF   │
│  (All possible material shapes, sizes, grades)             │
│  - W10x49, C12x20.7, L4x4x1/2, HSS6x6x1/4, PL1/2, etc.   │
│  - Each with dimensions, weight/ft, cost/ft                │
│  - Can reference AISC shapes database                      │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│  ACTIVE INVENTORY (Stock on hand)                          │
│                                                             │
│  FTSTKBID_BASE.DBF                                         │
│  - Specific pieces of material in inventory                │
│  - Links to FTPRICE_BASE via TYPE+GRADE+SIZE              │
│  - Each record = one piece with length, weight, location   │
│  - Status: Free, Reserved, Assigned, Committed, Used       │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│  INVENTORY TRANSACTIONS                                     │
│                                                             │
│  FTSTKCHG_BASE.DBF  - Stock changes                        │
│  STKMOVE_BASE.DBF   - Movements between locations          │
│  STKAUDIT_BASE.DBF  - Complete audit trail                 │
│  SEL_STOCK_EVENT_LOG.DBF - Selection/allocation events     │
└─────────────────────────────────────────────────────────────┘
```

---

## Relationships

### Primary Hierarchy:
```
FTMATL_BASE (Types: W, C, L, M, HSS, WT, etc.)
    │
    ├── FTGRADES_BASE (Grades: M, H, A, S, C)
    │       │
    │       └── FTPRICE_BASE (Specific sizes: W10x49, W12x26, etc.)
    │                   │
    │                   └── FTSTKBID_BASE (Actual inventory pieces)
    │
    └── Related Purchase/Supplier data:
            - FTPOFILE_BASE (5.9MB) - PO line items
            - FTPOMAST_BASE (1.9MB) - PO master
            - FTPORECV_BASE (1.8MB) - PO receiving
            - FTPOSUPP_BASE (65KB) - Suppliers
```

### Key Identifiers:
- **TYPE + GRADE + SIZE** = Unique material definition
- **StockId** = Unique inventory piece identifier
- **BarNum/NstId** = Links to nesting solutions
- **FileNum** = Links to project

---

## Common Material Types (Examples)

**Structural Shapes:**
- **W** - Wide flange beams (W10x49, W12x26, W14x90, etc.)
- **S** - Standard beams (S10x35, S12x50, etc.)
- **C** - Channels (C10x30, C12x20.7, etc.)
- **MC** - Miscellaneous channels
- **L** - Angles (L4x4x1/2, L3x3x3/8, etc.)
- **WT** - Structural tees (cut from W-beams)

**Hollow Sections:**
- **HSS** - Hollow structural sections (HSS6x6x1/4, HSS4x4x3/8, etc.)
- **PIPE** - Pipe sections
- **TUBE** - Rectangular/square tubing

**Plates & Bars:**
- **PL** - Plates (PL1/2, PL3/4, PL1, etc.)
- **BAR** - Flat bars, round bars, square bars

**Other:**
- **MISC** - Miscellaneous items
- **BOLT** - Bolts/fasteners
- **WELD** - Welding materials

---

## Notes

1. **FTPRICE_BASE is the "catalog"** - all possible materials that can be ordered/inventoried
2. **FTSTKBID_BASE is the "warehouse"** - actual physical pieces in stock
3. The system supports both imperial and metric units
4. Heat certificate tracking for material traceability
5. Remnant management - leftover pieces from cuts are tracked back to stock
6. Stock status workflow: Free → Reserved → Assigned → Committed → Used
7. Integration with OptiMiser (linear nesting) and Plate Manager (plate nesting)

