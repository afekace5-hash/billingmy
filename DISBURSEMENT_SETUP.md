# PANDUAN KONFIGURASI AUTO DISBURSEMENT

Fitur auto disbursement memungkinkan sistem untuk secara otomatis mentransfer uang ke rekening bank user melalui payment gateway.

## Supported Payment Gateways

### 1. FLIP (Recommended untuk Indonesia)

- **Website**: https://flip.id
- **Cara Daftar**:
  1. Daftar di https://bigflip.id/register
  2. Verifikasi akun dan bisnis
  3. Dapatkan API Key di dashboard
  4. Set FLIP_SECRET_KEY di file .env

**Keuntungan**:

- Biaya transfer murah (Rp 2,500 - Rp 4,500)
- Support semua bank Indonesia
- Response cepat
- Validasi rekening real-time

### 2. XENDIT

- **Website**: https://xendit.co
- **Cara Daftar**:
  1. Daftar di https://dashboard.xendit.co/register
  2. Verifikasi akun
  3. Dapatkan Secret Key di Settings > API Keys
  4. Set XENDIT_SECRET_KEY di file .env

**Keuntungan**:

- Platform lengkap (payment + disbursement)
- Support international
- Dashboard yang user-friendly

### 3. MIDTRANS IRIS

- **Website**: https://midtrans.com
- **Cara Daftar**:
  1. Daftar di https://dashboard.midtrans.com/register
  2. Aktivasi fitur Iris (Disbursement)
  3. Dapatkan Iris API Key
  4. Set MIDTRANS_IRIS_API_KEY di file .env

**Keuntungan**:

- Brand terpercaya
- Terintegrasi dengan Midtrans Payment Gateway

## Instalasi

### 1. Copy Environment Variables

```bash
# Copy konfigurasi ke file .env
cat .env.disbursement.example >> .env
```

### 2. Set API Keys

Edit file `.env` dan isi API key sesuai provider yang digunakan:

```env
# Pilih provider (flip, xendit, atau midtrans)
DISBURSEMENT_PROVIDER=flip

# Isi API Key dari provider yang dipilih
FLIP_SECRET_KEY=your-actual-flip-api-key
# atau
XENDIT_SECRET_KEY=your-actual-xendit-api-key
# atau
MIDTRANS_IRIS_API_KEY=your-actual-iris-api-key
```

### 3. Update Database

Kolom sudah otomatis ditambahkan saat setup:

- disbursement_provider
- disbursement_reference
- disbursement_status
- disbursement_fee
- disbursement_response
- auto_disburse

### 4. Setup Webhook (Untuk auto-update status)

#### FLIP Webhook

URL: `https://yourdomain.com/webhook/disbursement/flip`
Set di: https://bigflip.id/settings/callback

#### XENDIT Webhook

URL: `https://yourdomain.com/webhook/disbursement/xendit`
Set di: Dashboard > Settings > Webhooks > Disbursement

#### MIDTRANS Webhook

URL: `https://yourdomain.com/webhook/disbursement/midtrans`
Set di: Dashboard > Settings > Notification URL

## Cara Penggunaan

### 1. Request Withdraw (User/Admin)

- User input amount, bank, account number, account name
- Sistem create withdraw request dengan status "pending"

### 2. Process Auto Disbursement (Admin)

Dari halaman withdraw list:

- Klik button "Auto Disburse" pada withdraw yang pending
- Pilih provider (jika berbeda dari default)
- Klik "Process"
- Sistem akan:
  - Validasi rekening bank (jika supported)
  - Kirim request disbursement ke payment gateway
  - Update status jadi "processing"
  - Save reference ID untuk tracking

### 3. Check Status (Optional)

- Klik button "Check Status" untuk update status terbaru
- Atau tunggu webhook callback dari payment gateway

### 4. Status Flow

```
pending -> processing -> completed
                      -> failed/rejected
```

## Testing Mode

Untuk testing, gunakan sandbox API keys:

### FLIP Sandbox

```env
FLIP_SECRET_KEY=test_your-test-key
FLIP_API_URL=https://bigflip.id/api/v2
```

### XENDIT Test Mode

```env
XENDIT_SECRET_KEY=xnd_development_your-test-key
XENDIT_API_URL=https://api.xendit.co
```

### Midtrans Sandbox

```env
MIDTRANS_IRIS_API_KEY=IRIS-your-sandbox-key
MIDTRANS_IRIS_API_URL=https://app.sandbox.midtrans.com/iris/api/v1
```

## Bank Account Validation

Semua provider support validasi rekening bank real-time:

- Otomatis validasi account name
- Cegah salah transfer
- Reduce error rate

## Fee Structure

### FLIP

- BCA, Mandiri, BNI, BRI: Rp 2,500
- Bank lain: Rp 4,000

### XENDIT

- Flat fee: Rp 4,000 - Rp 5,000

### MIDTRANS

- Variable based on bank: Rp 3,000 - Rp 5,000

## Security Best Practices

1. **Jangan commit API Keys ke repository**
2. **Gunakan HTTPS untuk production**
3. **Whitelist IP di dashboard provider** (jika available)
4. **Set maximum withdrawal amount**
5. **Implement two-factor authentication untuk admin**
6. **Log semua transactions**
7. **Monitor balance regularly**

## Troubleshooting

### Error: "Insufficient balance"

- Check balance di payment gateway dashboard
- Top up saldo

### Error: "Invalid bank account"

- Pastikan format account number benar
- Pastikan bank code valid
- Gunakan validasi rekening dulu

### Error: "API Key invalid"

- Double check API key di .env
- Pastikan tidak ada space atau newline
- Refresh dashboard provider

### Webhook tidak jalan

- Check webhook URL accessible dari internet
- Check firewall/security settings
- Verify webhook signature (jika required)
- Check webhook logs di provider dashboard

## Support

Jika ada masalah:

1. Check logs: `writable/logs/`
2. Check provider dashboard untuk transaction history
3. Contact provider support:
   - FLIP: support@flip.id
   - XENDIT: support@xendit.co
   - MIDTRANS: support@midtrans.com

## Changelog

### Version 1.0.0 (2026-01-03)

- Initial release
- Support FLIP, XENDIT, MIDTRANS
- Auto disbursement
- Bank account validation
- Webhook integration
- Balance checking
