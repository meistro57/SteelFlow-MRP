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
docker-compose exec app php artisan test

# Run specific suites
docker-compose exec app php artisan test --testsuite=Unit
docker-compose exec app php artisan test --testsuite=Feature
```

### Coverage Included:
- **Unit**: Weight calculations, Metric/Imperial conversions, Model relationships.
- **Feature**: KISS Importer transactions, inventory stock movements, and protected report access.

---

## 🛠️ Manual Maintenance

- **Restart Services**: `docker-compose restart`
- **View Logs**: `docker-compose logs -f`
- **Database Access**: [http://localhost:8080](http://localhost:8080)
