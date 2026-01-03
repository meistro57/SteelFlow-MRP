# 🏗️ SteelFlow MRP

[![SteelFlow MRP CI](https://github.com/meistro57/SteelFlow-MRP/actions/workflows/laravel.yml/badge.svg)](https://github.com/meistro57/SteelFlow-MRP/actions/workflows/laravel.yml)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![Tailwind](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

**SteelFlow MRP** is a next-generation Manufacturing Resource Planning system precision-engineered for the steel fabrication industry. It bridges the gap between complex engineering data and shop-floor execution.

---

## 🌟 Vision
To replace obsolete legacy systems with a high-performance, web-native platform that centralizes estimating, production, and logistics into a single source of truth.

## 📊 Current Status

**Development Stage:** Foundation Complete, Feature Implementation In Progress

SteelFlow MRP has a **solid foundation** with complete database architecture (14 migrations, 29 models) and comprehensive backend services. The project is currently in **active development** with focus on building out the user interface and controllers to connect the backend logic to the frontend.

- ✅ **Infrastructure:** Docker environment, authentication, database schema
- ✅ **Backend Services:** Import parsers, nesting engines, inventory management, reporting
- 🔄 **Controllers & UI:** Partially implemented - core modules need frontend interfaces
- 📅 **Estimating Module:** Planned for Phase 8

See the [Roadmap & Progress](#️-roadmap--progress) section below for detailed status.

## 🛠️ The SteelFlow Stack

### **Backend Core**
- **Framework**: Laravel 11 (PHP 8.4+)
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis + Laravel Horizon
- **Search Engine**: Meilisearch (optional, for sub-millisecond lookups)

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
- **🌍 Global Ready**: Seamless switching between Metric and Imperial systems at the core logic level.

---

## 🗺️ Roadmap & Progress

### **Phase 1: Foundation** ✅ Complete
- [x] Dockerized Development Environment (Docker Compose v2)
- [x] Database Schema Foundation (14 migrations)
- [x] Project & Master Data Models (29 models)
- [x] Base Environment Configuration
- [x] Microsoft 365 OAuth Integration
- [x] Composer and NPM Configuration

### **Phase 2: BOM & Engineering** 🔄 In Progress
- [x] Database Schema (migrations complete)
- [x] Models: Project, Assembly, Part, Drawing
- [x] Services: BOMExtensionService, ReferenceDataService
- [x] Import Services (KISS/XSR parsers)
- [x] DrawingController & UI
- [ ] Full BOM Management Interface
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
- [x] Basic Production Pages
- [x] LabelService (ZPL generation)
- [ ] Complete Barcode Tracking App
- [ ] Work Area Routing Interface
- [ ] Labor Time Clocking UI

### **Phase 6: Shipping & Logistics** 📋 Backend Only
- [x] Database Schema (migrations complete)
- [x] Models: Load, LoadItem, ShippingDocument
- [x] Services: ShippingService
- [ ] Shipping Controllers & UI
- [ ] Load Builder Interface
- [ ] BOL & Packing List Generation
- [ ] Delivery Confirmation System

### **Phase 7: Reporting & Analytics** 🔄 In Progress
- [x] ReportController
- [x] Services: ReportingService
- [x] Dashboard Page (basic)
- [ ] BOM Reports
- [ ] Purchasing Reports
- [ ] Production Metrics Dashboard
- [ ] Inventory Reports
- [ ] Shipping Reports

### **Phase 8: Estimating Module** 📅 Planned
- [ ] Database Schema Design
- [ ] Bid & Revision Management
- [ ] Material Takeoff Engine
- [ ] Labor Standard Application
- [ ] Proposal & Quote Generation (PDF)
- [ ] Bid-to-Project Conversion Logic

**Legend:** ✅ Complete | 🔄 In Progress | 📋 Backend Only | 📅 Planned

---

## 🐳 Docker Services

SteelFlow MRP runs on a fully containerized infrastructure:

| Service | Container | Port | Description |
|---------|-----------|------|-------------|
| **App** | `steelflow-app` | - | PHP 8.4 FPM application server |
| **Web** | `steelflow-web` | 80 | Nginx web server (Alpine) |
| **Database** | `steelflow-db` | 3306 | MySQL 8.0 database |
| **Cache** | `steelflow-redis` | - | Redis for cache, sessions, and queues |
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

---

## 📂 Architecture Overview

```text
app/
├── Models/           # 29 Eloquent models (Projects, Assemblies, Parts, etc.)
├── Services/         # Business logic (BOM, Nesting, Inventory, Shipping, etc.)
├── Http/Controllers/ # Web controllers (Auth, Reports, Production, Labels, etc.)
└── Jobs/             # Background workers for heavy computations
resources/
├── js/
│   ├── Components/   # Vue components (ThemeToggle, BarcodeScanner, etc.)
│   └── Pages/        # Vue pages (Dashboard, Production, Reports, Auth)
└── views/            # Blade templates and PDF layouts
database/
└── migrations/       # 14 migrations covering all modules
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
