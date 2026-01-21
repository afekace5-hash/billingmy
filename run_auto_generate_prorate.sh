#!/bin/bash
# Auto Generate Prorate - Add to crontab for daily execution
# Example: 0 2 * * * /path/to/run_auto_generate_prorate.sh

cd "$(dirname "$0")"

echo "========================================"
echo "Auto Generate Prorate"
echo "========================================"
echo ""

php spark prorate:generate

echo ""
echo "========================================"
echo "Process completed at $(date)"
echo "========================================"
