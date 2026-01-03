# SteelFlow MRP Installation Guide

Complete installation and setup guide for SteelFlow MRP on Windows (WSL2), Linux, and macOS.

---

## Prerequisites

Before installing SteelFlow MRP, ensure you have the following:

### Required Software

| Software | Version | Purpose |
|----------|---------|---------|
| [Docker Desktop](https://www.docker.com/products/docker-desktop/) | Latest | Container runtime |
| [Git](https://git-scm.com/) | 2.x+ | Version control |

### Recommended (for local development)

| Software | Version | Purpose |
|----------|---------|---------|
| [Node.js](https://nodejs.org/) | 20.x LTS | Frontend asset compilation |
| [PHP](https://www.php.net/) | 8.4+ | Local Composer/Artisan commands |

### Windows-Specific Requirements

1. **WSL2 Enabled**: Windows Subsystem for Linux 2 must be installed
   ```powershell
   # In PowerShell (Admin)
   wsl --install
   ```
2. **Docker Desktop WSL2 Integration**: Enable in Docker Desktop Settings > Resources > WSL Integration

---

## Quick Installation

### Option 1: Automated Install (Recommended)

```bash
# Clone the repository
git clone https://github.com/meistro57/SteelFlow-MRP.git
cd SteelFlow-MRP

# Configure environment
cp .env.example .env

# Run the install script
chmod +x scripts/install.sh
./scripts/install.sh
```

The install script will:
1. Verify Docker is installed and running
2. Create `.env` from template (if not exists)
3. Build and start all Docker containers
4. Wait for MySQL to initialize
5. Install PHP dependencies via Composer
6. Install Node.js dependencies and build assets
7. Generate application key
8. Run database migrations and seed initial data

### Option 2: Manual Installation

```bash
# 1. Clone the repository
git clone https://github.com/meistro57/SteelFlow-MRP.git
cd SteelFlow-MRP

# 2. Configure environment
cp .env.example .env

# 3. Build and start Docker containers
docker compose up -d --build

# 4. Wait for MySQL (check logs if needed)
docker compose logs -f mysql

# 5. Install PHP dependencies
docker compose exec app composer install

# 6. Generate application key
docker compose exec app php artisan key:generate

# 7. Run database migrations and seed
docker compose exec app php artisan migrate --seed

# 8. Install frontend dependencies (if Node.js available locally)
npm install && npm run build

# OR build inside container:
docker compose exec app npm install
docker compose exec app npm run build
```

---

## Docker Services Reference

After installation, the following services will be running:

| Service | Container Name | Port | URL |
|---------|---------------|------|-----|
| Application | `steelflow-app` | - | (internal PHP-FPM) |
| Web Server | `steelflow-web` | 80 | http://localhost |
| Database | `steelflow-db` | 3306 | mysql://localhost:3306 |
| Redis Cache | `steelflow-redis` | 6379 | (internal) |
| phpMyAdmin | `steelflow-phpmyadmin` | 8080 | http://localhost:8080 |

### Accessing Services

- **Application**: http://localhost
- **phpMyAdmin**: http://localhost:8080
  - Server: `mysql`
  - Username: `steelflow` (or `root`)
  - Password: `secret` (default from .env)

---

## Default Login Credentials

After seeding the database, you can log in with:

| Field | Value |
|-------|-------|
| Email | `admin@steelflow.local` |
| Password | `password` |

---

## Environment Configuration

Key environment variables in `.env`:

```bash
# Application
APP_NAME="SteelFlow MRP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=steelflow
DB_USERNAME=steelflow
DB_PASSWORD=secret

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Search (Optional)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
```

### Azure OAuth (Optional)

For Microsoft 365 authentication, configure:

```bash
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret
AZURE_TENANT_ID=your-tenant-id
AZURE_REDIRECT_URI=http://localhost/auth/callback
```

---

## Updating SteelFlow MRP

To update an existing installation:

```bash
chmod +x update.sh
./update.sh
```

The update script will:
1. Pull latest code from git (if applicable)
2. Stop and rebuild Docker containers
3. Update PHP and Node.js dependencies
4. Clear and rebuild all caches
5. Run any new database migrations
6. Seed database if empty
7. Build frontend assets
8. Set proper file permissions

---

## Testing

Run the full test suite to verify your installation:

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific test suites
docker compose exec app php artisan test --testsuite=Unit
docker compose exec app php artisan test --testsuite=Feature

# Run with coverage (requires Xdebug)
docker compose exec app php artisan test --coverage
```

### Test Coverage

- **Unit Tests**: Weight calculations, metric/imperial conversions, model relationships
- **Feature Tests**: KISS import transactions, inventory movements, protected report access

---

## Common Commands

### Docker Operations

```bash
# Start containers
docker compose up -d

# Stop containers
docker compose down

# Restart all containers
docker compose restart

# Restart specific container
docker compose restart app

# View logs (all containers)
docker compose logs -f

# View logs (specific container)
docker compose logs -f app

# Access application shell
docker compose exec app bash

# Check container status
docker compose ps
```

### Laravel Artisan Commands

```bash
# Run inside container: docker compose exec app <command>

# Migrations
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Reset database with seeds
php artisan migrate:status       # Check migration status

# Cache Management
php artisan config:clear         # Clear config cache
php artisan cache:clear          # Clear application cache
php artisan view:clear           # Clear compiled views
php artisan route:clear          # Clear route cache
php artisan optimize:clear       # Clear all caches

# Development Tools
php artisan tinker               # Interactive REPL
php artisan route:list           # List all routes
php artisan queue:work           # Process queue jobs
```

### Frontend Development

```bash
# Development server with hot reload
npm run dev

# Production build
npm run build

# Lint JavaScript/Vue files
npm run lint
```

---

## Troubleshooting

### MySQL Connection Refused

If you see "Connection refused" errors:

```bash
# Wait for MySQL to fully initialize
docker compose logs -f mysql

# Check if MySQL is accepting connections
docker compose exec mysql mysqladmin ping -h localhost

# Restart the database container
docker compose restart mysql
```

### Permission Errors

If you see storage or cache permission errors:

```bash
# Fix permissions inside container
docker compose exec app chmod -R 775 storage bootstrap/cache

# Fix ownership (if needed)
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Container Won't Start

```bash
# View container logs
docker compose logs app

# Rebuild without cache
docker compose build --no-cache

# Remove all containers and volumes (WARNING: deletes data)
docker compose down -v
docker compose up -d --build
```

### Frontend Assets Not Loading

```bash
# Rebuild frontend assets
npm run build

# Or inside container
docker compose exec app npm run build

# Clear view cache
docker compose exec app php artisan view:clear
```

### Database Reset

To completely reset the database:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

---

## Production Deployment

For production deployments, consider:

1. **Environment Variables**:
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Use strong passwords for DB and Redis

2. **Caching**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Queue Worker**: Configure a process manager (Supervisor) for queue workers

4. **SSL/HTTPS**: Configure SSL certificates in Nginx

5. **Backups**: Set up automated database backups

---

*For additional help, see the main [README.md](../README.md) or open an issue on GitHub.*
