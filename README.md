#  Steel🏗️low MRP

[![SteelFlow MRP CI](https://github.com/meistro57/SteelFlow-MRP/actions/workflows/laravel.yml/badge.svg)](https://github.com/meistro57/SteelFlow-MRP/actions/workflows/laravel.yml)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![Tailwind](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

**SteelFlow MRP** is a next-generation Manufacturing Resource Planning system precision-engineered for the steel fabrication industry. It bridges the gap between complex engineering data and shop-floor execution.

---

## 🌟 Vision
To replace obsolete legacy systems with a high-performance, web-native platform that centralizes estimating, production, and logistics into a single source of truth.

<img width="1916" height="911" alt="image" src="https://github.com/user-attachments/assets/17b30f27-36a4-427c-905f-6e431f71aa81" />

<img width="2344" height="1548" alt="image" src="https://github.com/user-attachments/assets/391e2087-743a-4023-aab8-da24db3ca3ef" />

<img width="1916" height="909" alt="image" src="https://github.com/user-attachments/assets/06464e92-605f-4c40-8f9c-82aeafec05a4" />

<img width="1912" height="909" alt="image" src="https://github.com/user-attachments/assets/393e743a-a977-4ecb-8a57-67b8dd07a68b" />

<img width="1919" height="911" alt="image" src="https://github.com/user-attachments/assets/0b2c2565-a4a7-4d65-ac38-4d651987a003" />

<img width="2345" height="1545" alt="image" src="https://github.com/user-attachments/assets/368efc51-46c7-409f-9276-aef3d5835487" />

<img width="2344" height="1542" alt="image" src="https://github.com/user-attachments/assets/5162a2af-132e-4acd-a9c8-2189c231dd99" />

<img width="1919" height="909" alt="image" src="https://github.com/user-attachments/assets/24f33fa7-ad59-417a-b96c-c6c2dcd71059" />

<img width="1919" height="912" alt="image" src="https://github.com/user-attachments/assets/0575a13e-be4c-496d-8891-83ceb068a3a4" />

<img width="558" height="723" alt="image" src="https://github.com/user-attachments/assets/25012f34-1b12-49fd-a366-4d0f8c2d0e60" />
<img width="1919" height="912" alt="image" src="https://github.com/user-attachments/assets/494f5a38-b904-44bd-8d25-2b16f082c945" />




## 📊 Current Status

**Development Stage:** Core Platform Built, Feature Implementation In Progress

SteelFlow MRP has a **solid foundation** with a complete database architecture (43 migrations, 70 models) and a massive modular service layer. The project is currently in **active development** with the core ERP footprint already established and operational.

- ✅ **Infrastructure:** Docker environment, authentication, database schema, RBAC
- ✅ **Core Services:** BOM, inventory, nesting, production, shipping, reporting, finance, NCR
- 🔄 **Controllers & UI:** 22+ controllers and 45+ Inertia/Vue pages covering the entire manufacturing lifecycle
- 📅 **Estimating Module:** Planned for future release

See the [Roadmap & Progress](#️-roadmap--progress) section below for detailed status.

## 🛠️ The SteelFlow Stack

### **Backend Core**
- **Framework**: Laravel 11 (PHP 8.4+)
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis + Laravel Horizon
- **Search Engine**: Meilisearch (for sub-millisecond lookups)

### **Frontend Experience**
- **Framework**: Vue.js 3 + Inertia.js (The "Classic Monolith" feel with SPA speed)
- **State Management**: Pinia
- **UI Architecture**: Tailwind CSS + Headless UI

---

## ✨ Enterprise Features

- **📦 BOM Management**: High-fidelity tracking of piece marks, assemblies, and detailed part lists.
- **🔌 CAD Integration**: Native parsers for **KISS** and **XSR** formats.
- **📐 Material Optimization**: 1D (Linear) and 2D (Plate) nesting engines to minimize waste.
- **🛒 Smart Purchasing**: PO lifecycle tracking with integrated Heat Number and Mill Cert management.
- **⚡ Shop Floor Tracking**: Real-time progress updates via mobile-first barcode/QR scanning.
- **🚚 Logistics Control**: Automated load building, BOL generation, and shipping history.
- **📈 Advanced Optimization**: Optimize Linear Nesting module for maximum yield and remnant recovery.
- **🏪 Multi-mode POS**: Point of Sale interface for retail, gas exchange, and quick service invoicing.
- **🛠️ Service & Operation**: Dedicated Service Ticket and Shop Ticket modules for field and shop floor management.
- **📉 Quality Control**: Non-Conformance Report (NCR) tracking with integrated rework workflow.
- **⚖️ Financial Integrity**: Three-Way Match validation (PO vs. Receipt vs. Invoice) for procurement.
- **💰 Construction Billing**: AIA-style Progress Billing with automatic retainage management.
- **🌍 Global Ready**: Seamless switching between Metric and Imperial systems at the core logic level.

---

## 🗺️ Roadmap & Progress

### **Phase 1: Foundation** ✅ Complete
- [x] Dockerized Development Environment (Docker Compose v2)
- [x] Database Schema Foundation (43 migrations)
- [x] Project & Master Data Models (70 models)
- [x] Base Environment Configuration
- [x] Microsoft 365 OAuth Integration
- [x] RBAC & Audit Trail Infrastructure
- [x] Modular Enterprise Architecture

### **Phase 2: BOM & Engineering** 🔄 In Progress
- [x] Database Schema (migrations complete)
- [x] Models: Project, Assembly, Part, Drawing
- [x] Services: BOMExtensionService, ReferenceDataService
- [x] Import Services (KISS/XSR parsers)
- [x] DrawingController & UI
- [x] ProjectController & UI
- [ ] Assembly/Part Management Interface
- [ ] Weight & Pricing UI Integration
- [ ] KISS/XSR Import UI

### **Phase 3: Procurement & Inventory** 🔄 In Progress
- [x] Database Schema (migrations complete)
- [x] Models: PurchaseOrder, StockItem, ReceivingRecord
- [x] Services: InventoryService
- [ ] Purchase Order Controllers & UI
- [ ] Material Receiving Interface
- [ ] Stock Tracking Dashboard
- [ ] Heat Certificate Management UI

### **Phase 4: Optimization (Nesting)** 🔄 In Progress
- [x] Database Schema (migrations complete)
- [x] Models: Nesting, NestingBar, NestingPart
- [x] Services: Nesting services
- [ ] Nesting Controllers & UI
- [ ] Linear Nesting Interface
- [ ] Plate Nesting Integration
- [ ] Cut List Generation UI

### **Phase 5: Shop Floor Execution** 🔄 In Progress
- [x] Database Schema (migrations complete)
- [x] Models: ProductionBatch, WorkArea, TimeEntry, PartWorkArea
- [x] Services: Production services
- [x] ProductionController
- [x] Production Dashboard + Scan Pages
- [x] LabelService (ZPL generation)
- [ ] Complete Barcode Tracking App
- [ ] Work Area Routing Interface
- [ ] Labor Time Clocking UI

### **Phase 6: Shipping & Logistics** 🔄 In Progress
- [x] Database Schema (migrations complete)
- [x] Models: Load, LoadItem, ShippingDocument
- [x] Services: ShippingService
- [x] ShippingController + Dashboard UI
- [ ] Load Builder Interface
- [ ] BOL & Packing List Generation
- [ ] Delivery Confirmation System

### **Phase 7: Reporting & Analytics** 🔄 In Progress
- [x] ReportController
- [x] Services: ReportingService
- [x] Dashboard Page (basic)
- [x] BOM Reports (project BOM)
- [ ] Purchasing Reports
- [ ] Production Metrics Dashboard
- [x] Inventory Reports
- [ ] Shipping Reports

### **Phase 8: Estimating Module** 📅 Planned
- [ ] Database Schema Design
- [ ] Bid & Revision Management
- [ ] Material Takeoff Engine
- [ ] Labor Standard Application
- [ ] Proposal & Quote Generation (PDF)
- [ ] Bid-to-Project Conversion Logic

### **Phase 9: Specialized Modules** ✅ Complete
- [x] **NCR & Remediation**: Quality failure state machine and remakes
- [x] **Three-Way Match**: Financial validation for procurement
- [x] **Progress Billing**: AIA standards and retainage calculations
- [x] **Documents Module**: Centralized storage with versioning and CAD links
- [x] **AuditLog Module**: Immutable history tracking for all changes
- [x] **Shop Ticket Module**: Digital production orders and real-time tracking
- [x] **Finance & Billing**: Comprehensive AP/AR and invoicing system
- [ ] **Optimize Linear Nesting**: Yield maximization algorithms (Planned)
- [ ] **Point of Sale (POS)**: Retail and gas sales interface (Planned)
- [ ] **UI Editor Module**: Visual dashboard and layout builder (Planned)
- [ ] **Service Ticket Module**: Field service and maintenance tracking (Planned)

**Legend:** ✅ Complete | 🔄 In Progress | 📅 Planned

---

## 🐳 Docker Services

SteelFlow MRP runs on a fully containerized infrastructure:

| Service | Container | Port | Description |
|---------|-----------|------|-------------|
| **App** | `steelflow-app` | - | PHP 8.4 FPM application server |
| **Web** | `steelflow-web` | 80 | Nginx web server (Alpine) |
| **Database** | `steelflow-db` | 3306 | MySQL 8.0 database |
| **Cache** | `steelflow-redis` | - | Redis for cache, sessions, and queues |
| **Search** | `steelflow-meilisearch` | 7700 | Meilisearch for fast search indexing |
| **Admin** | `steelflow-phpmyadmin` | 8080 | phpMyAdmin database management |

---

## 🔧 Installation & Setup

### ⚙️ Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Node.js](https://nodejs.org/) (Local development)

### 🚀 Quick Start

#### Option 1: Using the Install Script (Recommended for first-time setup)
```bash
# 1. Clone the repository
git clone https://github.com/meistro57/SteelFlow-MRP.git && cd SteelFlow-MRP

# 2. Configure the environment
cp .env.example .env

# 3. Run the install script
./scripts/install.sh
```

#### Option 2: Manual Setup
```bash
# 1. Clone the repository
git clone https://github.com/meistro57/SteelFlow-MRP.git && cd SteelFlow-MRP

# 2. Configure the environment
cp .env.example .env

# 3. Launch the containers
docker compose up -d

# 4. Initialize the application
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

# 5. Compile the frontend
npm install && npm run dev
```

## 📱 LAN & Mobile Access (HTTP/HTTPS)

Use this section when you want to open SteelFlow from a phone or tablet on the same Wi‑Fi network.

### ✅ Find your host machine LAN IP

- **Linux/macOS**: run `hostname -I` (Linux) or `ipconfig getifaddr en0` (macOS) and pick the LAN address (usually `192.168.x.x` or `10.x.x.x`).
- **Windows**: run `ipconfig` and use the IPv4 address listed under your active adapter.
- **Docker Desktop**: your **host** LAN IP is still the one you use; containers are already mapped via `docker compose`.

### 🌐 Access from a phone (HTTP)

1. Make sure your phone is on the **same Wi‑Fi** as your dev machine.
2. Open the site in the phone browser using the LAN IP:
   - **Nginx / app**: `http://<LAN-IP>/`
   - **Vite dev server** (if running): `http://<LAN-IP>:5173/`

### 🔒 Access from a phone (HTTPS)

If you have self‑signed HTTPS enabled (e.g. via mkcert/Traefik/Nginx):

1. Browse to `https://<LAN-IP>/` from the phone.
2. Install and trust the **local root CA** used to sign the cert (not just the leaf cert):
   - **iOS**:
     - Send the CA file to the phone (AirDrop, Files, or email).
     - Install it via **Settings → General → VPN & Device Management**.
     - **Enable trust** in **Settings → General → About → Certificate Trust Settings**.
   - **Android**:
     - Copy the CA file to the device.
     - Install via **Settings → Security → Install a certificate → CA certificate**.
     - On newer Android versions, user‑installed CAs may not be trusted by all apps; browsers are usually fine.

### ⚠️ Common pitfalls

- **Firewall**: allow inbound traffic to ports **80**, **443**, and **5173** (if Vite is running).
- **Captive portals**: guest Wi‑Fi networks often block device‑to‑device traffic.
- **HMR host settings**: Vite’s HMR host is set to `localhost` in `vite.config.js`.  
  Update `server.hmr.host` to your LAN IP (or make it conditional via env) so mobile HMR works.
- **HTTPS + HMR**: if using HTTPS for the dev server, ensure the HMR client is also pointed at the HTTPS host/port.

### 🔄 Updating the Application

To update SteelFlow MRP after pulling new changes or updating dependencies, simply run:

```bash
./update.sh
```

This automated script will:
- Pull the latest code from git (if applicable)
- Rebuild Docker containers
- Update all dependencies (PHP & Node)
- Run database migrations
- Seed the database (if empty)
- Clear and rebuild all caches
- Build frontend assets
- Set proper permissions

**Default Login Credentials (after seeding):**
- Email: `admin@steelflow.local`
- Password: `password`

**Default Database Credentials (.env file):**
- Database: `steelflow`
- Username: `steelflow`
- Password: `secret`

> **Note:** These are development defaults. For production deployments, change the database credentials in your `.env` file to secure values.

---

## 📂 Architecture Overview

```text
app/
├── Models/           # Core models (Projects, Assemblies, Parts, etc.)
├── Services/         # Business logic (BOM, Nesting, Inventory, Shipping, etc.)
├── Http/Controllers/ # Web controllers (Auth, Reports, Production, Labels, etc.)
└── Jobs/             # Background workers for heavy computations
Modules/
├── Documents/        # Centralized file management & versioning (CAD drawings, KISS, etc.)
├── Inventory/        # Material tracking, receiving and vendors
├── Finance/          # Invoicing, Three-Way Match, Progress Billing
├── NCR/              # Quality non-conformance tracking
├── AuditLog/         # Immutable audit trails for all system actions
├── Authz/            # Role-based access control and sophisticated permissions
├── UPF/              # Legacy FabTrol material catalog compatibility
├── ShopTicket/       # Digital production orders and real-time tracking
├── PdfCenter/        # Automated document generation pipeline
├── Backup/           # Automated data protection and cloud sync
└── ProductionScheduling/ # Machine capacity and maintenance planning
resources/
├── js/
│   ├── Components/   # Vue components (ThemeToggle, BarcodeScanner, etc.)
│   └── Pages/        # Vue pages (Dashboard, Production, Reports, Auth)
└── views/            # Blade templates and PDF layouts
database/
└── migrations/       # 43 migrations covering all modules
docs/
├── ESTIMATING_PLAN.md # Roadmap for the estimating module
├── INSTALLATION.md    # Complete setup and installation guide
└── GUI_MANAGER.md     # Theme switching and UI customization
scripts/
├── install.sh        # First-time installation script
└── update.sh         # Update and deployment script
CRUSH.md               # Core concepts and business rules reference
ROADMAP.md             # Development status and priorities
```

---

## 🤝 Community & Legacy

### **Open Source**
- 📄 [LICENSE](LICENSE) (MIT)
- 🛠️ [CONTRIBUTING.md](CONTRIBUTING.md)
- 🌈 [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md)

### **Legacy Foundation**
This project is built with deep respect for the original FabTrol system.

---
*Built with ❤️ for the Steel Industry.*
