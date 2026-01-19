#!/bin/bash

# SteelFlow MRP Module Test Script
# This script runs all tests for both core Laravel and Nwidart Modules

# Set colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting SteelFlow MRP Module Health Check...${NC}"

# 1. Check for PHP Syntax Errors in all Modules
echo -e "\n${GREEN}1. Checking for syntax errors in Modules...${NC}"
find Modules -name "*.php" -print0 | xargs -0 -n 1 -P 4 php -l | grep -v "No syntax errors detected"
if [ $? -eq 0 ]; then
    echo -e "${RED}Found syntax errors in some files!${NC}"
else
    echo -e "No syntax errors found in Modules."
fi

# 2. Run Main Laravel Tests
echo -e "\n${GREEN}2. Running Main Laravel Tests...${NC}"
php artisan test
CORE_RESULT=$?
if [ $CORE_RESULT -eq 0 ]; then echo -e "${GREEN}Core tests passed!${NC}"; else echo -e "${RED}Core tests failed!${NC}"; fi

# 3. Run Module Tests
echo -e "\n${GREEN}3. Running Module-specific Tests...${NC}"
MODULE_PATHS=$(find Modules -maxdepth 1 -mindepth 1 -type d)

for MODULE_PATH in $MODULE_PATHS; do
    # Support both 'Tests' and 'tests' directories
    if [ -d "$MODULE_PATH/Tests" ]; then
        TEST_DIR="$MODULE_PATH/Tests"
    elif [ -d "$MODULE_PATH/tests" ]; then
        TEST_DIR="$MODULE_PATH/tests"
    else
        TEST_DIR=""
    fi

    if [ -n "$TEST_DIR" ]; then
        echo -e "\nTesting Module: ${GREEN}$MODULE${NC}"
        # Using php artisan test instead of phpunit directly
        php artisan test "$TEST_DIR"
        MODULE_RESULT=$?
        if [ $MODULE_RESULT -ne 0 ]; then
            echo -e "${RED}Tests failed for module: $MODULE${NC}"
        fi
    else
        echo -e "Module ${GREEN}$MODULE${NC} has no tests directory."
    fi
done

# 4. Check Service Providers and Core Services resolvability
echo -e "\n${GREEN}4. Verifying Service Resolvability...${NC}"
php scripts/check-services.php
TINKER_RESULT=$?

# 5. Summary
echo -e "\n${GREEN}=====================================${NC}"
echo -e "Module health check completed."
if [ $CORE_RESULT -eq 0 ] && [ $TINKER_RESULT -eq 0 ]; then
    echo -e "${GREEN}Overall Status: SUCCESS${NC}"
else
    echo -e "${RED}Overall Status: FAILED (Check logs above)${NC}"
fi
echo -e "${GREEN}=====================================${NC}"
