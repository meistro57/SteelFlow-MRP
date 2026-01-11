#!/bin/bash

# SteelFlow MRP Test Runner & Maintenance Script
# This script handles testing, code quality, and environment maintenance.

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
WHITE='\033[1;37m'
ORANGE='\033[38;5;208m'
BROWN='\033[38;5;94m'
NC='\033[0m' # No Color

# Function to run backend PHPUnit tests
run_backend_tests() {
    echo -e "${BLUE}Running backend PHPUnit tests...${NC}"
    docker compose exec app php artisan test
    local status=$?
    if [ $status -ne 0 ]; then
        echo -e "${RED}Backend PHPUnit tests failed!${NC}"
        return $status
    fi
    echo -e "${GREEN}Backend PHPUnit tests passed.${NC}"
    return 0
}

# Function to run backend code quality checks
run_backend_quality_checks() {
    echo -e "${BLUE}Running backend code quality checks (Laravel Pint)...${NC}"
    docker compose exec app composer lint
    local lint_status=$?
    if [ $lint_status -ne 0 ]; then
        echo -e "${RED}Backend linting failed!${NC}"
    else
        echo -e "${GREEN}Backend linting passed.${NC}"
    fi

    echo -e "${BLUE}Running backend code analysis (PHPStan)...${NC}"
    docker compose exec app composer analyse
    local analyse_status=$?
    if [ $analyse_status -ne 0 ]; then
        echo -e "${RED}Backend analysis failed!${NC}"
    else
        echo -e "${GREEN}Backend analysis passed.${NC}"
    fi
    
    return $((lint_status + analyse_status))
}

# Function to run frontend linting check
run_frontend_lint_check() {
    echo -e "${BLUE}Running frontend linting check (ESLint)...${NC}"
    npm run lint:check
    local status=$?
    if [ $status -ne 0 ]; then
        echo -e "${RED}Frontend linting check failed! Run with 'fix' command or option 9 to auto-fix.${NC}"
        return $status
    fi
    echo -e "${GREEN}Frontend linting check passed.${NC}"
    return 0
}

# Function to run frontend linting and auto-fix
run_frontend_lint_fix() {
    echo -e "${BLUE}Running frontend linting with auto-fix (ESLint --fix)...${NC}"
    npm run lint:fix
    local status=$?
    if [ $status -ne 0 ]; then
        echo -e "${YELLOW}Frontend linting and fixing completed with issues.${NC}"
        return $status
    fi
    echo -e "${GREEN}Frontend linting and fixing completed.${NC}"
    return 0
}

# Function to check system health
check_system_health() {
    echo -e "${BLUE}=== SYSTEM HEALTH CHECK ===${NC}"
    local status_ok=true

    if [ -f ".env" ]; then
        echo -e "${GREEN}✅ .env file exists${NC}"
    else
        echo -e "${RED}❌ .env file missing${NC}"
        status_ok=false
    fi

    if ! docker info >/dev/null 2>&1; then
        echo -e "${RED}❌ Docker not running${NC}"
        status_ok=false
    else
        echo -e "${GREEN}✅ Docker running${NC}"
    fi

    if ! docker compose ps | grep -q "app"; then
        echo -e "${RED}❌ Docker containers not running${NC}"
        status_ok=false
    else
        echo -e "${GREEN}✅ Docker containers running${NC}"
    fi

    if docker compose exec -T app composer install --dry-run 2>&1 | grep -E -q "Nothing to (modify|install, update or remove)"; then
        echo -e "${GREEN}✅ PHP dependencies up to date${NC}"
    else
        echo -e "${YELLOW}⚠️ PHP dependencies may need update${NC}"
        # status_ok=false # Don't fail health check just for this
    fi

    if [ -d "node_modules" ]; then
        echo -e "${GREEN}✅ Node.js modules directory exists${NC}"
    else
        echo -e "${RED}❌ Node.js dependencies not installed${NC}"
        status_ok=false
    fi

    if ! docker compose exec -T app php artisan --version >/dev/null 2>&1; then
        echo -e "${RED}❌ Laravel/artisan not accessible${NC}"
        status_ok=false
    else
        echo -e "${GREEN}✅ Laravel accessible${NC}"
    fi

    echo -e "${BLUE}=== END HEALTH CHECK ===${NC}"
    $status_ok && return 0 || return 1
}

