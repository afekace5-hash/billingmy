#!/bin/bash
#
# WhatsApp Billing Notification Cron Script
# Domain: https://billing.kimonet.my.id/
# 
# Script ini untuk testing manual atau backup jika cron hosting bermasalah
#

# Set variables
DOMAIN="https://billing.kimonet.my.id"
LOG_DIR="/tmp"
TIMESTAMP=$(date '+%Y-%m-%d_%H-%M-%S')
LOG_FILE="$LOG_DIR/whatsapp_billing_$TIMESTAMP.log"

echo "$(date): Starting WhatsApp Billing Notification Process..." | tee -a $LOG_FILE

# Function to call endpoint and log result
call_endpoint() {
    local endpoint=$1
    local description=$2
    
    echo "$(date): Calling $description..." | tee -a $LOG_FILE
    
    response=$(curl -s -w "\nHTTP_CODE:%{http_code}\nTIME:%{time_total}" "$DOMAIN/$endpoint")
    http_code=$(echo "$response" | grep "HTTP_CODE:" | cut -d: -f2)
    time_taken=$(echo "$response" | grep "TIME:" | cut -d: -f2)
    
    if [ "$http_code" = "200" ]; then
        echo "$(date): ✅ $description - Success (${time_taken}s)" | tee -a $LOG_FILE
    else
        echo "$(date): ❌ $description - Failed (HTTP: $http_code)" | tee -a $LOG_FILE
    fi
    
    echo "Response: $response" >> $LOG_FILE
    echo "----------------------------------------" >> $LOG_FILE
}

# Main notification process
call_endpoint "whatsapp/billing/send-all" "All Notifications"

# Individual endpoints (uncomment if needed for testing)
# call_endpoint "whatsapp/billing/send/due_date" "Due Date Notifications"
# call_endpoint "whatsapp/billing/send/h_minus_1" "H-1 Notifications" 
# call_endpoint "whatsapp/billing/send/h_minus_3" "H-3 Notifications"
# call_endpoint "whatsapp/billing/send/h_minus_7" "H-7 Notifications"
# call_endpoint "whatsapp/billing/send/payment_confirmation" "Payment Confirmations"

echo "$(date): WhatsApp Billing Notification Process Completed" | tee -a $LOG_FILE
echo "Log saved to: $LOG_FILE"

# Clean up old logs (keep only last 7 days)
find $LOG_DIR -name "whatsapp_billing_*.log" -mtime +7 -delete 2>/dev/null

# Optional: Send notification about completion
# curl -s "$DOMAIN/whatsapp/billing/test" >/dev/null 2>&1

exit 0
