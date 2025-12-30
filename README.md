# SteelFlow MRP

**SteelFlow MRP** is a modern, web-based manufacturing resource planning system designed specifically for the steel fabrication industry. It is built to replace legacy systems (like FabTrol) with a high-performance, scalable, and user-friendly platform.

## 🚀 Mission
Streamline steel fabrication from estimating to shipping through automated workflows, real-time tracking, and material optimization.

## 🛠 Technology Stack

- **Backend**: Laravel 11 (PHP 8.3+)
- **Frontend**: Vue.js 3 + Inertia.js
- **Database**: MySQL 8.0 / PostgreSQL
- **Real-time**: Redis + Laravel Horizon
- **Search**: Meilisearch
- **Styling**: Tailwind CSS
- **Infrastructure**: Docker + Nginx

## ✨ Key Features

- **BOM Management**: Complete Bill of Materials tracking with support for Assemblies and Parts.
- **CAD Integration**: Import data from industry-standard formats (KISS, XSR).
- **Material Optimization**: Advanced linear and plate nesting engines.
- **Purchasing & Inventory**: Full procurement cycle with heat number and mill cert tracking.
- **Production Tracking**: Real-time shop floor progress using barcode/QR scanning.
- **Shipping Control**: Simplified load building, weight verification, and shipping documentation.
- **Multi-Unit Support**: Native support for both Imperial and Metric systems.

## 📁 Project Structure

```text
steelflow/
├── app/
│   ├── Models/           # Eloquent Database Models
│   ├── Services/         # Advanced Business Logic (Import, Nesting, Production)
│   ├── Http/             # Controllers, Requests, Middleware
│   ├── Jobs/             # Asynchronous Background Tasks
│   └── Events/           # System Events & Listeners
├── database/
│   ├── migrations/       # Database Schema Definitions
│   └── seeders/          # Initial Data & Legacy Imports
├── resources/
│   ├── js/               # Vue.js Pages and Components
│   └── views/            # Backend templates for PDF Reports
└── tests/                # Automated Feature and Unit Tests
```

## 🛤 Roadmap

1.  **Phase 1: Foundation**: Core infrastructure, authentication, and master data.
2.  **Phase 2: BOM Management**: Assembly/Part handling and CAD imports.
3.  **Phase 3: Purchasing**: PO workflow and inventory management.
4.  **Phase 4: Nesting**: Linear and plate optimization engines.
5.  **Phase 5: Production**: Shop floor tracking and barcode integration.
6.  **Phase 6: Shipping**: Load building and logistics.

## 🔧 Getting Started

### Prerequisites
- Docker & Docker Compose
- Node.js & NPM

### Setup
1. Clone the repository.
2. Copy `.env.example` to `.env`.
3. Start the environment:
   ```bash
   docker-compose up -d
   ```
4. Install dependencies:
   ```bash
   docker-compose exec app composer install
   npm install && npm run build
   ```
5. Run migrations and seed data:
   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

---
*Built to redefine steel fabrication management.*
