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

# Function to create required Laravel directories
function ensure_laravel_directories() {
    status "Ensuring Laravel directory structure exists..."

    # Create storage directories
    mkdir -p storage/{app/{public,private},framework/{cache/data,sessions,testing,views},logs}

    # Create bootstrap cache directory
    mkdir -p bootstrap/cache

    # Add .gitkeep files to track empty directories
    touch storage/logs/.gitkeep
    touch bootstrap/cache/.gitkeep

    success "Laravel directories ready"
}

# Function to wait for a container to be healthy
function wait_for_container() {
    local container_name=$1
    local max_attempts=30
    local attempt=0

    status "Waiting for $container_name to be healthy..."

    while [ $attempt -lt $max_attempts ]; do
        if docker inspect --format='{{.State.Health.Status}}' "$container_name" 2>/dev/null | grep -q "healthy"; then
            success "$container_name is healthy"
            # Add stabilization delay to ensure container is ready for exec commands
            status "Waiting for container to stabilize..."
            sleep 5
            success "$container_name is ready"
            return 0
        fi

        # Check if container is running (for containers without health check)
        if docker inspect --format='{{.State.Running}}' "$container_name" 2>/dev/null | grep -q "true"; then
            # If no health check defined, check if it's been running for at least 5 seconds
            local running_time=$(docker inspect --format='{{.State.StartedAt}}' "$container_name" 2>/dev/null)
            if [ -n "$running_time" ]; then
                success "$container_name is running"
                # Add stabilization delay for containers without health check too
                status "Waiting for container to stabilize..."
                sleep 5
                success "$container_name is ready"
                return 0
            fi
        fi

        echo -n "."
        sleep 2
        ((attempt++))
    done

    warning "$container_name did not become healthy in time"
    docker logs "$container_name" --tail 50
    return 1
}

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker is not running. Please start Docker Desktop first."
    exit 1
fi

# Step 0: Ensure Laravel directory structure exists
ensure_laravel_directories

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

# Step 5: Wait for app container to be healthy
wait_for_container "steelflow-app"

# Step 6: Wait for MySQL to be ready
status "Waiting for MySQL to be ready..."
until docker compose exec mysql mysqladmin ping -h"localhost" --silent; do
    echo -n "."
    sleep 2
done
echo ""
success "MySQL is ready"

# Step 7: Update PHP dependencies
status "Updating PHP dependencies..."
docker compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader
success "PHP dependencies updated"

# Step 8: Update frontend dependencies
if [ -f "package.json" ]; then
    status "Updating frontend dependencies..."
    npm install
    success "Frontend dependencies updated"
else
    warning "No package.json found - skipping npm install"
fi

# Step 9: Clear Laravel caches
status "Clearing application caches..."
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
success "Caches cleared"

# Step 10: Generate application key if not set
status "Ensuring application key is set..."
if ! docker compose exec app grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    docker compose exec app php artisan key:generate --force
    success "Application key generated"
else
    echo "   Application key already set"
fi

# Step 11: Run database migrations
status "Running database migrations..."
docker compose exec app php artisan migrate --force
success "Database migrations completed"

# Step 12: Seed database (optional - only if empty)
status "Checking if database needs seeding..."
USER_COUNT=$(docker compose exec mysql mysql -u${DB_USERNAME:-steelflow} -p${DB_PASSWORD:-secret} ${DB_DATABASE:-steelflow} -sNe "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")

if [ "$USER_COUNT" -eq "0" ]; then
    status "Database is empty - seeding with initial data..."
    docker compose exec app php artisan db:seed --force
    success "Database seeded with sample data"
else
    echo "   Database already contains data - skipping seed"
fi

# Step 13: Optimize application
status "Optimizing application..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
success "Application optimized"

# Step 14: Build frontend assets
if [ -f "package.json" ]; then
    status "Building frontend assets..."
    npm run build
    success "Frontend assets built"
fi

# Step 15: Set proper permissions
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
