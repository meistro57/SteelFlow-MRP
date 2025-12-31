#!/bin/bash

# SteelFlow MRP Installation Script for WSL2
set -e

echo "🏗️  Starting SteelFlow MRP Installation..."

# 1. Check for Docker
if ! [ -x "$(command -v docker)" ]; then
  echo '❌ Error: docker is not installed. Please install Docker Desktop for Windows and enable WSL2 integration.' >&2
  exit 1
fi

# 2. Environment file
if [ ! -f .env ]; then
    echo "📄 Creating .env from .env.example..."
    cp .env.example .env
    echo "⚠️  Please update AZURE_CLIENT_ID in .env after installation."
fi

# 3. Launch Docker containers
echo "🐳 Launching Docker containers..."
docker compose up -d --build

# 4. Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to initialize..."
until docker compose exec mysql mysqladmin ping -h"localhost" --silent; do
    sleep 2
done

# 5. Install PHP Dependencies
echo "📦 Installing PHP dependencies..."
docker compose exec app composer install

# 6. Install Frontend Dependencies
if [ -x "$(command -v npm)" ]; then
    echo "🎨 Installing Frontend dependencies..."
    npm install
    npm run build
else
    echo "⚠️  npm not found locally. Skipping frontend build. Run npm manually inside WSL2."
fi

# 7. Application Setup
echo "🔑 Finalizing application configuration..."
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force --seed

# 8. Success
echo "✅ SteelFlow MRP is now installed!"
echo "🌐 App URL: http://localhost"
echo "🔧 phpMyAdmin: http://localhost:8080 (User: root / Pass: ${DB_PASSWORD:-secret})"
echo "🧪 To run tests: docker compose exec app php artisan test"
