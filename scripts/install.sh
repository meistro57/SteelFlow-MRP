#!/bin/bash

# SteelFlow MRP Installation Script
set -e

echo "🏗️  SteelFlow MRP Installation Script"
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

# Step 1: Environment file
if [ ! -f .env ]; then
    status "Creating .env from .env.example..."
    cp .env.example .env
    success ".env file created"
    warning "Please update AZURE_CLIENT_ID in .env after installation."
else
    echo "   .env file already exists - skipping"
fi

# Step 2: Ensure Laravel directory structure exists
status "Ensuring Laravel directory structure exists..."
mkdir -p storage/{app/{public,private},framework/{cache/data,sessions,testing,views},logs}
mkdir -p bootstrap/cache
[ -f storage/logs/.gitkeep ] || touch storage/logs/.gitkeep 2>/dev/null || true
[ -f bootstrap/cache/.gitkeep ] || touch bootstrap/cache/.gitkeep 2>/dev/null || true
success "Laravel directories ready"

# Step 2.5: Generate SSL certificates
status "Generating self-signed SSL certificates..."
if [ -f "scripts/generate-ssl-certs.sh" ]; then
    chmod +x scripts/generate-ssl-certs.sh
    ./scripts/generate-ssl-certs.sh
    success "SSL certificates generated"
else
    warning "SSL certificate generation script not found - skipping"
fi

# Step 3: Build and launch Docker containers
status "Building and launching Docker containers..."
docker compose up -d --build
success "Containers started"

# Step 4: Wait for app container to be healthy
wait_for_container "steelflow-app"

# Step 5: Wait for MySQL to be ready
status "Waiting for MySQL to be ready..."
until docker compose exec mysql mysqladmin ping -h"localhost" --silent; do
    echo -n "."
    sleep 2
done
echo ""
success "MySQL is ready"

# Step 6: Install PHP Dependencies
status "Installing PHP dependencies..."
docker compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader
success "PHP dependencies installed"

# Step 7: Install Frontend Dependencies inside the app container
if [ -f "package.json" ]; then
    status "Installing frontend dependencies inside container..."
    if docker compose exec app command -v npm > /dev/null 2>&1; then
        docker compose exec app npm install
        success "Frontend dependencies installed"
    else
        warning "npm is unavailable in the app container - check the Docker build"
    fi
else
    warning "No package.json found - skipping npm install"
fi

# Step 8: Generate application key
status "Generating application key..."
docker compose exec app php artisan key:generate --force
success "Application key generated"

# Step 9: Run database migrations and seed
status "Running database migrations..."
docker compose exec app php artisan migrate --force
success "Database migrations completed"

status "Seeding database with initial data..."
docker compose exec app php artisan db:seed --force
success "Database seeded"

# Step 10: Build frontend assets inside the container
if [ -f "package.json" ]; then
    status "Building frontend assets inside container..."
    if docker compose exec app command -v npm > /dev/null 2>&1; then
        docker compose exec app npm run build
        success "Frontend assets built"
    else
        warning "npm is unavailable in the app container - skipping asset build"
    fi
fi

# Step 11: Set proper permissions
status "Setting proper permissions..."
docker compose exec app chmod -R 775 storage bootstrap/cache
success "Permissions set"

# Final status check
echo ""
echo "=========================================="
status "Checking container status..."
docker compose ps

echo ""
echo "✨ Installation Complete! ✨"
echo ""
echo "📊 Application URLs:"
echo "   🌐 SteelFlow MRP:  http://localhost"
echo "   🔒 SteelFlow MRP (HTTPS): https://localhost"
echo "   🔧 phpMyAdmin:     http://localhost:8080"
echo "   🔍 Meilisearch:    http://localhost:7700"
echo ""
echo "🔐 Default Login Credentials:"
echo "   📧 Email:    admin@steelflow.local"
echo "   🔑 Password: password"
echo ""
echo "🌐 Network Access:"
echo "   The application accepts connections on ANY IP address."
echo "   Access via LAN: https://YOUR_IP_ADDRESS"
echo "   (Browser will show SSL warning for self-signed cert - this is normal)"
echo ""
echo "🛠️  Useful Commands:"
echo "   • View logs:        docker compose logs -f"
echo "   • Restart app:      docker compose restart app"
echo "   • Stop all:         docker compose down"
echo "   • Run tests:        docker compose exec app php artisan test"
echo "   • Access shell:     docker compose exec app bash"
echo "   • Update app:       ./update.sh"
echo ""
success "SteelFlow MRP is ready! 🚀"
