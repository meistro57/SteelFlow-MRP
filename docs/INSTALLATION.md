# Setup & Installation (WSL2 / Linux)

SteelFlow MRP is built to run in a containerized environment. This script will ensure your environment is fully prepared.

## 🚀 One-Command Install

Run the following command in your WSL2 terminal to install logic, dependencies, and launch the infrastructure:

```bash
chmod +x scripts/install.sh && ./scripts/install.sh
```

### What This Script Does:
1. **Environment Initialization**: Creates `.env` from `.env.example`.
2. **Infrastructure Launch**: Boots Docker containers for:
   - **PHP 8.3 FPM** (Application Logic)
   - **Nginx** (Web Server)
   - **MySQL 8.0** (Primary Database)
   - **Redis** (Queue & Cache)
   - **phpMyAdmin** (Database Management @ port 8080)
3. **Dependency Management**: Installs PHP (Composer) and Frontend (NPM) packages.
4. **Database Provisioning**: Generates application keys and runs all migrations.

---

## 🧪 Testing the Vision

Once installed, verify the integrity of the system by running the full test suite:

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific suites
docker compose exec app php artisan test --testsuite=Unit
docker compose exec app php artisan test --testsuite=Feature
```

### Coverage Included:
- **Unit**: Weight calculations, Metric/Imperial conversions, Model relationships.
- **Feature**: KISS Importer transactions, inventory stock movements, and protected report access.

---

## 🛠️ Manual Maintenance

- **Restart Services**: `docker compose restart`
- **View Logs**: `docker compose logs -f`
- **Database Access**: [http://localhost:8080](http://localhost:8080)

---

## 🩺 Troubleshooting

### Container Restart Loop

**Symptom**: The `steelflow-app` container repeatedly restarts and never becomes stable.

**Diagnosis**: Check the container logs:
```bash
docker logs steelflow-app --tail 100
```

**Common Causes & Solutions**:

#### 1. Missing `.env` File or Empty `APP_KEY`
The entrypoint script automatically handles this, but if you encounter issues:

```bash
# Quick fix - restart the container
docker compose restart app

# If that doesn't work, rebuild with cache cleared
docker compose down
docker compose up -d --build
```

**What the entrypoint does automatically**:
- Creates `.env` from `.env.example` if missing
- Generates a secure `APP_KEY` if not set
- Sets proper ownership (`www-data`) for all Laravel files

#### 2. Permission Issues
```bash
# Check current permissions
docker compose exec app ls -la storage/

# Fix permissions
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

#### 3. Database Not Ready
Laravel will fail if MySQL isn't fully initialized. Wait 30-60 seconds after first start:

```bash
# Check if MySQL is ready
docker compose exec mysql mysqladmin ping -h"localhost"

# If not ready, view MySQL logs
docker compose logs mysql
```

#### 4. Missing Dependencies
```bash
# Reinstall PHP dependencies
docker compose exec app composer install --no-interaction

# Check for errors
docker compose logs app
```

### View Container Status

```bash
# Check all container statuses
docker compose ps

# View real-time logs for app container
docker compose logs -f app

# Check healthcheck status
docker inspect steelflow-app --format='{{.State.Health.Status}}'
```

### Reset Everything

If all else fails, perform a complete reset:

```bash
# Stop and remove all containers, networks, and volumes
docker compose down -v

# Remove the Docker image to force a fresh build
docker rmi steelflow-app

# Start fresh
docker compose up -d --build

# Wait for containers to be healthy, then run migrations
docker compose exec app php artisan migrate --seed
```
