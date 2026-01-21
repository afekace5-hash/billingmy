#!/bin/bash
# Auto Isolir Runner Script untuk VPS/Linux Hosting
# Simpan sebagai: run_auto_isolir.sh
# Permissions: chmod +x run_auto_isolir.sh

# Set variables
APP_PATH="/var/www/html/billingkimo"  # Ganti dengan path aplikasi Anda
PHP_PATH="/usr/bin/php"               # Ganti dengan path PHP yang benar
LOG_FILE="$APP_PATH/writable/logs/auto-isolir.log"
ERROR_LOG="$APP_PATH/writable/logs/auto-isolir-error.log"
EMAIL_ADMIN="admin@yourdomain.com"    # Ganti dengan email admin

# Function to log with timestamp
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Function to send email notification
send_email() {
    local subject="$1"
    local message="$2"
    
    if command -v mail &> /dev/null; then
        echo "$message" | mail -s "$subject" "$EMAIL_ADMIN"
    elif command -v sendmail &> /dev/null; then
        echo -e "Subject: $subject\n\n$message" | sendmail "$EMAIL_ADMIN"
    fi
}

# Start logging
log_message "Auto Isolir Script Started"

# Check if app directory exists
if [ ! -d "$APP_PATH" ]; then
    log_message "ERROR: Application directory not found: $APP_PATH"
    send_email "Auto Isolir Error" "Application directory not found: $APP_PATH"
    exit 1
fi

# Change to app directory
cd "$APP_PATH" || {
    log_message "ERROR: Cannot change to app directory: $APP_PATH"
    exit 1
}

# Check if PHP exists
if [ ! -f "$PHP_PATH" ]; then
    log_message "ERROR: PHP not found at: $PHP_PATH"
    send_email "Auto Isolir Error" "PHP not found at: $PHP_PATH"
    exit 1
fi

# Create logs directory if not exists
mkdir -p "$(dirname "$LOG_FILE")"

# Run auto isolir using CodeIgniter spark command
log_message "Running auto isolir command..."

if "$PHP_PATH" spark auto:isolir >> "$LOG_FILE" 2>> "$ERROR_LOG"; then
    log_message "Auto isolir completed successfully"
    
    # Count isolated customers from log
    isolated_count=$(tail -50 "$LOG_FILE" | grep -c "Customer isolated:")
    
    if [ "$isolated_count" -gt 0 ]; then
        send_email "Auto Isolir Report" "Auto isolir completed successfully. $isolated_count customers were isolated."
    fi
else
    log_message "ERROR: Auto isolir command failed"
    
    # Get last error
    last_error=$(tail -5 "$ERROR_LOG")
    
    send_email "Auto Isolir Failed" "Auto isolir command failed. Last error: $last_error"
    exit 1
fi

# Alternative: Use cron_simple.php if spark command doesn't work
# Uncomment the lines below and comment the spark command above
#
# log_message "Running auto isolir using PHP script..."
# if "$PHP_PATH" cron_simple.php >> "$LOG_FILE" 2>> "$ERROR_LOG"; then
#     log_message "Auto isolir completed successfully"
# else
#     log_message "ERROR: Auto isolir script failed"
#     exit 1
# fi

log_message "Auto Isolir Script Finished"

# Clean old logs (keep last 30 days)
find "$(dirname "$LOG_FILE")" -name "*.log" -type f -mtime +30 -delete 2>/dev/null

exit 0