# Function to repair common issues
repair_system() {
    echo -e "${BLUE}=== REPAIRING SYSTEM ISSUES ===${NC}"

    # 1. Environment File
    if [ ! -f ".env" ]; then
        echo -e "${YELLOW}Creating .env from .env.example...${NC}"
        cp .env.example .env
    fi

    # 2. Docker Containers
    if ! docker compose ps | grep -q "app"; then
        echo -e "${YELLOW}Starting Docker containers...${NC}"
        docker compose up -d
        sleep 8
    fi

    # 3. PHP Dependencies
    echo -e "${YELLOW}Updating PHP dependencies...${NC}"
    docker compose exec app composer install

    # 4. App Key
    if ! grep -q "APP_KEY=base64" .env; then
        echo -e "${YELLOW}Generating application key...${NC}"
        docker compose exec app php artisan key:generate
    fi

    # 5. Storage Link
    echo -e "${YELLOW}Ensuring storage link exists...${NC}"
    docker compose exec app php artisan storage:link --quiet

    # 6. Database Migrations
    echo -e "${YELLOW}Running database migrations...${NC}"
    docker compose exec app php artisan migrate --force

    # 7. Node Dependencies
    echo -e "${YELLOW}Updating Node.js dependencies...${NC}"
    npm install

    # 8. Optimized Caches
    echo -e "${YELLOW}Clearing and refreshing application caches...${NC}"
    docker compose exec app php artisan cache:clear
    docker compose exec app php artisan config:clear
    docker compose exec app php artisan route:clear
    docker compose exec app php artisan view:clear

    echo -e "${GREEN}=== REPAIR COMPLETE ===${NC}"
    return 0
}

# Function to rebuild docker environment
rebuild_docker() {
    echo -e "${YELLOW}=== REBUILDING DOCKER ENVIRONMENT ===${NC}"
    echo "This will stop containers, rebuild images without cache, and restart."
    docker compose down
    docker compose build --no-cache
    docker compose up -d
    echo -e "${GREEN}=== DOCKER REBUILD COMPLETE ===${NC}"
    repair_system
}

# Function to reset database
reset_database() {
    echo -e "${RED}=== RESETTING DATABASE (DESTRUCTIVE) ===${NC}"
    echo "This will wipe all data and re-run migrations with seeders."
    read -p "Are you sure you want to proceed? (y/N) " confirm
    if [[ $confirm == [yY] || $confirm == [yY][eE][sS] ]]; then
        docker compose exec app php artisan migrate:fresh --seed
        echo -e "${GREEN}Database reset complete.${NC}"
    else
        echo "Reset cancelled."
    fi
}

# Function to run full system diagnostics
run_full_diagnostics() {
    echo -e "${BLUE}=== RUNNING FULL SYSTEM DIAGNOSTICS ===${NC}"
    
    check_system_health || {
        echo -e "${YELLOW}Health check failed. Attempting repair...${NC}"
        repair_system
    }
    
    run_backend_tests
    local backend_status=$?
    
    run_backend_quality_checks
    local quality_status=$?
    
    run_frontend_lint_check
    local frontend_status=$?

    echo -e "\n${BLUE}=== DIAGNOSTICS SUMMARY ===${NC}"
    echo -e "Backend Tests: $([ $backend_status -eq 0 ] && echo -e "${GREEN}PASS${NC}" || echo -e "${RED}FAIL${NC}")"
    echo -e "Quality Checks: $([ $quality_status -eq 0 ] && echo -e "${GREEN}PASS${NC}" || echo -e "${RED}FAIL${NC}")"
    echo -e "Frontend Lint: $([ $frontend_status -eq 0 ] && echo -e "${GREEN}PASS${NC}" || echo -e "${RED}FAIL${NC}")"

    local total_fail=$((backend_status + quality_status + frontend_status))
    if [ $total_fail -eq 0 ]; then
        echo -e "\n${GREEN}🎉 All systems operational and tests passed!${NC}"
        return 0
    else
        echo -e "\n${RED}❌ System has issues. See details above.${NC}"
        return 1
    fi
}

