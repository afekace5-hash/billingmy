<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= esc($invoice->invoice_no) ?> - Billing System</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
            background: #f4f7fa;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* --- INVOICE HEADER --- */
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e7ef;
            padding-bottom: 20px;
        }

        .header-left {
            flex: 1;
        }

        .header .logo {
            height: 70px;
            margin-bottom: 10px;
        }

        .company-info {
            font-size: 14px;
            color: #2d3a4a;
            line-height: 1.6;
        }

        .company-info strong {
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }

        .company-info .tagline {
            color: #1a73e8;
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }

        .company-info a {
            color: #1a73e8;
            text-decoration: none;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a73e8;
            margin-bottom: 8px;
        }

        .invoice-title .details {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }

        /* --- INFO TABLE --- */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin: 25px 0;
        }

        .info-left,
        .info-right {
            flex: 1;
        }

        .info-right {
            text-align: right;
        }

        .info-section strong {
            display: block;
            color: #333;
            margin-bottom: 8px;
        }

        .info-section p {
            margin: 4px 0;
            color: #555;
            line-height: 1.5;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .status-lunas {
            color: #43a047;
            background: #e8f5e9;
            border: 2px solid #43a047;
        }

        .status-belum-lunas {
            color: #e53935;
            background: #ffebee;
            border: 2px solid #e53935;
        }

        .periode {
            font-size: 18px;
            font-weight: 700;
            color: #1a73e8;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        /* --- ITEMS TABLE --- */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }

        .items-table th,
        .items-table td {
            padding: 12px;
            border: 1px solid #e0e7ef;
            text-align: left;
        }

        .items-table th {
            background: #f1f6fb;
            color: #1a73e8;
            font-weight: 700;
        }

        .items-table tbody tr {
            background: #fff;
        }

        .items-table tbody tr:hover {
            background: #f9f9f9;
        }

        .items-table tfoot td {
            font-weight: 700;
            color: #1a73e8;
            background: #f8fafc;
            font-size: 18px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* --- KETERANGAN --- */
        .keterangan {
            margin: 25px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #1a73e8;
        }

        .keterangan strong {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        /* --- SIGNATURES --- */
        .signatures {
            display: flex;
            justify-content: space-around;
            margin: 40px 0 30px;
        }

        .signature-box {
            text-align: center;
            flex: 1;
        }

        .signature-box p {
            margin-bottom: 60px;
        }

        /* --- BANK INFO --- */
        .bank-info {
            display: flex;
            gap: 30px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e3e8ee;
        }

        .bank-section {
            flex: 1;
        }

        .bank-section h3 {
            font-size: 14px;
            font-weight: 600;
            color: #1a73e8;
            margin-bottom: 10px;
        }

        .bank-section ul {
            margin: 0;
            padding-left: 20px;
            list-style: disc;
        }

        .bank-section li {
            margin-bottom: 6px;
            font-size: 13px;
            line-height: 1.5;
        }

        .payment-gateway {
            text-align: right;
        }

        .payment-gateway p {
            margin: 5px 0;
            font-size: 13px;
        }

        .payment-gateway a {
            color: #1a73e8;
            text-decoration: underline;
            word-break: break-all;
            font-size: 12px;
        }

        /* --- PRINT BUTTON --- */
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            margin: 0 5px;
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 0 5px;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        /* --- PRINT STYLES --- */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                max-width: 100%;
                padding: 15px;
            }

            .no-print {
                display: none !important;
            }

            .header {
                page-break-inside: avoid;
            }

            .items-table {
                page-break-inside: avoid;
            }

            @page {
                margin: 1.5cm;
                size: A4;
            }
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
            }

            .invoice-title {
                text-align: left;
                margin-top: 15px;
            }

            .info-section {
                flex-direction: column;
                gap: 20px;
            }

            .info-right {
                text-align: left;
            }

            .bank-info {
                flex-direction: column;
                gap: 20px;
            }

            .payment-gateway {
                text-align: left;
            }

            .items-table {
                font-size: 12px;
            }

            .items-table th,
            .items-table td {
                padding: 8px 6px;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print Invoice</button>
        <a href="<?= site_url('customer-portal/invoices') ?>" class="btn-back">← Kembali</a>
    </div>

    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div class="header-left">
                <?php
                helper('company');
                $company = getCompanyData();
                ?>
                <img src="<?= getCompanyLogo('lg') ?>" alt="Logo" class="logo">
                <div class="company-info">
                    <strong><?= esc(!empty($company['name']) ? $company['name'] : 'PT. KIMONET DIGITAL SYNERGY') ?></strong>
                    <span class="tagline"><?= esc(!empty($company['tagline']) ? $company['tagline'] : 'Dari Kita, Untuk Konektivitas Nusantara') ?></span>
                    <?= esc(!empty($company['address']) ? $company['address'] : 'Dusun Lebo Kulon Rt02/rw08') ?><br>
                    <?= esc(!empty($company['city']) ? $company['city'] : 'Batang') ?>, Telp <?= esc(!empty($company['phone']) ? $company['phone'] : '085183112127') ?><br>
                    <a href="<?= esc(!empty($company['website']) ? $company['website'] : 'https://www.kimonet.my.id') ?>" target="_blank">
                        <?= esc(!empty($company['website']) ? $company['website'] : 'www.kimonet.my.id') ?>
                    </a>
                </div>
            </div>
            <div class="invoice-title">
                <h1>Invoice #<?= esc($invoice->invoice_no) ?></h1>
                <div class="details">
                    <div>Tanggal Bayar: <?= esc($invoice->paid_at ? date('d-m-Y H:i:s', strtotime($invoice->paid_at)) : '-') ?></div>
                    <div>Nomor Pelanggan: <?= esc($invoice->customer_no) ?></div>
                </div>
            </div>
        </div>

        <!-- INFO SECTION -->
        <div class="info-section">
            <div class="info-left">
                <strong>Kepada,</strong>
                <p><?= esc($invoice->customer_name) ?></p>
                <p><?= esc($invoice->customer_address) ?></p>
                <p>Telp. <?= esc($invoice->customer_phone) ?></p>
            </div>
            <div class="info-right">
                <?php if ($invoice->status === 'paid' || $invoice->status === 'lunas'): ?>
                    <span class="status-badge status-lunas">✓ LUNAS</span>
                <?php else: ?>
                    <span class="status-badge status-belum-lunas">BELUM LUNAS</span>
                <?php endif; ?>
                <div>
                    <strong>PERIODE</strong>
                    <div class="periode"><?= strtoupper(date('F Y', strtotime($invoice->periode . '-01'))) ?></div>
                </div>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" width="50">No</th>
                    <th>Deskripsi</th>
                    <th class="text-right" width="120">Tarif</th>
                    <th width="200">Pemakaian</th>
                    <th class="text-right" width="120">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td><?= esc($invoice->package) ?></td>
                    <td class="text-right">Rp <?= number_format($invoice->bill, 0, ',', '.') ?></td>
                    <td><?= esc($invoice->usage_period) ?></td>
                    <td class="text-right">Rp <?= number_format($invoice->bill, 0, ',', '.') ?></td>
                </tr>
                <?php if (isset($invoice->arrears) && $invoice->arrears > 0): ?>
                    <tr>
                        <td class="text-center">2</td>
                        <td>Tunggakan</td>
                        <td class="text-right">Rp <?= number_format($invoice->arrears, 0, ',', '.') ?></td>
                        <td>-</td>
                        <td class="text-right">Rp <?= number_format($invoice->arrears, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (isset($invoice->additional_fee) && $invoice->additional_fee > 0): ?>
                    <tr>
                        <td class="text-center"><?= isset($invoice->arrears) && $invoice->arrears > 0 ? '3' : '2' ?></td>
                        <td>Biaya Pemasangan</td>
                        <td class="text-right">Rp <?= number_format($invoice->additional_fee, 0, ',', '.') ?></td>
                        <td>One Time</td>
                        <td class="text-right">Rp <?= number_format($invoice->additional_fee, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (isset($invoice->discount) && $invoice->discount > 0): ?>
                    <tr>
                        <td class="text-center">-</td>
                        <td>Diskon</td>
                        <td class="text-right">Rp <?= number_format($invoice->discount, 0, ',', '.') ?></td>
                        <td>-</td>
                        <td class="text-right">- Rp <?= number_format($invoice->discount, 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">GRAND TOTAL</td>
                    <td class="text-right">
                        Rp <?= number_format(
                                ((float)$invoice->bill +
                                    (float)($invoice->arrears ?? 0) +
                                    (float)($invoice->additional_fee ?? 0) -
                                    (float)($invoice->discount ?? 0)),
                                0,
                                ',',
                                '.'
                            ) ?>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- KETERANGAN -->
        <?php if (!empty($invoice->keterangan) && $invoice->keterangan !== '-'): ?>
            <div class="keterangan">
                <strong>Keterangan:</strong>
                <?= esc($invoice->keterangan) ?>
            </div>
        <?php endif; ?>

        <!-- SIGNATURES -->
        <div class="signatures">
            <div class="signature-box">
                <p>Penyetor,</p>
                <p>( <?= esc($invoice->customer_name) ?> )</p>
            </div>
            <div class="signature-box">
                <p>Penerima,</p>
                <p>( OFFICE )</p>
            </div>
        </div>

        <!-- BANK INFO & PAYMENT GATEWAY -->
        <div class="bank-info">
            <div class="bank-section">
                <h3>Rekening Pembayaran Transfer Bank:</h3>
                <?php if (!empty($activeBanks)): ?>
                    <ul>
                        <?php foreach ($activeBanks as $bank): ?>
                            <li>
                                <b><?= esc(strtoupper($bank['bank_name'])) ?></b> -
                                <?= esc($bank['account_number']) ?>
                                a.n. <?= esc($bank['account_holder']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #e53935;">Tidak ada rekening bank aktif.</p>
                <?php endif; ?>
            </div>

            <div class="bank-section payment-gateway">
                <h3>Pembayaran Online:</h3>
                <p>Bisa menggunakan <b>QRIS, VIRTUAL AKUN,<br>INDOMARET</b> dan <b>ALFAMART</b></p>
                <p style="margin-top: 10px;"><strong>Kunjungi:</strong></p>
                <a href="<?= esc($invoice->payment_url ?? site_url('customer-portal')) ?>" target="_blank">
                    <?= esc($invoice->payment_url ?? site_url('customer-portal')) ?>
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-print jika ada parameter print di URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>

</html>