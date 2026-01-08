#!/bin/bash

# This script runs various test suites for the SteelFlow MRP project.
# It mimics a CI/CD environment to ensure code quality before deployment.

# Function to run backend PHPUnit tests
run_backend_tests() {
    echo "Running backend PHPUnit tests..."
    docker compose exec app php artisan test
    if [ $? -ne 0 ]; then
        echo "Backend PHPUnit tests failed!"
        return 1
    fi
    echo "Backend PHPUnit tests passed."
    return 0
}

# Function to run backend code quality checks (Lint and Analyse)
run_backend_quality_checks() {
    echo "Running backend code quality checks (Laravel Pint)..."
    docker compose exec app composer lint
    if [ $? -ne 0 ]; then
        echo "Backend linting failed!"
        return 1
    fi
    echo "Backend linting passed."

    echo "Running backend code analysis (PHPStan)..."
    docker compose exec app composer analyse
    if [ $? -ne 0 ]; then
        echo "Backend analysis failed!"
        return 1
    fi
    echo "Backend analysis passed."
    return 0
}

# Function to run frontend linting check
run_frontend_lint_check() {
    echo "Running frontend linting check (ESLint)..."
    npm run lint:check
    if [ $? -ne 0 ]; then
        echo "Frontend linting check failed! Run option 9 to auto-fix where possible."
        return 1
    fi
    echo "Frontend linting check passed."
    return 0
}

# Function to run frontend linting and auto-fix
run_frontend_lint_fix() {
    echo "Running frontend linting with auto-fix (ESLint --fix)..."
    npm run lint:fix
    if [ $? -ne 0 ]; then
        echo "Frontend linting and fixing completed with issues."
        return 1
    fi
    echo "Frontend linting and fixing completed."
    return 0
}

# Function to create a fun progress animation
show_progress_animation() {
    local message="$1"
    local duration="$2"
    local characters="⊙⚪⊙ ⚫⊙ ⁙⋊⁚⊟ ⋋⁝⋌ ⋺∘▼∘ ⊛⋗∘⋘ ⊙✮⊙"

    echo -n "$message "

    for i in {1..10}; do
        for char in $characters; do
            echo -ne "\r$message $char"
            sleep "$duration"
        done
    done

    echo -ne "\r$message ✅✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓✓\n"
}

# Function to check system health
check_system_health() {
    echo "=== SYSTEM HEALTH CHECK ==="
    local status_ok=true

    # Check if Docker is running
    if ! docker info >/dev/null 2>&1; then
        echo "❌ Docker not running"
        status_ok=false
    else
        echo "✅ Docker running"
    fi

    # Check if containers are up
    if ! docker compose ps | grep -q "app"; then
        echo "❌ Docker containers not running"
        status_ok=false
    else
        echo "✅ Docker containers running"
    fi

    # Check PHP dependencies
    if docker compose exec -T app composer install --dry-run >/dev/null 2>&1; then
        echo "❌ PHP dependencies need update"
        status_ok=false
    else
        echo "✅ PHP dependencies installed"
    fi

    # Check Node.js dependencies
    if ! npm ls --prefix ./ >/dev/null 2>&1; then
        echo "❌ Node.js dependencies not installed"
        status_ok=false
    else
        echo "✅ Node.js dependencies installed"
    fi

    # Check database connection
    if ! docker compose exec -T app php artisan --version >/dev/null 2>&1; then
        echo "❌ Laravel/artisan not accessible"
        status_ok=false
    else
        echo "✅ Laravel accessible"
    fi

    # Check if migrations are run
    if ! docker compose exec -T db mysql -u steelflow -psteelflow_mrp steelflow -e "SHOW TABLES;" >/dev/null 2>&1; then
        echo "❌ MySQL database not accessible"
        status_ok=false
    else
        echo "✅ MySQL database accessible"
    fi

    echo "=== END HEALTH CHECK ==="
    $status_ok && return 0 || return 1
}

# Function to repair common issues
repair_system() {
    echo "=== REPAIRING SYSTEM ISSUES ==="

    # Start containers if not running
    if ! docker compose ps | grep -q "app"; then
        echo "Starting Docker containers..."
        docker compose up -d
        sleep 10
        if ! docker compose ps | grep -q "app"; then
            echo "❌ Failed to start containers"
            return 1
        fi
        echo "✅ Containers started"
    fi

    # Install PHP dependencies
    echo "Installing/updating PHP dependencies..."
    docker compose exec app composer install --optimize-autoloader
    if [ $? -ne 0 ]; then
        echo "❌ Failed to install PHP dependencies"
        return 1
    fi
    echo "✅ PHP dependencies installed"

    # Run migrations
    echo "Running database migrations..."
    docker compose exec app php artisan migrate
    if [ $? -ne 0 ]; then
        echo "❌ Failed to run migrations or migrations already up-to-date"
        return 1
    fi
    echo "✅ Migrations completed or skipped"

    # Install Node.js dependencies
    echo "Installing/updating Node.js dependencies..."
    npm install --production=false
    if [ $? -ne 0 ]; then
        echo "❌ Failed to install Node.js dependencies"
        return 1
    fi
    echo "✅ Node.js dependencies installed"

    # Clear caches
    echo "Clearing application caches..."
    docker compose exec app php artisan cache:clear
    docker compose exec app php artisan config:clear
    docker compose exec app php artisan route:clear
    docker compose exec app php artisan view:clear
    echo "✅ Caches cleared"

    echo "=== REPAIR COMPLETE ==="
    return 0
}

