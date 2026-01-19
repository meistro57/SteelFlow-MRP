#!/bin/bash

# Default to "all" lines if no switch is provided
LINES="all"
LOG_FILE="logs-$(date +'%Y-%m-%d-%H%M').txt"

# Process the command line switches
# This looks for -n followed by a value
while getopts "n:" opt; do
  case $opt in
    n) LINES="$OPTARG" ;;
    \?) echo "Invalid option -$OPTARG" >&2; exit 1 ;;
  esac
done

echo "----------------------------------------"
echo " 📝 Capture Mode:  Last $LINES lines + Following"
echo " 📂 Saving to:     $LOG_FILE"
echo " 🛑 Stop command:  Ctrl+C"
echo "----------------------------------------"

# Run docker compose with the --tail flag
docker compose logs --tail="$LINES" -f | tee "$LOG_FILE"
