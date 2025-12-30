# 🏗️ SteelFlow MRP

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![Tailwind](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

**SteelFlow MRP** is a next-generation Manufacturing Resource Planning system precision-engineered for the steel fabrication industry. It bridges the gap between complex engineering data and shop-floor execution.

---

## 🌟 Vision
To replace obsolete legacy systems with a high-performance, web-native platform that centralizes estimating, production, and logistics into a single source of truth.

## 🛠️ The SteelFlow Stack

### **Backend Core**
- **Framework**: Laravel 11 (PHP 8.3+)
- **Database**: MySQL 8.0 / PostgreSQL
- **Cache/Queue**: Redis + Laravel Horizon
- **Search Engine**: Meilisearch for sub-millisecond lookups

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

### **Phase 1: Foundation** (Infrastructure)
- [x] Dockerized Development Environment
- [x] Database Schema Foundation (Migrations)
- [x] Project & Master Data Models
- [x] Base Environment Configuration
- [x] Microsoft 365 OAuth Integration
- [x] UI layout & Navigation Shell

### **Phase 2: BOM & Engineering** (Complete)
- [x] Weight & Pricing Services
- [x] KISS/XSR Import Skeleton
- [x] Reference Data Management
- [x] KISS Parser Implementation
- [x] BOM Extension Logic
- [x] Drawing Revision Tracking

### **Phase 3: Procurement & Inventory** (Complete)
- [x] Purchase Order Workflow
- [x] Material Receiving & Heat Certs
- [x] Multi-Location Stock Tracking
- [x] Stock Move Audit Trails

### **Phase 4: Optimization (Nesting)**
- [ ] Linear Nesting Engine
- [ ] Plate Nesting Integration (ProNest/Custom)
- [ ] Remnant Management
- [ ] Cut List Generation

### **Phase 5: Shop Floor Execution**
- [ ] Production Batching
- [ ] Work Area Routing
- [ ] Real-time Barcode Tracking App
- [ ] Labor Time Clocking

### **Phase 6: Shipping & Logistics**
- [ ] Load Builder
- [ ] Shipping Document Generation (BOL/Packing Lists)
- [ ] Delivery Confirmation

---

## 🔧 Installation & Setup

### ⚙️ Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- [Node.js](https://nodejs.org/) (Local development)

### 🚀 Quick Start
```bash
# 1. Clone the vision
git clone https://github.com/meistro57/SteelFlow-MRP.git && cd SteelFlow-MRP

# 2. Configure the environment
cp .env.example .env

# 3. Launch the containers
docker-compose up -d

# 4. Initialize the application
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed

# 5. Compile the frontend
npm install && npm run dev
```

---

## 📂 Architecture Overview

```text
app/
├── Models/           # Database blueprints
├── Services/         # Heavy-lifting logic (Nesting, Weight, Imports)
├── Http/             # The API & Web Gateway
└── Jobs/             # Background workers for heavy computations
resources/
├── js/               # Vue pages that feel like magic
└── views/            # Report templates for high-quality PDFs
```

---
*Built with ❤️ for the Steel Industry.*
