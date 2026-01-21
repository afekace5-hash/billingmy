<?php

/**
 * View: billing_detail.php
 * Tampilkan detail tagihan pelanggan (public)
 * Variabel yang dibutuhkan: $customer, $unpaidInvoices, $activeGateways
 */
?>
<div class="mb-3">
    <label class="form-label fw-semibold text-dark mb-1">Nomor Pelanggan</label>
    <input type="text" class="form-control bg-light" value="<?= esc($customer['nomor_layanan']) ?>" readonly>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold text-dark mb-1">Nama</label>
    <input type="text" class="form-control bg-light" value="<?= esc($customer['nama_pelanggan']) ?>" readonly>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold text-dark mb-1">Telp</label>
    <input type="text" class="form-control bg-light" value="<?= esc($customer['nomor_whatsapp']) ?>" readonly>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold text-dark mb-1">Paket</label>
    <input type="text" class="form-control bg-light" value="<?= esc($customer['package_profile_name'] ?? '-') ?>" readonly>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold text-dark mb-1">Tarif</label>
    <input type="text" class="form-control bg-light" value="Rp <?= number_format($customer['tarif'], 0, ',', '.') ?>" readonly>
</div>
<?php if (!empty($unpaidInvoices) && isset($unpaidInvoices[0])): ?>
    <div class="mb-3">
        <label class="form-label fw-semibold text-dark mb-1">Periode Pembayaran</label>
        <input type="text" class="form-control bg-light" value="<?= date('M Y', strtotime($unpaidInvoices[0]['periode'])) ?>" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold text-dark mb-1">Total Tagihan</label>
        <input type="text" class="form-control bg-light fw-bold text-dark" value="Rp <?= number_format($unpaidInvoices[0]['bill'] ?? 0, 0, ',', '.') ?>" readonly>
    </div>
    <?php
    // Double-check invoice status before showing payment button
    $invoiceStatus = $unpaidInvoices[0]['status'] ?? 'unpaid';
    $transactionId = $unpaidInvoices[0]['transaction_id'] ?? null;
    // Bersihkan transaction_id jika masih null/empty
    $transactionId = !empty($transactionId) && $transactionId !== '' ? $transactionId : null;

    if ($invoiceStatus === 'unpaid'):
    ?>
        <button onclick="payInvoice(<?= $unpaidInvoices[0]['id'] ?>)" class="btn btn-lg w-100 fw-bold text-white border-0 shadow mb-2" style="background: linear-gradient(45deg, #667eea, #764ba2); padding:14px; border-radius:12px; font-size:1.1rem; letter-spacing:0.5px; transition: all 0.3s ease;">
            KLIK DISINI UNTUK MEMBAYAR
        </button>

        <?php if ($transactionId): ?>
            <button onclick="checkPaymentStatus('<?= esc($transactionId) ?>')" class="btn btn-outline-info w-100 mb-2">
                <i class="mdi mdi-refresh me-1"></i> Cek Status Pembayaran
            </button>
            <button onclick="forceRefresh()" class="btn btn-outline-secondary w-100 mb-3">
                <i class="mdi mdi-cached me-1"></i> Refresh Halaman
            </button>
            <div class="alert alert-info small mb-3">
                <i class="mdi mdi-information-outline me-1"></i>
                <strong>Sudah bayar tapi masih muncul tombol ini?</strong><br>
                Klik "<strong>Cek Status Pembayaran</strong>" atau "<strong>Refresh Halaman</strong>" untuk memperbarui data terbaru dari server.
            </div>
        <?php else: ?>
            <p class="small text-muted text-center mb-3">
                <i class="mdi mdi-information-outline"></i> Setelah klik tombol bayar dan selesai pembayaran, kembali ke halaman ini untuk cek status
            </p>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-success text-center mb-3">
            <i class="mdi mdi-check-circle h1 mb-2 text-success"></i>
            <h5 class="alert-heading">Pembayaran Berhasil!</h5>
            <p class="mb-0">Tagihan periode <strong><?= date('M Y', strtotime($unpaidInvoices[0]['periode'])) ?></strong> telah lunas.</p>
            <hr>
            <p class="mb-0 small text-muted">Terima kasih atas pembayaran Anda!</p>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-success text-center mb-3">
        <i class="mdi mdi-check-circle-outline h1 mb-2 text-success"></i>
        <h5 class="alert-heading">Selamat!</h5>
        <p class="mb-2">Tidak ada tagihan yang belum dibayar.</p>
        <hr>
        <p class="mb-0 small text-muted">Semua tagihan Anda sudah lunas. Terima kasih!</p>
    </div>
    <button onclick="forceRefresh()" class="btn btn-primary w-100 mb-3">
        <i class="mdi mdi-refresh me-1"></i> Refresh Data
    </button>
<?php endif; ?>
<a href="<?= base_url('cek-tagihan') ?>" class="btn btn-outline-secondary w-100 mt-2">Kembali ke Pencarian</a>
<div class="mt-3 text-muted" style="font-size:0.95rem;">
    <b>Catatan:</b><br>
    Lakukan sebelum tanggal 10 setiap bulan untuk menghindari isolir otomatis
</div>