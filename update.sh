#!/bin/bash

# SteelFlow MRP Update Script
# Handles all Docker operations, updates dependencies, and runs the application
set -e

echo "🏗️  SteelFlow MRP Update & Deploy Script"
echo "=========================================="
echo ""

# Function to display fancy status messages
function status() {
    echo "▶️  $1"
}

function success() {
    echo "✅ $1"
}

function warning() {
    echo "⚠️  $1"
}

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running. Please start Docker Desktop first."
    exit 1
fi

# Step 1: Pull latest code (if in a git repo)
if [ -d .git ]; then
    status "Pulling latest code from repository..."
    git pull origin $(git branch --show-current) || warning "Git pull failed or no changes available"
    success "Code updated"
else
    warning "Not a git repository - skipping git pull"
fi

# Step 2: Stop existing containers
status "Stopping existing containers..."
docker compose down
success "Containers stopped"

# Step 3: Build/rebuild Docker images
status "Building Docker images..."
docker compose build --no-cache
success "Docker images built"

# Step 4: Start containers
status "Starting Docker containers..."
docker compose up -d
success "Containers started"

# Step 5: Wait for MySQL to be ready
status "Waiting for MySQL to be ready..."
until docker compose exec mysql mysqladmin ping -h"localhost" --silent; do
    echo -n "."
    sleep 2
done
echo ""
success "MySQL is ready"

# Step 6: Update PHP dependencies
status "Updating PHP dependencies..."
docker compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader
success "PHP dependencies updated"

# Step 7: Update frontend dependencies
if [ -f "package.json" ]; then
    status "Updating frontend dependencies..."
    npm install
    success "Frontend dependencies updated"
else
    warning "No package.json found - skipping npm install"
fi

# Step 8: Clear Laravel caches
status "Clearing application caches..."
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
success "Caches cleared"

# Step 9: Generate application key if not set
status "Ensuring application key is set..."
if ! docker compose exec app grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    docker compose exec app php artisan key:generate --force
    success "Application key generated"
else
    echo "   Application key already set"
fi

# Step 10: Run database migrations
status "Running database migrations..."
docker compose exec app php artisan migrate --force
success "Database migrations completed"

# Step 11: Seed database (optional - only if empty)
status "Checking if database needs seeding..."
USER_COUNT=$(docker compose exec mysql mysql -u${DB_USERNAME:-steelflow} -p${DB_PASSWORD:-secret} ${DB_DATABASE:-steelflow} -sNe "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")

if [ "$USER_COUNT" -eq "0" ]; then
    status "Database is empty - seeding with initial data..."
    docker compose exec app php artisan db:seed --force
    success "Database seeded with sample data"
else
    echo "   Database already contains data - skipping seed"
fi

# Step 12: Optimize application
status "Optimizing application..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
success "Application optimized"

# Step 13: Build frontend assets
if [ -f "package.json" ]; then
    status "Building frontend assets..."
    npm run build
    success "Frontend assets built"
fi

# Step 14: Set proper permissions
status "Setting proper permissions..."
docker compose exec app chmod -R 775 storage bootstrap/cache
success "Permissions set"

# Final status check
echo ""
echo "=========================================="
status "Checking container status..."
docker compose ps

echo ""
echo "✨ Update Complete! ✨"
echo ""
echo "📊 Application URLs:"
echo "   🌐 SteelFlow MRP:  http://localhost"
echo "   🔧 phpMyAdmin:     http://localhost:8080"
echo ""
echo "🔐 Default Login Credentials:"
echo "   📧 Email:    admin@steelflow.local"
echo "   🔑 Password: password"
echo ""
echo "🛠️  Useful Commands:"
echo "   • View logs:        docker compose logs -f"
echo "   • Restart app:      docker compose restart app"
echo "   • Stop all:         docker compose down"
echo "   • Run tests:        docker compose exec app php artisan test"
echo "   • Access shell:     docker compose exec app bash"
echo ""
success "SteelFlow MRP is ready! 🚀"
