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
| [Node.js](https://nodejs.org/) | 20.x LTS | Frontend asset compilation (optional - runs in container) |
| [PHP](https://www.php.net/) | 8.4+ | Local Composer/Artisan commands (optional - runs in container) |

**Note:** Node.js and PHP are optional for local development since all commands can be run inside the Docker containers.

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
3. Ensure Laravel directory structure exists
4. **Generate self-signed SSL certificates for HTTPS**
5. Build and start all Docker containers
6. Wait for containers to be healthy
7. Install PHP dependencies via Composer (inside container)
8. Install Node.js dependencies and build assets (inside container)
9. Generate application key
10. Run database migrations and seed initial data
11. Set proper file permissions

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

# 8. Install frontend dependencies and build assets (inside container)
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
| Search Engine | `steelflow-meilisearch` | 7700 | http://localhost:7700 |
| phpMyAdmin | `steelflow-phpmyadmin` | 8080 | http://localhost:8080 |

### Accessing Services

- **Application (HTTP)**: http://localhost
- **Application (HTTPS)**: https://localhost
- **Meilisearch**: http://localhost:7700 (Search dashboard)
- **phpMyAdmin**: http://localhost:8080
  - Server: `mysql`
  - Username: `steelflow` (or `root`)
  - Password: `secret` (default from .env)

### Accessing from Other Devices

The application is configured to accept connections on **any IP address**, making it accessible from:

- `https://localhost` (local machine)
- `https://192.168.x.x` (your LAN IP - find with `ip addr` or `ifconfig`)
- Any other network interface on your machine

**Note:** Browsers will show a security warning for self-signed certificates. This is expected and safe for development. Click "Advanced" → "Proceed" to continue.

---

## Default Login Credentials

After seeding the database, you can log in with:

| Field | Value |
|-------|-------|
| Email | `admin@steelflow.local` |
| Password | `password` |

---

## PHP Configuration

SteelFlow MRP includes optimized PHP settings in `docker/php.ini`:

- **Memory Limit**: 512M (for large BOM imports and nesting operations)
- **Upload Size**: 100M max file uploads
- **Execution Time**: 120s timeout
- **OPcache**: Enabled for production performance with 256M cache

These settings are automatically applied when the Docker container builds.

---

## SSL/HTTPS Configuration

SteelFlow MRP includes **automatic SSL/HTTPS setup** with self-signed certificates that work on any IP address.

### Automatic SSL Setup

SSL certificates are automatically generated during installation and include:

- `localhost` and wildcard `*.localhost`
- `steelflow.local` and wildcard `*.steelflow.local`
- IPv4 loopback: `127.0.0.1`
- IPv6 loopback: `::1`
- All detected LAN IP addresses (e.g., `192.168.x.x`, `10.x.x.x`)

The certificates use **Subject Alternative Names (SAN)** to support multiple IPs and hostnames, ensuring the application works on any network interface without being bound to a specific IP.

### Manual SSL Certificate Regeneration

Regenerate certificates if your IP addresses change:

```bash
chmod +x scripts/generate-ssl-certs.sh
./scripts/generate-ssl-certs.sh
docker compose restart web
```

### Using Trusted Certificates (Optional)

For a better development experience without browser warnings, use [mkcert](https://github.com/FiloSottile/mkcert):

```bash
# Install mkcert (example for macOS)
brew install mkcert
mkcert -install

# Generate trusted certificate
cd docker/certs
mkcert -cert-file localhost.pem -key-file localhost-key.pem \
  localhost 127.0.0.1 ::1 YOUR_LAN_IP

# Restart web server
docker compose restart web
```

### Forcing HTTPS

To redirect all HTTP traffic to HTTPS, uncomment this line in `docker/nginx.conf`:

```nginx
# return 301 https://$host$request_uri;
```

Then restart the web container:

```bash
docker compose restart web
```

For detailed SSL configuration options, see [`docker/certs/README.md`](../docker/certs/README.md).

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

# Vite HMR (Optional)
# When testing on a phone, set this to your machine's LAN IP (e.g. 192.168.1.25).
# Leave blank to let Vite use the current hostname.
VITE_HMR_HOST=
```

### Vite HMR on Mobile Devices

If you are testing the frontend on a phone or tablet, set `VITE_HMR_HOST` in your `.env` to your
machine's LAN IP (for example, `192.168.1.25`). Vite already binds to `0.0.0.0` and Docker exposes
port `5173`, so the dev server remains reachable on your local network.

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

For production deployments, follow these best practices:

### 1. Environment Variables

Update `.env` for production:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use strong, random passwords
DB_PASSWORD=<strong-random-password>
REDIS_PASSWORD=<strong-random-password>
```

### 2. SSL/HTTPS Configuration

**Important:** Self-signed certificates are for development only!

For production, use certificates from a trusted Certificate Authority:

#### Option A: Let's Encrypt (Free, Automated)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Generate certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is configured automatically
```

Update `docker/nginx.conf` with Let's Encrypt certificate paths:

```nginx
ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
```

#### Option B: Commercial Certificate

1. Purchase SSL certificate from a trusted CA
2. Place certificate files in `docker/certs/`
3. Update `docker/nginx.conf` with certificate paths
4. Force HTTPS by uncommenting the redirect line in nginx config

### 3. Performance Optimization

Cache Laravel configuration for better performance:

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan event:cache
```

### 4. Queue Workers

Configure a process manager (Supervisor) for queue workers:

```ini
[program:steelflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=docker compose exec app php artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/storage/logs/worker.log
```

### 5. Database Backups

Set up automated database backups:

```bash
# Example daily backup script
#!/bin/bash
docker compose exec mysql mysqldump -u steelflow -p steelflow > backup-$(date +%Y%m%d).sql
```

### 6. Security Hardening

- Enable firewall (UFW, iptables)
- Restrict database access to localhost only
- Use environment-specific `.env` files
- Enable rate limiting in nginx
- Keep Docker images and dependencies updated

### 7. Monitoring

Consider implementing:
- Application monitoring (e.g., Laravel Telescope)
- Server monitoring (e.g., Prometheus, Grafana)
- Error tracking (e.g., Sentry)
- Uptime monitoring

---

*For additional help, see the main [README.md](../README.md) or open an issue on GitHub.*
