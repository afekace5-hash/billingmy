# Setup Auto Generate Invoice - Linux/Unix/MacOS

#!/bin/bash

echo "Setting up automatic invoice generation for Linux/Unix..."

# Path ke project
PROJECT_PATH="/path/to/your/billingkimo"

# Buat cron job untuk generate invoice setiap tanggal 1 jam 08:00
CRON_JOB="0 8 1 * * cd $PROJECT_PATH && php spark invoices:autogenerate >> writable/logs/invoice-generation.log 2>&1"

# Tambahkan ke crontab
(crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -

if [ $? -eq 0 ]; then
    echo "✓ Cron job berhasil ditambahkan!"
    echo "✓ Invoice akan otomatis di-generate setiap tanggal 1 jam 08:00"
    echo ""
    echo "Untuk melihat cron job yang telah dibuat:"
    echo "crontab -l"
    echo ""
    echo "Untuk edit/hapus cron job:"
    echo "crontab -e"
else
    echo "✗ Gagal menambahkan cron job"
fi

# Buat direktori log jika belum ada
mkdir -p "$PROJECT_PATH/writable/logs"

echo ""
echo "CATATAN:"
echo "1. Update PROJECT_PATH di script ini dengan path yang benar"
echo "2. Pastikan PHP bisa dijalankan dari command line"
echo "3. Pastikan permission folder writable/ sudah benar"