# Function to run tests with automatic repair
run_tests_with_autorepair() {
    echo -e "${BLUE}=== RUNNING TESTS WITH AUTO-REPAIR ===${NC}"
    
    local failed=false
    
    run_backend_tests || {
        echo -e "${YELLOW}Backend tests failed. Attempting repair...${NC}"
        repair_system
        echo -e "${BLUE}Retrying backend tests after repair...${NC}"
        run_backend_tests || failed=true
    }
    
    if [ "$failed" = false ]; then
        run_backend_quality_checks || failed=true
    fi
    
    if [ "$failed" = false ]; then
        run_frontend_lint_check || failed=true
    fi

    if [ "$failed" = true ]; then
        echo -e "\n${RED}❌ Tests still failing after repair attempt.${NC}"
        return 1
    else
        echo -e "\n${GREEN}🎉 All tests passed (possibly after repairs)!${NC}"
        return 0
    fi
}

# Function to tail logs
show_logs() {
    echo -e "${BLUE}Tailing application logs (press Ctrl+C to stop)...${NC}"
    docker compose logs -f app
}

# Function to run artisan tinker
run_tinker() {
    echo -e "${BLUE}Opening Laravel Tinker...${NC}"
    docker compose exec app php artisan tinker
}

# Function to re-seed database only
seed_database() {
    echo -e "${YELLOW}=== RE-SEEDING DATABASE ===${NC}"
    docker compose exec app php artisan db:seed
    echo -e "${GREEN}Database seeding complete.${NC}"
}

# Function to generate IDE helpers
generate_ide_helpers() {
    echo -e "${BLUE}Generating IDE helpers...${NC}"
    docker compose exec app php artisan ide-helper:generate
    docker compose exec app php artisan ide-helper:models --nowrite
    docker compose exec app php artisan ide-helper:meta
    echo -e "${GREEN}IDE helpers generated.${NC}"
}

# Function to run frontend build
run_frontend_build() {
    echo -e "${BLUE}Building frontend assets (npm run build)...${NC}"
    npm run build
    local status=$?
    if [ $status -ne 0 ]; then
        echo -e "${RED}Frontend build failed!${NC}"
        return $status
    fi
    echo -e "${BLUE}Publishing Filament assets...${NC}"
    docker compose exec app php artisan filament:assets
    echo -e "${GREEN}Frontend build and Filament assets published.${NC}"
    return 0
}

# Function to re-index search (Meilisearch)
reindex_search() {
    echo -e "${BLUE}Re-indexing all searchable models...${NC}"
    # Standard models that use Scout
    local models=("Project" "FabJob" "FabPart" "RawMaterial")
    for model in "${models[@]}"; do
        echo -e "${CYAN}Indexing $model...${NC}"
        docker compose exec app php artisan scout:import "App\\Models\\$model"
    done
    echo -e "${GREEN}Search indexing complete.${NC}"
}

# Function to show project statistics
show_project_stats() {
    echo -e "${BLUE}=== PROJECT STATISTICS ===${NC}"
    echo -e "${WHITE}Backend:${NC}"
    echo -ne "  Models:      " && find app/Models -name "*.php" | wc -l
    echo -ne "  Controllers: " && find app/Http/Controllers -name "*.php" | wc -l
    echo -ne "  Services:    " && find app/Services -name "*.php" | wc -l
    echo -ne "  Migrations:  " && find database/migrations -name "*.php" | wc -l
    echo -ne "  Tests:       " && find tests -name "*.php" | wc -l
    echo -e "\n${WHITE}Frontend:${NC}"
    echo -ne "  Components:  " && find resources/js/Components -name "*.vue" 2>/dev/null | wc -l || echo 0
    echo -ne "  Pages:       " && find resources/js/Pages -name "*.vue" 2>/dev/null | wc -l || echo 0
    echo -e "\n${WHITE}Lines of Code (PHP):${NC}"
    find app -name "*.php" | xargs wc -l | tail -n 1
    echo -e "${BLUE}=== END STATISTICS ===${NC}"
}