# Function to run full system diagnostics
run_full_diagnostics() {
    echo "=== RUNNING FULL SYSTEM DIAGNOSTICS ==="

    echo "--- Health Check ---"
    check_system_health
    local health_status=$?

    if [ $health_status -ne 0 ]; then
        echo "Health issues detected. Running repairs..."
        repair_system
        if [ $? -ne 0 ]; then
            echo "❌ Repairs failed. Cannot proceed with diagnostics."
            return 1
        fi
        echo "Re-running health check after repairs..."
        check_system_health
        if [ $? -ne 0 ]; then
            echo "❌ System still unhealthy after repairs."
            return 1
        fi
    fi

    echo "--- Backend Tests ---"
    run_backend_tests
    local backend_status=$?

    echo "--- Backend Quality Checks ---"
    run_backend_quality_checks
    local quality_status=$?

    echo "--- Frontend Linting ---"
    run_frontend_lint_check
    local frontend_status=$?

    local all_passed=$((backend_status || quality_status || frontend_status))

    echo "=== DIAGNOSTICS SUMMARY ==="
    echo "Health Check: $(test $health_status -eq 0 && echo '✅ PASS' || echo '❌ FAIL')"
    echo "Backend Tests: $(test $backend_status -eq 0 && echo '✅ PASS' || echo '❌ FAIL')"
    echo "Quality Checks: $(test $quality_status -eq 0 && echo '✅ PASS' || echo '❌ FAIL')"
    echo "Frontend Lint: $(test $frontend_status -eq 0 && echo '✅ PASS' || echo '❌ FAIL')"

    if [ $all_passed -eq 0 ]; then
        echo "🎉 All tests passed!"
        return 0
    else
        echo "❌ Some tests failed."
        return 1
    fi
}

# Main menu function
main_menu() {
    while true; do
        echo -e "
--- SteelFlow Test Runner ---"
        echo "1. Run Full System Diagnostics (Health Check + All Tests)"
        echo "2. Check System Health"
        echo "3. Repair System Issues (Dependencies, Migrations, etc.)"
        echo "4. Run All Tests (Backend PHPUnit, Backend Quality, Frontend Lint)"
        echo "5. Run Backend PHPUnit Tests"
        echo "6. Run Backend Code Quality Checks"
        echo "7. Run Frontend Linting"
        echo "8. Exit"
        echo "---------------------------"
        read -p "Choose an option: " choice
        echo ""

        case $choice in
            1)
                run_full_diagnostics
                ;;
            2)
                check_system_health
                ;;
            3)
                repair_system
                ;;
            4)
                echo "Running all test suites..."
                ALL_PASSED=0
                run_backend_tests || ALL_PASSED=1
                run_backend_quality_checks || ALL_PASSED=1
                run_frontend_lint_check || ALL_PASSED=1

                if [ $ALL_PASSED -eq 0 ]; then
                    echo -e "
All tests completed successfully!"
                else
                    echo -e "
Some tests failed."
                fi
                ;;
            5)
                run_backend_tests
                ;;
            6)
                run_backend_quality_checks
                ;;
            7)
                run_frontend_lint_check
                ;;
            8)
                run_frontend_lint_fix
                ;;
            9)
                echo "Updating PHP dependencies..."
                update_php_dependencies
                ;;
            10)
                echo "Exiting Test Runner."
                exit 0
                ;;
            *)
                echo "Invalid option. Please try again."
                ;;
        esac
    done
}

# Ensure Docker containers are up
echo "Checking Docker containers..."
if ! docker compose ps | grep -q "app"; then
    echo "Docker containers not running. Starting them now..."
    docker compose up -d
    sleep 5 # Give some time for services to start
    if ! docker compose ps | grep -q "app"; then
        echo "Failed to start Docker containers. Exiting."
        exit 1
    fi
    echo "Docker containers started."
fi

# Make sure npm dependencies are installed for frontend linting
echo "Ensuring frontend dependencies are installed..."
if ! npm ls --prefix ./ 2>/dev/null >/dev/null; then
    echo "Node modules not found. Running 'npm install'..."
    npm install
fi

main_menu
