#!/bin/bash

# Define colors
BOLD='\033[1m'
DIM='\033[2m'
CYAN='\033[0;36m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
RESET='\033[0m'

LOG_FILE="CRUSH_LOG.md"

if [ ! -f "$LOG_FILE" ]; then
    echo -e "${YELLOW}Error: ${LOG_FILE} not found.${RESET}"
    exit 1
fi

# Header
echo -e "\n${BOLD}${MAGENTA}┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓${RESET}"
echo -e "${BOLD}${MAGENTA}┃${RESET}  ${BOLD}${CYAN}SteelFlow MRP - Revision & Progress Log${RESET}                  ${BOLD}${MAGENTA}┃${RESET}"
echo -e "${BOLD}${MAGENTA}┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛${RESET}"

# Simple line-by-line processing
while IFS= read -r line || [ -n "$line" ]; do
    # Main Title (#)
    if [[ $line =~ ^#\ (.*) ]]; then
        title="${BASH_REMATCH[1]}"
        echo -e "\n${BOLD}${CYAN}${title}${RESET}"
        echo -e "${CYAN}$(printf '%.s━' $(seq 1 ${#title}))${RESET}"
    
    # Section Header (##)
    elif [[ $line =~ ^##\ (.*) ]]; then
        header="${BASH_REMATCH[1]}"
        echo -e "\n${BOLD}${GREEN}▶ ${header}${RESET}"
    
    # Sub-header (###)
    elif [[ $line =~ ^###\ (.*) ]]; then
        echo -e "\n  ${BOLD}${YELLOW}${BASH_REMATCH[1]}${RESET}"
    
    # Bullet points (- )
    elif [[ $line =~ ^-\ (.*) ]]; then
        content="${BASH_REMATCH[1]}"
        # Highlight backticks in bullet points
        content=$(echo "$content" | sed "s/\`\([^\`]*\)\`/\x1b[1;33m\1\x1b[0m/g")
        echo -e "    ${BOLD}${BLUE}•${RESET} ${content}"
    
    # Default line
    else
        if [[ -n "$line" ]]; then
            # Highlight backticks in normal lines
            formatted_line=$(echo "$line" | sed "s/\`\([^\`]*\)\`/\x1b[1;33m\1\x1b[0m/g")
            echo -e "  $formatted_line"
        else
            echo ""
        fi
    fi
done < "$LOG_FILE"

echo -e "\n${DIM}End of log - Generated with Crush${RESET}\n"