# Function to run CAD Importer specific tests
run_cad_tests() {
    echo -e "${BLUE}Running CAD Importer (KISS/XSR) tests...${NC}"
    docker compose exec app php artisan test --filter=ImportTest
}

# Main menu function
main_menu() {
    while true; do
        clear
        local now=$(date +"%H:%M:%S")
        
        # SteelFlow Fuji Logo
        echo -e "            ${ORANGE}◢◣${NC}"
        echo -e "           ${ORANGE}◢██◣${NC}"
        echo -e "          ${ORANGE}◢█◤◥█◣${NC}"
        echo -e "         ${ORANGE}◢█◤  ◥█◣${NC}"
        echo -e "      ${WHITE}S T E E L F L O W${NC}"
        echo -e ""
        
        # Menu Box
        echo -e "${BROWN}┌─────────────────────────────────────────────────────────────────────┐${NC}"
        echo -e "${BROWN}│${NC}  ${WHITE}SteelFlow MRP${NC} ${BROWN}─${NC} ${YELLOW}Workbench v1.0${NC}                             ${WHITE}${now}${NC} ${BROWN}│${NC}"
        echo -e "${BROWN}├─────────────────────────────────────────────────────────────────────┤${NC}"
        echo -e "${BROWN}│${NC} ${WHITE}SYSTEM & ENVIRONMENT${NC}                                                ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[1]${NC} Full Diagnostics      ${CYAN}[6]${NC} ${RED}Reset Database (WIPE)${NC}                ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[2]${NC} Check Health          ${CYAN}[7]${NC} Rebuild Docker (No-Cache)           ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[3]${NC} Repair Issues         ${CYAN}[8]${NC} Tail Application Logs               ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[4]${NC} Laravel Tinker        ${CYAN}[9]${NC} Generate IDE Helpers                ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[5]${NC} Seed Database         ${CYAN}[16]${NC} Project Statistics                 ${BROWN}│${NC}"
        echo -e "${BROWN}├─────────────────────────────────────────────────────────────────────┤${NC}"
        echo -e "${BROWN}│${NC} ${WHITE}TESTING & QUALITY${NC}                                                   ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[10]${NC} Run All Tests        ${CYAN}[13]${NC} Backend Quality Checks             ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[11]${NC} Tests w/ AutoFix     ${CYAN}[14]${NC} Frontend Linting Check             ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[12]${NC} Backend PHPUnit      ${CYAN}[15]${NC} Auto-Fix Frontend Lint             ${BROWN}│${NC}"
        echo -e "${BROWN}├─────────────────────────────────────────────────────────────────────┤${NC}"
        echo -e "${BROWN}│${NC} ${WHITE}ASSETS & SEARCH${NC}                                                     ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[17]${NC} Build Frontend Assets  ${CYAN}[19]${NC} Run CAD Importer Tests           ${BROWN}│${NC}"
        echo -e "${BROWN}│${NC}  ${CYAN}[18]${NC} Re-index Search        ${CYAN}[0]${NC} ${YELLOW}Exit Workbench${NC}                   ${BROWN}│${NC}"
        echo -e "${BROWN}└─────────────────────────────────────────────────────────────────────┘${NC}"
        echo -e "${RED}██████${ORANGE}██████${YELLOW}██████${GREEN}██████${BLUE}██████${MAGENTA}██████${NC}"
        
        echo -ne "\n ${WHITE}Select Option${NC} ${ORANGE}λ${NC} "
        read -r choice

        case $choice in
            1) run_full_diagnostics ;;
            2) check_system_health ;;
            3) repair_system ;;
            4) run_tinker ;;
            5) seed_database ;;
            6) reset_database ;;
            7) rebuild_docker ;;
            8) show_logs ;;
            9) generate_ide_helpers ;;
            10) run_backend_tests; run_backend_quality_checks; run_frontend_lint_check ;;
            11) run_tests_with_autorepair ;;
            12) run_backend_tests ;;
            13) run_backend_quality_checks ;;
            14) run_frontend_lint_check ;;
            15) run_frontend_lint_fix ;;
            16) show_project_stats ;;
            17) run_frontend_build ;;
            18) reindex_search ;;
            19) run_cad_tests ;;
            0) echo "Exiting."; exit 0 ;;
            *) echo -e "${RED}Invalid option: $choice${NC}" ;;
        esac
        
        echo -ne "\n${YELLOW}Press enter to return to menu...${NC}"
        read
    done
}

