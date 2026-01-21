<?php helper('company'); ?>
<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Cetak Invoice &mdash; Billing</title>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNhh/TCPM6x5CGR0UyWFAj4HUHd4F1GDAHl+M7Uu8iBPKZg04khlKt4r4Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" integrity="sha512-BNaRQnYJYiPSqHHDb58B0yaPfCu+Wgds8Gp/gU33kqBtgNS4tSPHuGibyoeqMV/TJlSKda6FXzoEyYGjTe+vXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* --- INVOICE PRINT & PDF MAIN CSS --- */
    /* Semua style di sini akan ikut ke PDF (html2pdf.js) */

    /* --- INVOICE HEADER --- */
    .header {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 18px;
        margin-bottom: 18px;
        border-bottom: 2px solid #e0e7ef;
        padding-bottom: 18px;
        flex-direction: row;
        position: relative;
    }

    .header .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0;
    }

    .header .company-info {
        font-size: 15px;
        color: #2d3a4a;
        line-height: 1.5;
        margin-bottom: 0;
    }

    .header .invoice-title {
        font-size: 30px;
        font-weight: 700;
        color: #1a73e8;
        letter-spacing: 1px;
        position: absolute;
        right: 0;
        top: 0;
        text-align: right;
        min-width: 180px;
    }

    @media (max-width: 600px) {
        .header {
            flex-direction: column;
            align-items: stretch;
            text-align: left;
        }

        .header>div {
            width: 100%;
        }

        .header .logo {
            justify-content: center;
            margin-bottom: 12px;
        }

        .header .company-info {
            margin-bottom: 12px;
        }

        .header .invoice-title {
            text-align: left;
            position: static !important;
            min-width: unset;
        }
    }

    /* --- INVOICE TABLES --- */
    .info-table,
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 18px;
    }

    .info-table td {
        font-size: 15px;
        padding: 8px;
    }

    .items-table th,
    .items-table td {
        padding: 10px 8px;
        border: 1px solid #e0e7ef;
    }

    .items-table th {
        background: #f1f6fb;
        color: #1a73e8;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .items-table tfoot td {
        font-weight: 700;
        color: #1a73e8;
        background: #f8fafc;
    }

    /* --- STATUS BADGE --- */
    .status-lunas {
        font-size: 16px;
        color: #43a047;
        font-weight: bold;
        border: 2px solid #43a047;
        padding: 4px 12px;
        border-radius: 6px;
        background: #e8f5e9;
        text-align: center;
        margin-bottom: 8px;
        display: inline-block;
    }

    .status-belum-lunas {
        font-size: 16px;
        color: #e53935;
        font-weight: bold;
        border: 2px solid #e53935;
        padding: 4px 12px;
        border-radius: 6px;
        background: #ffebee;
        text-align: center;
        margin-bottom: 8px;
        display: inline-block;
    }

    /* --- END INVOICE PRINT & PDF MAIN CSS --- */

    .header {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 18px;
        margin-bottom: 18px;
        border-bottom: 2px solid #e0e7ef;
        padding-bottom: 18px;
        flex-direction: row;
        position: relative;
    }

    @media (max-width: 600px) {
        .header {
            flex-direction: column;
            align-items: stretch;
            text-align: left;
        }

        .header>div {
            width: 100%;
        }

        .header .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
        }

        .header .company-info {
            margin-bottom: 12px;
        }

        .header .invoice-title {
            text-align: left;
            position: static !important;
            min-width: unset;
        }
    }
</style>

