<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Receipt - <?= $invoice['invoice_no'] ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            background: white;
            padding: 10px;
            width: 80mm;
            margin: 0 auto;
        }

        .receipt {
            width: 100%;
            max-width: 80mm;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-tagline {
            font-size: 8px;
            margin-bottom: 2px;
        }

        .company-address {
            font-size: 8px;
            margin-bottom: 1px;
        }

        .separator {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
        }

        .double-separator {
            border-bottom: 1px solid #000;
            margin: 10px 0;
        }

        .invoice-info {
            margin-bottom: 10px;
        }

        .invoice-info .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .customer-info {
            margin-bottom: 10px;
        }

        .item-table {
            width: 100%;
            margin: 10px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .item-desc {
            flex: 1;
            padding-right: 5px;
        }

        .item-amount {
            text-align: right;
            white-space: nowrap;
        }

        .total-section {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .grand-total {
            font-weight: bold;
            font-size: 11px;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 5px;
        }

        .notes {
            margin-top: 10px;
            font-size: 8px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .payment-info {
            margin: 10px 0;
            font-size: 8px;
            text-align: center;
        }

        .qr-code {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border: 1px dashed #000;
        }

        @media print {
            body {
                padding: 0;
                width: 80mm;
            }

            .receipt {
                width: 80mm;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="company-name"><?= strtoupper($company['name'] ?? '') ?></div>
            <div class="company-tagline"><?= $company['tagline'] ?? '' ?></div>
            <div class="company-address"><?= $company['address'] ?? '' ?></div>
            <div class="company-address"><?= $company['city'] ?? '' ?><?= !empty($company['city']) && !empty($company['phone']) ? ' - ' : '' ?><?= $company['phone'] ?? '' ?></div>
            <div class="company-address"><?= $company['website'] ?? '' ?></div>
        </div>

        <!-- Invoice Information -->
        <div class="invoice-info">
            <div class="row">
                <span>No Invoice:</span>
                <span><?= $invoice['invoice_no'] ?></span>
            </div>
            <div class="row">
                <span>Tanggal:</span>
                <span><?= date('d/m/Y H:i') ?></span>
            </div>
            <div class="row">
                <span>Periode:</span>
                <span><?= formatPeriodeShort($invoice['periode']) ?></span>
            </div>
            <div class="row">
                <span>Pelanggan:</span>
                <span><?= $customer['nomor_layanan'] ?? 'N/A' ?></span>
            </div>
        </div>

        <div class="separator"></div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div><strong><?= $customer['nama_pelanggan'] ?? 'Nama Pelanggan' ?></strong></div>
            <div><?= $customer['alamat'] ?? $customer['address'] ?? 'Alamat tidak tersedia' ?></div>
            <div><?= $customer['telepphone'] ?? 'Tidak tersedia' ?></div>
        </div>

        <div class="separator"></div>

        <!-- Items -->
        <div class="item-table">
            <div class="item-row">
                <div class="item-desc"><strong>DESKRIPSI</strong></div>
                <div class="item-amount"><strong>JUMLAH</strong></div>
            </div>

            <div class="separator"></div>

            <div class="item-row">
                <div class="item-desc"><?= $invoice['package'] ?></div>
                <div class="item-amount">Rp <?= number_format($invoice['bill'], 0, ',', '.') ?></div>
            </div>

            <?php if (isset($invoice['additional_fee']) && $invoice['additional_fee'] > 0): ?>
                <div class="item-row">
                    <div class="item-desc">Biaya Tambahan</div>
                    <div class="item-amount">Rp <?= number_format($invoice['additional_fee'], 0, ',', '.') ?></div>
                </div>
            <?php endif; ?>

            <?php if (isset($invoice['discount']) && $invoice['discount'] > 0): ?>
                <div class="item-row">
                    <div class="item-desc">Diskon</div>
                    <div class="item-amount">-Rp <?= number_format($invoice['discount'], 0, ',', '.') ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Total Section -->
        <div class="total-section">
            <?php
            $subtotal = $invoice['bill'];
            if (isset($invoice['additional_fee'])) $subtotal += $invoice['additional_fee'];
            if (isset($invoice['discount'])) $subtotal -= $invoice['discount'];
            ?>

            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
            </div>

            <?php if (isset($invoice['arrears']) && $invoice['arrears'] > 0): ?>
                <div class="total-row">
                    <span>Tunggakan:</span>
                    <span>Rp <?= number_format($invoice['arrears'], 0, ',', '.') ?></span>
                </div>
            <?php endif; ?>

            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rp <?= number_format($subtotal + ($invoice['arrears'] ?? 0), 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="separator"></div>

        <!-- Payment Status -->
        <div class="center">
            <?php if ($invoice['status'] == 'paid'): ?>
                <strong>*** LUNAS ***</strong><br>
                Terima kasih atas pembayaran Anda
            <?php else: ?>
                <strong>*** BELUM LUNAS ***</strong><br>
                Batas pembayaran: <?= date('d/m/Y', strtotime($invoice['due_date'] ?? $invoice['periode'] . '-10')) ?>
            <?php endif; ?>
        </div>

        <div class="separator"></div>

        <!-- Payment Info -->
        <div class="payment-info">
            <div><strong>CARA PEMBAYARAN:</strong></div>
            <div>Transfer Bank, QRIS, Virtual Account</div>
            <div>Indomaret, Alfamart</div>
            <br>
            <div><strong>PEMBAYARAN ONLINE:</strong></div>
            <div><?= base_url('/' . $customer['nomor_layanan']) ?></div>
        </div>

        <div class="separator"></div>

        <!-- QR Code Section -->
        <div class="qr-code">
            <div><strong>SCAN QR CODE UNTUK PEMBAYARAN</strong></div>
            <div style="margin: 10px 0; height: 60px; border: 1px solid #000; display: flex; align-items: center; justify-content: center;">
                [QR CODE]
            </div>
            <div style="font-size: 7px;">Scan dengan aplikasi e-wallet atau banking</div>
        </div>

        <!-- Footer Notes -->
        <div class="notes">
            <div><strong>TERIMA KASIH</strong></div>
            <div>Simpan struk ini sebagai bukti pembayaran</div>
            <?php if (!empty($company['phone'])): ?><div>CS: <?= $company['phone'] ?></div><?php endif; ?>
            <?php if (!empty($company['website'])): ?><div><?= $company['website'] ?></div><?php endif; ?>
        </div>
    </div>

    <script>
        // Auto print untuk thermal
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>