# Handle command line arguments (non-interactive mode)
if [ $# -gt 0 ]; then
    case "$1" in
        "test")
            run_backend_tests && run_backend_quality_checks && run_frontend_lint_check
            exit $?
            ;;
        "backend")
            run_backend_tests
            exit $?
            ;;
        "quality")
            run_backend_quality_checks
            exit $?
            ;;
        "lint")
            run_frontend_lint_check
            exit $?
            ;;
        "fix")
            run_frontend_lint_fix
            exit $?
            ;;
        "autorepair")
            run_tests_with_autorepair
            exit $?
            ;;
        "rebuild")
            rebuild_docker
            exit $?
            ;;
        "health")
            check_system_health
            exit $?
            ;;
        "repair")
            repair_system
            exit $?
            ;;
        "tinker")
            run_tinker
            exit $?
            ;;
        "logs")
            show_logs
            exit $?
            ;;
        "seed")
            seed_database
            exit $?
            ;;
        "ide-helpers")
            generate_ide_helpers
            exit $?
            ;;
        "stats")
            show_project_stats
            exit $?
            ;;
        "build")
            run_frontend_build
            exit $?
            ;;
        "reindex")
            reindex_search
            exit $?
            ;;
        "cad-tests")
            run_cad_tests
            exit $?
            ;;
        "reset-db")
            # For non-interactive reset-db, we skip the prompt if a force flag is provided
            if [ "$2" == "--force" ]; then
                docker compose exec app php artisan migrate:fresh --seed
                exit $?
            else
                reset_database
                exit $?
            fi
            ;;
        "help"|"--help"|"-h")
            echo "Usage: $0 [command]"
            echo ""
            echo "Commands:"
            echo "  test              - Run all tests"
            echo "  backend           - Run PHPUnit tests"
            echo "  quality           - Run backend quality checks"
            echo "  lint              - Run frontend linting"
            echo "  fix               - Run frontend linting fix"
            echo "  autorepair        - Run tests with automatic repair on failure"
            echo "  rebuild           - Rebuild Docker containers"
            echo "  health            - Check system health"
            echo "  repair            - Repair system issues"
            echo "  tinker            - Access Laravel Tinker"
            echo "  logs              - Tail application logs"
            echo "  seed              - Run database seeders"
            echo "  ide-helpers       - Generate IDE helper files"
            echo "  stats             - Show project statistics"
            echo "  build             - Build frontend assets"
            echo "  reindex           - Re-index search"
            echo "  cad-tests         - Run CAD importer tests"
            echo "  reset-db [--force]- Reset database"
            echo "  help              - Show this help message"
            exit 0
            ;;
        *)
            echo "Unknown command: $1"
            echo "Use '$0 help' for usage information."
            exit 1
            ;;
    esac
fi

# Default behavior if no arguments: check docker and enter menu
if ! docker compose ps | grep -q "app" && [[ ! "$1" == "rebuild" ]]; then
    echo "Starting Docker containers..."
    docker compose up -d
    sleep 2
fi

main_menu