<div class="page-content" style="background: #f4f7fa; min-height: 100vh;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Action Buttons Row -->
                        <div class="col-xl-12 col-md-12 mb-5 no-print">
                            <div class="btn-showcase d-flex flex-wrap gap-2 mb-3">
                                <button type="button" class="btn btn-light btn-xl d-flex align-items-center gap-2" id="btnSelectPayment" data-invoice-id="<?= $invoice['id'] ?>">
                                    <i class="bx bx-credit-card"></i> <span>Pilih Pembayaran</span>
                                </button>
                                <button type="button" onclick="printDiv('area-1')" class="btn btn-light btn-xl d-flex align-items-center gap-2">
                                    <i class="bx bx-printer"></i> <span>PRINT HVS</span>
                                </button>
                                <button type="button" onclick="dot()" class="btn btn-light btn-xl d-flex align-items-center gap-2">
                                    <i class="bx bx-printer"></i> <span>PRINT BLUETOOTH</span>
                                </button>
                                <button type="button" onclick="downloadPDF()" class="btn btn-light btn-xl d-flex align-items-center gap-2">
                                    <i class="bx bx-download"></i> <span>DOWNLOAD</span>
                                </button>
                                <button type="button" onclick="copytext()" class="btn btn-light btn-xl d-flex align-items-center gap-2">
                                    <i class="bx bx-copy"></i> <span>COPY LINK INVOICE</span>
                                </button>
                                <button type="button" onclick="kirimulang()" class="btn btn-light btn-xl d-flex align-items-center gap-2">
                                    <i class="bx bxl-whatsapp"></i> <span>KIRIM ULANG</span>
                                </button>
                                <button type="button" onclick="setting()" class="btn btn-light btn-xl d-flex align-items-center gap-2">
                                    <i class="bx bx-cog"></i> <span>SETTING</span>
                                </button>
                                <a href="<?= base_url('invoices') ?>" class="btn btn-light btn-xl d-flex align-items-center gap-2">
                                    <i class="bx bx-undo"></i> <span>KEMBALI</span>
                                </a>
                            </div>
                        </div>

                        <!-- Invoice Content -->
                        <div id="area-1" class="invoice-content">
                            <div class="header">
                                <div>
                                    <img src="<?= getCompanyLogo('lg') ?>" alt="Logo" class="logo" style="height:70px;">
                                </div>
                                <div class="company-info" style="font-size:15px; color:#2d3a4a; line-height:1.5;">
                                    <?php $company = getCompanyData(); ?>
                                    <div style="text-align:left;">
                                        <strong><?= esc((!empty($company['name']) ? $company['name'] : 'PT. KIMONET DIGITAL SYNERGY')) ?></strong><br>
                                        <span style="color:#1a73e8; font-weight:500;">
                                            <?= esc(!empty($company['tagline']) ? $company['tagline'] : 'Dari Kita, Untuk Konektivitas Nusantara') ?>
                                        </span><br>
                                        <?= esc(!empty($company['address']) ? $company['address'] : 'Dusun Lebo Kulon Rt02/rw08') ?><br>
                                        <?= esc(!empty($company['city']) ? $company['city'] : 'Batang') ?>, Telp <?= esc(!empty($company['phone']) ? $company['phone'] : '085183112127') ?>
                                        <a href="<?= esc(!empty($company['website']) ? $company['website'] : 'https://www.kimonet.my.id') ?>" target="_blank" style="color:#1a73e8; text-decoration:none;">
                                            <?= esc(!empty($company['website']) ? $company['website'] : 'www.kimonet.my.id') ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="invoice-title" style="font-size:30px; font-weight:700; color:#1a73e8; letter-spacing:1px; position:absolute; right:0; top:0; text-align:right; min-width:180px;">
                                    <span style="font-size:20px; font-weight:700; color:#1a73e8; display:block; text-align:right;">Invoice #<?= esc(isset($invoice['invoice_no']) ? $invoice['invoice_no'] : '') ?></span>
                                    <div style="font-size:15px; font-weight:400; color:#222; line-height:1.3; margin-top:2px;">
                                        <div style="margin-bottom:2px;">Tanggal Bayar: <?= esc(isset($invoice['paid_at']) && $invoice['paid_at'] ? date('d-m-Y H:i:s', strtotime($invoice['paid_at'])) : (isset($invoice['payment_date']) && $invoice['payment_date'] ? date('d-m-Y H:i:s', strtotime($invoice['payment_date'])) : '-')) ?></div>
                                        <div>Nomor Pelanggan: <?= esc(isset($customer['nomor_layanan']) ? $customer['nomor_layanan'] : (isset($invoice['nomor_layanan']) ? $invoice['nomor_layanan'] : 'N/A')) ?></div>
                                    </div>
                                </div>
                            </div>

                            <table class="info-table" style="width:100%; border-collapse:collapse; margin-top:18px;">
                                <tr>
                                    <td style="font-size:15px;">
                                        <strong>Kepada,</strong><br>
                                        <?= esc(isset($customer['nama_pelanggan']) ? $customer['nama_pelanggan'] : (isset($invoice['nama_pelanggan']) ? $invoice['nama_pelanggan'] : 'Nama Pelanggan')) ?><br>

                                        Telp: <?= esc(isset($customer['telepphone']) ? $customer['telepphone'] : (isset($invoice['telepphone']) ? $invoice['telepphone'] : 'Tidak tersedia')) ?><br>
                                        Alamat: <?= esc(isset($customer['address']) ? $customer['address'] : (isset($invoice['address']) ? $invoice['address'] : 'Alamat tidak tersedia')) ?><br>
                                        <?= esc(isset($customer['village']) ? $customer['village'] : (isset($invoice['village']) ? $invoice['village'] : 'Desa Lebo')) ?>, <?= esc(isset($customer['district']) ? $customer['district'] : (isset($invoice['district']) ? $invoice['district'] : 'Gringsing')) ?><br>
                                        <?= esc(isset($customer['kabupaten']) ? $customer['kabupaten'] : 'Batang') ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php
                                        // Tampilkan status pembayaran untuk semua periode
                                        if (isset($invoice['status']) && ($invoice['status'] === 'paid' || $invoice['status'] === 'lunas')) {
                                            echo '<div class="status-lunas" style="font-size:16px; color:#43a047; font-weight:bold; border:2px solid #43a047; padding:4px 12px; border-radius:6px; background:#e8f5e9; text-align:center; margin-bottom:8px; display:inline-block;">LUNAS</div><br>';
                                        } else {
                                            echo '<div class="status-belum-lunas" style="font-size:16px; color:#e53935; font-weight:bold; border:2px solid #e53935; padding:4px 12px; border-radius:6px; background:#ffebee; text-align:center; margin-bottom:8px; display:inline-block;">BELUM LUNAS</div><br>';
                                        }
                                        ?>
                                        <strong>PERIODE</strong><br>
                                        <span style="font-size:19px; font-weight:700; letter-spacing:2px; color:#1a73e8;">
                                            <?= isset($invoice['periode']) ? strtoupper(date('F Y', strtotime($invoice['periode'] . '-01'))) : '-' ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <table class="items-table" style="width:100%; border-collapse:collapse; margin-top:18px;">
                                <thead>
                                    <tr style="background:#f1f6fb; color:#1a73e8; font-weight:700; letter-spacing:0.5px;">
                                        <th style="padding:10px 8px;">No</th>
                                        <th style="padding:10px 8px;">Deskripsi</th>
                                        <th style="padding:10px 8px;">Tarif</th>
                                        <th style="padding:10px 8px;">Pemakaian</th>
                                        <th style="padding:10px 8px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background:#fff;">
                                        <td style="padding:10px 8px; text-align:center;">1</td>
                                        <td style="padding:10px 8px;"><?= esc(isset($invoice['package']) ? $invoice['package'] : '') ?></td>
                                        <td style="padding:10px 8px;">Rp <?= isset($invoice['bill']) ? number_format((float)$invoice['bill'], 0, ',', '.') : '0' ?></td>
                                        <td style="padding:10px 8px;"><?= esc(isset($invoice['usage_period']) ? $invoice['usage_period'] : '') ?></td>
                                        <td style="padding:10px 8px; font-weight:600; color:#1a73e8;">Rp <?= isset($invoice['bill']) ? number_format((float)$invoice['bill'], 0, ',', '.') : '0' ?></td>
                                    </tr>
                                    <?php if (isset($invoice['additional_fee']) && (float)$invoice['additional_fee'] > 0): ?>
                                        <tr style="background:#fff;">
                                            <td style="padding:10px 8px; text-align:center;">2</td>
                                            <td style="padding:10px 8px;">Biaya Pemasangan</td>
                                            <td style="padding:10px 8px;">Rp <?= number_format((float)$invoice['additional_fee'], 0, ',', '.') ?></td>
                                            <td style="padding:10px 8px;">One Time</td>
                                            <td style="padding:10px 8px; font-weight:600; color:#1a73e8;">Rp <?= number_format((float)$invoice['additional_fee'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background:#f8fafc;">
                                        <td colspan="4" class="grand-total" style="text-align:right; font-size:20px; font-weight:bold; color:#1a73e8; padding-right:32px;">Grand Total</td>
                                        <td class="grand-total" style="font-size:20px; font-weight:bold; color:#1a73e8; padding-left:24px;">Rp <?= isset($invoice['bill']) ? number_format(((float)$invoice['bill'] + (float)($invoice['additional_fee'] ?? 0) - (float)($invoice['discount'] ?? 0)), 0, ',', '.') : '0' ?></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="keterangan">
                                <strong>Keterangan</strong><br>
                                <?= esc($invoice['keterangan'] ?? '-') ?>
                            </div>

                            <!-- DEBUG INFO -->
                            <div style="background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107; display: block;">
                                <strong>DEBUG INFO:</strong><br>
                                Payment History Exists: <?= isset($paymentHistory) ? 'YES' : 'NO' ?><br>
                                Payment History Count: <?= isset($paymentHistory) ? count($paymentHistory) : '0' ?><br>
                                Payment History Empty: <?= empty($paymentHistory) ? 'YES' : 'NO' ?><br>
                                <?php if (isset($paymentHistory) && !empty($paymentHistory)): ?>
                                    First Payment Code: <?= esc($paymentHistory[0]['payment_code'] ?? 'NULL') ?><br>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($paymentHistory) && count($paymentHistory) > 0): ?>
                                <!-- DEBUG: Payment History Count -->
                                <!-- <?= '<!-- DEBUG: Found ' . count($paymentHistory) . ' payment records -->' ?> -->
                                <!-- Tabel History Pembayaran -->
                                <div id="paymentInfoTable" style="margin-top: 24px; padding: 16px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #1a73e8;">
                                    <h6 style="color: #1a73e8; font-weight: 600; margin-bottom: 12px;">Riwayat Pembayaran</h6>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: #e3f2fd;">
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">No</th>
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">Metode Pembayaran</th>
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">Kode Pembayaran</th>
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">Status</th>
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($paymentHistory as $payment): ?>
                                                <tr>
                                                    <td style="padding: 12px; border-bottom: 1px solid #e0e7ef; font-size: 14px;"><?= $no++ ?></td>
                                                    <td style="padding: 12px; border-bottom: 1px solid #e0e7ef; font-size: 14px;">
                                                        <?= esc($payment['payment_method']) ?>
                                                    </td>
                                                    <td style="padding: 12px; border-bottom: 1px solid #e0e7ef; font-size: 14px; font-weight: 600; color: #1a73e8;">
                                                        <?php
                                                        if (!empty($payment['payment_code'])) {
                                                            echo esc($payment['payment_code']);
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td style="padding: 12px; border-bottom: 1px solid #e0e7ef;">
                                                        <?php
                                                        $statusBadge = '';
                                                        $status = strtolower($payment['status'] ?? 'pending');
                                                        if ($status === 'paid' || $status === 'success') {
                                                            $statusBadge = '<span style="background: #4caf50; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Lunas</span>';
                                                        } elseif ($status === 'expired') {
                                                            $statusBadge = '<span style="background: #9e9e9e; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Expired</span>';
                                                        } elseif ($status === 'cancelled') {
                                                            $statusBadge = '<span style="background: #f44336; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Dibatalkan</span>';
                                                        } else {
                                                            $statusBadge = '<span style="background: #ff9800; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Pending</span>';
                                                        }
                                                        echo $statusBadge;
                                                        ?>
                                                    </td>
                                                    <td style="padding: 12px; border-bottom: 1px solid #e0e7ef;">
                                                        <?php
                                                        // Extract payment_url from response_data
                                                        $responseData = json_decode($payment['response_data'] ?? '{}', true);
                                                        $paymentUrl = $responseData['payment_url'] ?? null;
                                                        ?>
                                                        <?php if (!empty($paymentUrl) && $status === 'pending'): ?>
                                                            <button class="btn btn-sm btn-primary" onclick="window.open('<?= esc($paymentUrl) ?>', '_blank')">
                                                                <i class="fas fa-credit-card"></i> Bayar
                                                            </button>
                                                        <?php elseif ($status === 'paid' || $status === 'success'): ?>
                                                            <span style="color: #4caf50;"><i class="fas fa-check-circle"></i> Dibayar</span>
                                                        <?php else: ?>
                                                            <span style="color: #9e9e9e;">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php elseif (!empty($invoice['payment_method'])): ?>
                                <!-- Fallback: Tabel Informasi Pembayaran dari invoice (untuk backward compatibility) -->
                                <div id="paymentInfoTable" style="margin-top: 24px; padding: 16px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #1a73e8;">
                                    <h6 style="color: #1a73e8; font-weight: 600; margin-bottom: 12px;">Informasi Pembayaran</h6>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: #e3f2fd;">
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">Aksi</th>
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">Metode Pembayaran</th>
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">Kode Pembayaran</th>
                                                <th style="padding: 10px; text-align: left; font-size: 14px; border-bottom: 2px solid #1a73e8;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="padding: 12px; border-bottom: 1px solid #e0e7ef;">
                                                    <?php if (!empty($invoice['payment_url'])): ?>
                                                        <button class="btn btn-sm btn-primary" onclick="window.open('<?= esc($invoice['payment_url']) ?>', '_blank')">
                                                            <i class="fas fa-credit-card"></i> Bayar Sekarang
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-primary" onclick="window.open('<?= base_url('payment/process/' . $invoice['id']) ?>', '_blank')">
                                                            <i class="fas fa-credit-card"></i> Bayar via Flip
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e0e7ef; font-size: 14px;">
                                                    <?= esc($invoice['payment_method']) ?>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e0e7ef; font-size: 14px; font-weight: 600; color: #1a73e8;">
                                                    <?php
                                                    if (!empty($invoice['payment_code'])) {
                                                        echo esc($invoice['payment_code']);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e0e7ef;">
                                                    <?php
                                                    $statusBadge = '';
                                                    $status = strtolower($invoice['status'] ?? 'unpaid');
                                                    if ($status === 'paid' || $status === 'lunas') {
                                                        $statusBadge = '<span style="background: #4caf50; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Lunas</span>';
                                                    } else {
                                                        $statusBadge = '<span style="background: #ff9800; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Pending</span>';
                                                    }
                                                    echo $statusBadge;
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <!-- Placeholder untuk tabel yang akan muncul setelah set payment -->
                                <div id="paymentInfoPlaceholder"></div>
                            <?php endif; ?>

                            <table style="width:100%; margin-top:32px;">
                                <tr>
                                    <td style="width:50%; text-align:center; font-size:15px;">Penyetor,<br><br><br>( <?= esc(isset($customer['nama_pelanggan']) ? $customer['nama_pelanggan'] : (isset($invoice['customer_name']) ? $invoice['customer_name'] : 'Nama Pelanggan')) ?> )</td>
                                    <td style="width:50%; text-align:center; font-size:15px;">Penerima,<br><br><br>( OFFICE ) </td>
                                </tr>
                            </table>

                            <table style="width:100%; margin-top:2px; border-top:1px solid #e3e8ee; padding-top:1px; font-size:15px; color:#444; border-spacing:0; border-collapse:separate;">
                                <tr>
                                    <td style="vertical-align:top; width:60%; padding-top:10px;">
                                        <span style="font-weight:600; color:#1a73e8; font-size:15px;">Rekening pembayaran transfer Bank :</span>
                                        <?php if (!empty($activeBanks)): ?>
                                            <ul style="margin:6px 0 0 0; padding-left:18px; color:#222; font-size:14px;">
                                                <?php foreach ($activeBanks as $bank): ?>
                                                    <li>
                                                        <b><?= esc(strtoupper($bank['bank_name'])) ?></b> - <?= esc($bank['account_number']) ?> a.n. <?= esc($bank['account_holder']) ?>
                                                    </li>
                                                <?php endforeach ?>
                                            </ul>
                                        <?php else: ?>
                                            <div style="color:#e53935;">Tidak ada rekening bank aktif.</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="vertical-align:top; width:40%; text-align:right; font-size:13px; color:#444; padding-top:10px;">
                                        <div>Pembayaran Online bisa mengunakan <b>QRIS, VIRTUAL AKUN, INDOMARET</b> dan <b>ALFAMART</b></div>
                                        <div>Kunjungi <a href="<?= base_url('/' . ($invoice['nomor_layanan'] ?? '')) ?>" target="_blank" style="color:#1a73e8; text-decoration:underline; word-break:break-all; font-weight:500;">
                                                <?= base_url('/' . ($invoice['nomor_layanan'] ?? '')) ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div> <!-- End area-1 -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Setting Invoice -->
<div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="settingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="settingModalLabel">Setting Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="invoiceSettingsForm">
                    <!-- TAMPILKAN EMAIL DI HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label mb-0">TAMPILKAN EMAIL DI HEADER</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showEmailHeader" checked>
                        </div>
                    </div>

                    <!-- TAMPILKAN KECEPATAN INTERNET DI PAKET -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label mb-0">TAMPILKAN KECEPATAN INTERNET DI PAKET</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showInternetSpeed" checked>
                        </div>
                    </div>

                    <!-- TAMPILKAN NO REKENING DI FOOTER -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label mb-0">TAMPILKAN NO REKENING DI FOOTER</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showAccountNumber" checked>
                        </div>
                    </div>

                    <!-- TAMPILKAN PESAN PAYMENT GATEWAY -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label mb-0">TAMPILKAN PESAN PAYMENT GATEWAY</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showPaymentGateway" checked>
                        </div>
                    </div>

                    <!-- TEBALKAN TEXT LUNAS -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label mb-0">TEBALKAN TEXT LUNAS</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="boldPaidText">
                        </div>
                    </div>

                    <!-- PESAN TAMBAHAN DI FOOTER -->
                    <div class="mb-3">
                        <label for="footerMessage" class="form-label">PESAN TAMBAHAN DI FOOTER</label>
                        <textarea class="form-control" id="footerMessage" rows="3" placeholder="Keterangan"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" onclick="saveInvoiceSettings()">SIMPAN</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('javascript') ?>
<script type="text/javascript">
    console.log('Invoice JavaScript section loading...');

    let printCharacteristic;

    // Function untuk Print HVS
    function printDiv(divId) {
        console.log('printDiv called with:', divId);
        const printContent = document.getElementById(divId);
        if (!printContent) {
            alert('Konten tidak ditemukan!');
            return;
        }

        // Simpan konten asli
        const originalContent = document.body.innerHTML;

        // Buat CSS khusus untuk print - menggunakan string concatenation untuk menghindari template literal
        const printCSS = '<style>' +
            '@media print {' +
            '* { box-sizing: border-box; }' +
            'body { margin: 0; padding: 15px; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.4; color: #000; }' +
            '.no-print { display: none !important; }' +
            '.invoice-content { width: 100%; max-width: none; }' +
            '.header { page-break-inside: avoid; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #000; display: flex; justify-content: space-between; align-items: center; }' +
            '.company-info { text-align: center; }' +
            '.invoice-title { text-align: right; }' +
            '.items-table, .info-table { page-break-inside: avoid; margin: 15px 0; }' +
            'table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }' +
            'th, td { border: 1px solid #000; padding: 8px; text-align: left; vertical-align: top; }' +
            'th { background-color: #f0f0f0 !important; font-weight: bold; }' +
            '.status-lunas, .status-belum-lunas { border: 2px solid; padding: 4px 12px; border-radius: 6px; font-weight: bold; display: inline-block; margin-bottom: 10px; }' +
            '.status-lunas { color: #000; background: #f0f0f0; border-color: #000; }' +
            '.status-belum-lunas { color: #000; background: #f0f0f0; border-color: #000; }' +
            'a { color: #000; text-decoration: underline; }' +
            'img { max-height: 60px; }' +
            '}' +
            '@page { margin: 1.5cm; size: A4; }' +
            '</style>';

        // Ganti konten body dengan konten print
        document.body.innerHTML = printCSS + printContent.outerHTML;

        // Print
        window.print();

        // Kembalikan konten asli setelah print
        setTimeout(() => {
            document.body.innerHTML = originalContent;
            // Re-attach event listeners jika diperlukan
            location.reload();
        }, 1000);
    }



    // Function untuk Copy Link Invoice
    async function copytext() {
        const copyText = '<?= base_url('billing/' . ($invoice['service_number'] ?? '')) ?>';
        try {
            await copy(copyText);
            showNotification('info', 'Link berhasil disalin');
        } catch (e) {
            console.error(e);
            showNotification('danger', 'Error copy text');
        }
    }

    function copy(text) {
        return new Promise((resolve, reject) => {
            if (typeof navigator !== "undefined" && typeof navigator.clipboard !== "undefined" && navigator.permissions !== "undefined") {
                const type = "text/plain";
                const blob = new Blob([text], {
                    type
                });
                const data = [new ClipboardItem({
                    [type]: blob
                })];
                navigator.permissions.query({
                    name: "clipboard-write"
                }).then((permission) => {
                    if (permission.state === "granted" || permission.state === "prompt") {
                        navigator.clipboard.write(data).then(resolve, reject).catch(reject);
                    } else {
                        reject(new Error("Permission not granted!"));
                    }
                });
            } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
                var textarea = document.createElement("textarea");
                textarea.textContent = text;
                textarea.style.position = "fixed";
                textarea.style.width = '2em';
                textarea.style.height = '2em';
                textarea.style.padding = 0;
                textarea.style.border = 'none';
                textarea.style.outline = 'none';
                textarea.style.boxShadow = 'none';
                textarea.style.background = 'transparent';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                try {
                    document.execCommand("copy");
                    document.body.removeChild(textarea);
                    resolve();
                } catch (e) {
                    document.body.removeChild(textarea);
                    reject(e);
                }
            } else {
                reject(new Error("None of copying methods are supported by this browser!"));
            }
        });
    }

    // Function untuk kirim ulang
    function kirimulang() {
        const invoiceId = <?= $invoice['id'] ?? 0 ?>;

        if (!invoiceId) {
            showNotification('danger', 'ID invoice tidak valid');
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Mengirim pesan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send request to backend
        fetch('<?= base_url('invoices/resend-whatsapp/') ?>' + invoiceId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();

                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: data.message || 'Pesan WhatsApp berhasil dikirim',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Gagal mengirim pesan WhatsApp',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat mengirim pesan',
                    confirmButtonText: 'OK'
                });
            });
    }

    // Function untuk membuka modal setting
    function setting() {
        if (typeof $ !== 'undefined' && $("#settingstruk").length) {
            $("#settingstruk").modal('show');
        } else if (typeof bootstrap !== 'undefined') {
            const settingModal = new bootstrap.Modal(document.getElementById('settingModal'));
            settingModal.show();
        } else {
            alert('Modal setting tidak tersedia.');
        }
    }

    // Function untuk save setting
    function savesetting() {
        let vpg = document.getElementById("vpg") ? document.getElementById("vpg").checked : false;
        let vemail = document.getElementById("vemail") ? document.getElementById("vemail").checked : false;
        let vbank = document.getElementById("vbank") ? document.getElementById("vbank").checked : false;
        let vpesan = document.getElementById("vpesan") ? document.getElementById("vpesan").value : '';
        let vtbl = document.getElementById("vtbl") ? document.getElementById("vtbl").checked : false;
        let mbps = document.getElementById("mbps") ? document.getElementById("mbps").checked : false;

        // Convert boolean to number
        vpg = vpg ? 1 : 0;
        vemail = vemail ? 1 : 0;
        vbank = vbank ? 1 : 0;
        vtbl = vtbl ? 1 : 0;
        mbps = mbps ? 1 : 0;

        // Close modal
        if (typeof $ !== 'undefined' && $("#settingstruk").length) {
            $("#settingstruk").modal('hide');
        }

        // Save to server (optional - you can implement this)
        var http = new XMLHttpRequest();
        var params = "mbps=" + mbps + "&vpg=" + vpg + "&vemail=" + vemail + "&vbank=" + vbank + "&vpesan=" + encodeURIComponent(vpesan) + "&vtbl=" + vtbl + "&jenis=0";

        // For now, just save to localStorage
        const settings = {
            showEmailHeader: vemail === 1,
            showInternetSpeed: mbps === 1,
            showAccountNumber: vbank === 1,
            showPaymentGateway: vpg === 1,
            boldPaidText: vtbl === 1,
            footerMessage: vpesan
        };

        localStorage.setItem('invoiceSettings', JSON.stringify(settings));
        applyInvoiceSettings(settings);

        showNotification('info', 'Pengaturan berhasil disimpan!');
    }

    // Function untuk load settings dari localStorage
    function loadInvoiceSettings() {
        const settings = JSON.parse(localStorage.getItem('invoiceSettings')) || {
            showEmailHeader: true,
            showInternetSpeed: true,
            showAccountNumber: true,
            showPaymentGateway: true,
            boldPaidText: false,
            footerMessage: ''
        };

        const elements = {
            showEmailHeader: document.getElementById('showEmailHeader'),
            showInternetSpeed: document.getElementById('showInternetSpeed'),
            showAccountNumber: document.getElementById('showAccountNumber'),
            showPaymentGateway: document.getElementById('showPaymentGateway'),
            boldPaidText: document.getElementById('boldPaidText'),
            footerMessage: document.getElementById('footerMessage')
        };

        for (const [key, element] of Object.entries(elements)) {
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = settings[key];
                } else {
                    element.value = settings[key];
                }
            }
        }
    }

    // Function untuk save settings ke localStorage dan apply changes
    function saveInvoiceSettings() {
        console.log('saveInvoiceSettings called');
        const settings = {
            showEmailHeader: document.getElementById('showEmailHeader')?.checked || false,
            showInternetSpeed: document.getElementById('showInternetSpeed')?.checked || false,
            showAccountNumber: document.getElementById('showAccountNumber')?.checked || false,
            showPaymentGateway: document.getElementById('showPaymentGateway')?.checked || false,
            boldPaidText: document.getElementById('boldPaidText')?.checked || false,
            footerMessage: document.getElementById('footerMessage')?.value || ''
        };

        // Save to localStorage
        localStorage.setItem('invoiceSettings', JSON.stringify(settings));

        // Apply settings to current invoice
        applyInvoiceSettings(settings);

        // Close modal
        if (typeof bootstrap !== 'undefined') {
            const settingModal = bootstrap.Modal.getInstance(document.getElementById('settingModal'));
            if (settingModal) {
                settingModal.hide();
            }
        }

        alert('Pengaturan berhasil disimpan!');
    }

    // Function untuk apply settings ke invoice
    function applyInvoiceSettings(settings) {
        // Implementation of settings application
        console.log('Applying settings:', settings);

        // Add your settings application logic here
        // This is just a placeholder for now
    }

    // Bluetooth printing function
    async function dot() {
        console.log('dot (Bluetooth print) called');

        // Check if Web Bluetooth is supported
        if (!navigator.bluetooth) {
            showNotification('danger', 'Web Bluetooth tidak didukung oleh browser ini. Gunakan Chrome/Edge dan pastikan Bluetooth diaktifkan.');
            return;
        }

        try {
            if (printCharacteristic == null) {
                console.log('Mencari perangkat Bluetooth...');

                // Request Bluetooth device with broader filter for better compatibility
                const device = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: [
                        '000018f0-0000-1000-8000-00805f9b34fb',
                        '0000ff00-0000-1000-8000-00805f9b34fb',
                        '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                        '0000180f-0000-1000-8000-00805f9b34fb'
                    ]
                });

                showNotification('info', `Connecting to: ${device.name}`);

                // Connect to device
                const server = await device.gatt.connect();
                console.log('Connected to GATT server');

                // Try different services
                let service;
                try {
                    service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                } catch (e) {
                    try {
                        service = await server.getPrimaryService('0000ff00-0000-1000-8000-00805f9b34fb');
                    } catch (e2) {
                        service = await server.getPrimaryService('49535343-fe7d-4ae5-8fa9-9fafd205e455');
                    }
                }

                // Try different characteristics
                try {
                    printCharacteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                } catch (e) {
                    try {
                        printCharacteristic = await service.getCharacteristic('0000ff02-0000-1000-8000-00805f9b34fb');
                    } catch (e2) {
                        printCharacteristic = await service.getCharacteristic('49535343-8841-43f4-a8d4-ecbe34729bb3');
                    }
                }

                showNotification('success', 'Printer connected successfully!');
            }

            // Send print data
            showNotification('info', 'Printing invoice...');
            await sendPrinterData();
            showNotification('success', 'Invoice printed successfully!');

        } catch (error) {
            console.error('Bluetooth error:', error);
            printCharacteristic = null;

            if (error.name === 'NotFoundError') {
                showNotification('danger', 'Tidak ada perangkat printer Bluetooth yang ditemukan. Pastikan printer sudah dipasangkan dan dalam mode pairing.');
            } else if (error.name === 'SecurityError') {
                showNotification('danger', 'Akses Bluetooth ditolak. Pastikan halaman ini diakses melalui HTTPS.');
            } else if (error.name === 'NetworkError') {
                showNotification('danger', 'Gagal terhubung ke printer. Pastikan printer dalam jangkauan dan aktif.');
            } else {
                showNotification('danger', 'Error Bluetooth: ' + error.message);
            }
        }
    }

    // Function untuk mengirim data ke thermal printer
    async function sendPrinterData() {
        console.log('Sending data to thermal printer...');

        const encoder = new TextEncoder('utf-8');

        // ESC/POS commands
        const ESC = '\x1B';
        const GS = '\x1D';

        // Formatting commands
        const commands = {
            init: ESC + '@', // Initialize printer
            center: ESC + 'a' + String.fromCharCode(1), // Center align
            left: ESC + 'a' + String.fromCharCode(0), // Left align
            right: ESC + 'a' + String.fromCharCode(2), // Right align
            bold: ESC + 'E' + String.fromCharCode(1), // Bold on
            boldOff: ESC + 'E' + String.fromCharCode(0), // Bold off
            small: GS + '!' + String.fromCharCode(0), // Normal size
            large: GS + '!' + String.fromCharCode(17), // Double size
            underline: ESC + '-' + String.fromCharCode(1), // Underline on
            underlineOff: ESC + '-' + String.fromCharCode(0), // Underline off
            cut: GS + 'V' + String.fromCharCode(0), // Paper cut
            lineFeed: '\n'
        };

        try {
            // Initialize printer
            await printCharacteristic.writeValue(encoder.encode(commands.init));
            await delay(100);

            // Company header
            const companyName = "<?= addslashes(strtoupper(getCompanyData()['name'] ?? 'PT. KIMONET DIGITAL SYNERGY')) ?>";
            const companyTagline = "<?= addslashes(getCompanyData()['tagline'] ?? 'Dari Kita, Untuk Konektivitas Nusantara') ?>";
            const companyAddress = "<?= addslashes(getCompanyData()['address'] ?? 'Dusun Lebo Kulon Rt02/rw08') ?>";
            const companyPhone = "Telp: <?= addslashes(getCompanyData()['phone'] ?? '085183112127') ?>";
            const companyCity = "<?= addslashes(getCompanyData()['city'] ?? 'Batang') ?>";
            const companyWebsite = "<?= addslashes(getCompanyData()['website'] ?? 'www.kimonet.my.id') ?>";

            await writeAndDelay(commands.center + commands.bold + companyName + commands.boldOff + commands.lineFeed);
            await writeAndDelay(commands.center + companyTagline + commands.lineFeed);
            await writeAndDelay(commands.center + companyPhone + commands.lineFeed);
            await writeAndDelay(commands.center + companyAddress + commands.lineFeed);
            await writeAndDelay(commands.center + companyCity + ' - ' + companyWebsite + commands.lineFeed);
            await writeAndDelay(commands.lineFeed);

            // Invoice title
            await writeAndDelay(commands.center + commands.bold + 'BUKTI PEMBAYARAN' + commands.boldOff + commands.lineFeed);
            await writeAndDelay(commands.center + '================================' + commands.lineFeed);
            await writeAndDelay(commands.lineFeed);

            // Invoice details
            const currentDate = new Date().toLocaleDateString('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            await writeAndDelay(commands.left + 'Tanggal  : ' + currentDate + commands.lineFeed);
            await writeAndDelay('No. Pel  : <?= esc($invoice['customer_no'] ?? "TEST001") ?>' + commands.lineFeed);
            await writeAndDelay('Nama     : <?= esc($invoice['customer_name'] ?? "Test Customer") ?>' + commands.lineFeed);
            await writeAndDelay('Telp     : <?= esc($invoice['customer_phone'] ?? "085183112127") ?>' + commands.lineFeed);
            await writeAndDelay('Paket    : <?= esc($invoice['package'] ?? "Test Package") ?>' + commands.lineFeed);
            await writeAndDelay('Periode  : <?= strtoupper(date("F Y", strtotime(($invoice['periode'] ?? "2024-10") . "-01"))) ?>' + commands.lineFeed);
            await writeAndDelay('Pemakaian: <?= esc($invoice['usage_period'] ?? "1 Bulan") ?>' + commands.lineFeed);
            await writeAndDelay(commands.lineFeed);

            // Billing details
            await writeAndDelay('--------------------------------' + commands.lineFeed);
            await writeAndDelay('Tarif/Bln: Rp <?= number_format(isset($invoice['bill']) ? (float)$invoice['bill'] : 100000, 0, ",", ".") ?>' + commands.lineFeed);
            await writeAndDelay(commands.bold + 'Total Tag: Rp <?= number_format(isset($invoice['bill']) ? (float)$invoice['bill'] : 100000, 0, ",", ".") ?>' + commands.boldOff + commands.lineFeed);
            await writeAndDelay('--------------------------------' + commands.lineFeed);
            await writeAndDelay(commands.lineFeed);

            // Payment status
            const paymentStatus = '<?= ($invoice['status'] ?? "unpaid") === "paid" || ($invoice['status'] ?? "unpaid") === "lunas" ? "LUNAS" : "BELUM LUNAS" ?>';
            await writeAndDelay('Status   : ' + commands.bold + paymentStatus + commands.boldOff + commands.lineFeed);
            await writeAndDelay(commands.lineFeed);

            // Notes
            const notes = '<?= esc($invoice['keterangan'] ?? "-") ?>';
            if (notes && notes !== '-') {
                await writeAndDelay('Catatan  : ' + notes + commands.lineFeed);
                await writeAndDelay(commands.lineFeed);
            }

            // Signature section
            await writeAndDelay(commands.left + 'Penyetor:' + commands.right + 'Penerima:' + commands.lineFeed);
            await writeAndDelay(commands.lineFeed + commands.lineFeed);
            await writeAndDelay(commands.left + '(<?= esc($invoice['customer_name'] ?? "Customer") ?>)' + commands.right + '(OFFICE)' + commands.lineFeed);
            await writeAndDelay(commands.lineFeed);

            // Bank account information
            await writeAndDelay(commands.left + commands.small + 'Rekening Transfer Bank:' + commands.lineFeed);
            await writeAndDelay('BCA: 1234567890 - PT KIMONET' + commands.lineFeed);
            await writeAndDelay('MANDIRI: 0987654321 - PT KIMONET' + commands.lineFeed);
            await writeAndDelay(commands.lineFeed);

            // Online payment info
            await writeAndDelay('Pembayaran Online:' + commands.lineFeed);
            await writeAndDelay('QRIS, VIRTUAL AKUN,' + commands.lineFeed);
            await writeAndDelay('INDOMARET, ALFAMART' + commands.lineFeed);
            await writeAndDelay('<?= base_url('billing/' . ($invoice->service_number ?? '')) ?>' + commands.lineFeed);
            await writeAndDelay(commands.lineFeed);

            // Footer
            await writeAndDelay(commands.center + 'Terima Kasih' + commands.lineFeed);
            await writeAndDelay(commands.lineFeed + commands.lineFeed + commands.lineFeed);

            // Cut paper
            await printCharacteristic.writeValue(encoder.encode(commands.cut));

            console.log('Print completed successfully');
            alert('Invoice berhasil dicetak ke thermal printer!');

        } catch (error) {
            console.error('Print error:', error);
            throw error;
        }
    }

    // Helper function untuk delay
    function delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // Helper function untuk write data dengan delay
    async function writeAndDelay(data) {
        const encoder = new TextEncoder('utf-8');
        await printCharacteristic.writeValue(encoder.encode(data));
        await delay(50); // Small delay between writes
    }

    // Function untuk handle error
    function handleBluetoothError(error) {
        console.error('Bluetooth error:', error);
        printCharacteristic = null;

        let errorMessage = 'Terjadi kesalahan saat mencetak: ';

        switch (error.name) {
            case 'NotFoundError':
                errorMessage += 'Printer tidak ditemukan. Pastikan printer Bluetooth sudah dipasangkan.';
                break;
            case 'SecurityError':
                errorMessage += 'Akses ditolak. Pastikan menggunakan HTTPS.';
                break;
            case 'NetworkError':
                errorMessage += 'Gagal terhubung ke printer. Periksa koneksi Bluetooth.';
                break;
            case 'InvalidStateError':
                errorMessage += 'Printer dalam kondisi tidak valid. Coba restart printer.';
                break;
            default:
                errorMessage += error.message;
        }

        alert(errorMessage);
    }

    // Function untuk Download PDF
    function downloadPDF() {
        // Download PDF dengan jsPDF, layout custom
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF({
            unit: 'mm',
            format: 'a4',
            orientation: 'portrait'
        });

        // Data
        const customerName = '<?= esc($invoice->customer_name ?? "Customer") ?>';
        const periode = '<?= strtoupper(date("F Y", strtotime(($invoice->periode ?? "2024-10") . "-01"))) ?>';
        const invoiceNo = '<?= esc(isset($invoice['invoice_no']) ? $invoice['invoice_no'] : '') ?>';
        const companyName = '<?= esc((!empty($company['name']) ? $company['name'] : 'PT. KIMONET DIGITAL SYNERGY')) ?>';
        const companyAddress = '<?= esc(!empty($company['address']) ? $company['address'] : 'Dusun Lebo Kulon Rt02/rw08') ?>';
        const companyPhone = '<?= esc(!empty($company['phone']) ? $company['phone'] : '085183112127') ?>';
        const companyWebsite = '<?= esc(!empty($company['website']) ? $company['website'] : 'www.kimonet.my.id') ?>';
        const package = '<?= esc(isset($invoice['package']) ? $invoice['package'] : '') ?>';
        const bill = 'Rp <?= isset($invoice['bill']) ? number_format((float)$invoice['bill'], 0, ',', '.') : '0' ?>';
        const usage = '<?= esc(isset($invoice['usage_period']) ? $invoice['usage_period'] : '') ?>';
        const grandTotal = 'Rp <?= number_format(((float)(isset($invoice['bill']) ? $invoice['bill'] : 100000) + (float)($invoice['additional_fee'] ?? 0) - (float)($invoice['discount'] ?? 0)), 0, ',', '.') ?>';
        const bankInfo = 'BRI - 374301021214532 a.n. afik rofikan';
        const paymentUrl = '<?= base_url('billing/' . ($invoice['service_number'] ?? '')) ?>';

        // Header
        doc.setFontSize(18);
        doc.setTextColor(26, 115, 232);
        doc.text(companyName, 15, 20);
        doc.setFontSize(10);
        doc.setTextColor(44, 58, 74);
        doc.text(companyAddress + ', Telp ' + companyPhone, 15, 26);
        doc.text(companyWebsite, 15, 32);

        doc.setFontSize(14);
        doc.setTextColor(26, 115, 232);
        doc.text('Invoice #' + invoiceNo, 150, 20, {
            align: 'right'
        });
        doc.setFontSize(10);
        doc.setTextColor(44, 58, 74);
        doc.text('Periode: ' + periode, 150, 26, {
            align: 'right'
        });

        // Info
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text('Kepada:', 15, 42);
        doc.text(customerName, 15, 48);

        // Tabel
        doc.setFontSize(11);
        doc.setTextColor(26, 115, 232);
        doc.text('No', 15, 60);
        doc.text('Deskripsi', 30, 60);
        doc.text('Tarif', 90, 60);
        doc.text('Pemakaian', 120, 60);
        doc.text('Total', 160, 60);
        doc.setTextColor(0, 0, 0);
        doc.text('1', 15, 68);
        doc.text(package, 30, 68);
        doc.text(bill, 90, 68);
        doc.text(usage, 120, 68);
        doc.text(bill, 160, 68);

        // Grand Total
        doc.setFontSize(13);
        doc.setTextColor(26, 115, 232);
        doc.text('Grand Total', 90, 80);
        doc.text(grandTotal, 160, 80);

        // Bank info
        doc.setFontSize(10);
        doc.setTextColor(26, 115, 232);
        doc.text('Rekening pembayaran transfer Bank:', 15, 95);
        doc.setTextColor(44, 58, 74);
        doc.text(bankInfo, 15, 100);

        // Payment URL
        doc.setFontSize(10);
        doc.setTextColor(26, 115, 232);
        doc.text('Pembayaran Online:', 15, 110);
        doc.setTextColor(44, 58, 74);
        doc.text(paymentUrl, 15, 115);

        // Download
        doc.save(periode + '-' + customerName + '.pdf');
    }

    // Auto-load settings saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Invoice functions ready');
        console.log('Available functions:', {
            printDiv: typeof printDiv,
            dot: typeof dot,
            downloadPDF: typeof downloadPDF,
            copytext: typeof copytext,
            kirimulang: typeof kirimulang,
            setting: typeof setting
        });

        // Load saved settings if available
        try {
            const savedSettings = JSON.parse(localStorage.getItem('invoiceSettings'));
            if (savedSettings) {
                applyInvoiceSettings(savedSettings);
            }
        } catch (e) {
            console.log('No saved settings found or error loading settings');
        }

        // Initialize Payment Gateway Selection
        initPaymentGateway();
    });

    // Payment Gateway Functions
    function initPaymentGateway() {
        console.log('Initializing Payment Gateway...');

        // Payment Gateway Selection
        $('#btnSelectPayment').on('click', function(e) {
            e.preventDefault();
            console.log('Payment button clicked');
            var invoiceId = $(this).data('invoice-id');

            // Load active payment gateways
            $.ajax({
                url: '<?= base_url('payment/get-active-gateways') ?>',
                type: 'GET',
                success: function(response) {
                    console.log('Payment gateways loaded:', response);
                    if (response.status === 'success' && response.data.length > 0) {
                        var options = '';
                        response.data.forEach(function(method) {
                            options += '<option value="' + method.gateway_id + '" ' +
                                'data-method="' + method.method_code + '" ' +
                                'data-name="' + method.display_name + '">' +
                                method.display_name + '</option>';
                        });
                        $('#paymentMethodSelect').html(options);
                        $('#paymentModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ada Metode Pembayaran',
                            text: 'Belum ada payment gateway yang aktif. Silakan aktifkan payment gateway terlebih dahulu.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat metode pembayaran. ' + (xhr.responseJSON?.message || error)
                    });
                }
            });
        });

        // Handle Set Payment
        $('#btnSetPayment').on('click', function(e) {
            e.preventDefault();
            console.log('Set payment button clicked');
            var invoiceId = $('#btnSelectPayment').data('invoice-id');
            var gatewayId = $('#paymentMethodSelect').val();
            var gatewayName = $('#paymentMethodSelect option:selected').data('name');
            var sendNotif = $('#sendNotification').is(':checked');

            if (!gatewayId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Metode',
                    text: 'Silakan pilih metode pembayaran terlebih dahulu'
                });
                return;
            }

            var selectedOption = $('#paymentMethodSelect option:selected');
            var methodCode = selectedOption.data('method');
            var methodName = selectedOption.data('name');

            // Process payment
            $.ajax({
                url: '<?= base_url('payment/set-method') ?>',
                type: 'POST',
                data: {
                    invoice_id: invoiceId,
                    gateway_id: gatewayId,
                    method_code: methodCode,
                    method_name: methodName,
                    send_notification: sendNotif ? 1 : 0,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    console.log('Payment set response:', response);
                    if (response.status === 'success') {
                        $('#paymentModal').modal('hide');

                        // Reload halaman untuk menampilkan payment history yang baru
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Metode pembayaran berhasil diset!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Set payment AJAX error:', {
                        xhr: xhr,
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memproses pembayaran'
                    });
                }
            });
        });

        console.log('Payment Gateway initialized successfully!');
    }

    // Notification system
    function showNotification(type, message) {
        console.log('Notification [' + type + ']: ' + message);

        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'alert alert-' + type + ' alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);';

        notification.innerHTML = '<i class="fas fa-' + getNotificationIcon(type) + '"></i>' +
            '<span class="ms-2">' + message + '</span>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';

        // Add to body
        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(function() {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(function() {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 150);
            }
        }, 5000);
    }

    function getNotificationIcon(type) {
        switch (type) {
            case 'success':
                return 'check-circle';
            case 'danger':
            case 'error':
                return 'exclamation-circle';
            case 'warning':
                return 'exclamation-triangle';
            case 'info':
                return 'info-circle';
            default:
                return 'bell';
        }
    }

    console.log('Invoice JavaScript section loaded successfully!');
</script>

<!-- Payment Method Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="bx bx-credit-card me-2"></i>Pilih Metode Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="paymentMethodSelect" class="form-label">Metode Pembayaran</label>
                    <select class="form-select" id="paymentMethodSelect">
                        <option value="">Pilih metode pembayaran...</option>
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="sendNotification" checked>
                    <label class="form-check-label" for="sendNotification">
                        Kirim notifikasi ke pelanggan
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSetPayment">
                    <i class="bx bx-check me-1"></i>Set Payment Method
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>