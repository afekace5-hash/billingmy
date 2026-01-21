<?= $this->extend('layout/default') ?>
<?= $this->section('title') ?>
<title>Template Message &mdash; WhatsApp Gateway</title>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Template Message</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">WhatsApp Gateway</a></li>
                            <li class="breadcrumb-item active">Template Message</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Info Notification -->
        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bx bx-info-circle me-2"></i>
                <?= esc(session()->getFlashdata('info')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Warning Notification -->
        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bx bx-error me-2"></i>
                <?= esc(session()->getFlashdata('warning')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Template System Status Info -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Info Template WhatsApp:</strong>
            <ul class="mb-0 mt-2">
                <li>Template akan disimpan otomatis ke database saat klik tombol "Simpan"</li>
                <li>Gunakan variable dalam kurung kurawal <code>{variable}</code> untuk data dinamis</li>
                <li>Status koneksi WhatsApp:
                    <span class="badge bg-success">
                        <i class="bx bx-check-circle"></i> Connected
                    </span>
                </li>
                <li>Total template tersimpan: <strong>5 Template</strong></li>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- end page title -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 text-left">
                                <a href="<?= site_url('whatsapp') ?>" class="btn btn-secondary waves-effect btn-label waves-light">
                                    <i class="mdi mdi-qrcode-scan label-icon"></i>
                                    Initial Setting
                                </a>
                                <a href="<?= site_url('whatsapp/reset') ?>" class="btn btn-danger waves-effect btn-label waves-light">
                                    <i class="bx bx-reset label-icon"></i>
                                    Reset WhatsApp
                                </a>
                            </div>
                            <div id="diplayMenu" class="text-md-right text-lg-end col-md-6 diplayMenu">

                                <a href="<?= site_url('whatsapp/message/blast') ?>" class="btn btn-secondary waves-effect btn-label waves-light">
                                    <i class="bx bx-user-voice label-icon"></i>
                                    Blast
                                </a> <a href="<?= site_url('whatsapp/template/message') ?>" class="btn btn-success waves-effect btn-label waves-light">
                                    <i class="bx bx-file-blank label-icon"></i>
                                    Template
                                </a>

                                <a href="<?= site_url('whatsapp/info') ?>" class="btn btn-info waves-effect btn-label waves-light">
                                    <i class="bx bx-info-circle label-icon"></i>
                                    Info
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <form action="<?= site_url('whatsapp/template-message/send') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="card-body">
                            <h4>Template WhatsApp Message</h4>
                            <p class="card-title-desc">Semua kata dengan <code>Curly bracket</code> atau symbol seperti ini <code>{}</code> tidak boleh dihapus</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-xl-12 col-md-12 col-sm-12">
                                        <div class="mb-3">
                                            <label for="bill_reminder" class="col-form-label">Notif Pengingat Tagihan<span class="text-danger">*</span></label>
                                            <textarea class="form-control "
                                                name="bill_reminder" id="bill_reminder" cols="30"
                                                rows="8"><?= isset($templates['bill_reminder']) ? esc($templates['bill_reminder']) : '```{company}```

_Halo {customer},_
Tagihan Anda jatuh tempo pada:

*Tanggal*: {tanggal}
*Total Tagihan*: {tagihan}
*Periode*: {periode}

{bank_data}

{link_payment}

_Abaikan pesan ini jika Anda sudah melakukan pembayaran_

*Terima kasih*' ?></textarea>
                                            <div class="mt-2">
                                                <label for="bill_reminder_image" class="form-label">Gambar untuk Notif Pengingat Tagihan (opsional)</label>
                                                <input type="file" class="form-control" name="bill_reminder_image" id="bill_reminder_image" accept="image/*">
                                                <?php if (!empty($templates['bill_reminder_image'])): ?>
                                                    <div class="mt-2">
                                                        <img src="<?= base_url('uploads/' . $templates['bill_reminder_image']) ?>" alt="Gambar Notif Tagihan" style="max-width: 150px; max-height: 150px;" onerror="this.onerror=null;this.src='<?= base_url('no-image.png') ?>';">
                                                        <div><small>Gambar saat ini</small></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <span class="card-title-desc"><code>Variable yang bisa dipakai untuk notif ini :</code></span>
                                            <br> <span class="card-title-desc"><code>{company}, {customer}, {tanggal}, {tagihan}, {periode}, {no_invoice}, {paket}, {village}, {district}, {city}, {adderss}</code></span>
                                            <br>
                                            <span class="card-title-desc"><code>{bank_data}</code> adalah yang ada pada pengaturan <a href="<?= site_url('settings/bank-accounts') ?>">Banks</a> </span>
                                            <br>
                                            <span class="card-title-desc"><code>{link_payment}</code> akan tampil jika semua setup payment gateway sudah di isi <a href="<?= site_url('settings/payment') ?>">Settings</a> </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-xl-12 col-md-12 col-sm-12">
                                        <div class="mb-3">
                                            <label for="bill_paid" class="col-form-label">Notif Setelah Pembayaran Tagihan<span
                                                    class="text-danger">*</span></label> <textarea class="form-control "
                                                name="bill_paid" id="bill_paid" cols="30"
                                                rows="8"><?= isset($templates['bill_paid']) ? esc($templates['bill_paid']) : '```{company}```

                                                _Halo {customer},_

                                                Terima kasih sudah melakukan pembayaran

                                                *No Invoice*: {no_invoice}
                                                *Tanggal*: {tanggal}
                                                *Jumlah pembayaran*: {total}
                                                *Tunggakan*: {tunggakan}
                                                *Periode*: {periode}


                                                *Terima kasih*' ?></textarea>
                                            <span class="card-title-desc"><code>Variable yang bisa dipakai untuk notif ini :</code></span>
                                            <br>
                                            <span class="card-title-desc"><code>{company}, {customer}, {tanggal}, {total}, {tunggakan}, {periode}, {no_invoice}, {no_layanan}, {metode_pembayaran}</code></span>
                                            <br>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-xl-12 col-md-12 col-sm-12">
                                        <div class="mb-3">
                                            <label for="new_customer" class="col-form-label">Notif Untuk Pelanggan Baru<span
                                                    class="text-danger">*</span></label> <textarea class="form-control "
                                                name="new_customer" id="new_customer" cols="30"
                                                rows="8"><?= isset($templates['new_customer']) ? esc($templates['new_customer']) : '```{company}```

_Halo {customer},_

Terima kasih sudah menjadi pelanggan kami :

Paket : {paket},
Harga : {harga},
Bandwidth : {bandwidth},
Tanggal Jatuh tempo :  {tanggal}

jika ada kendala silahkan hubungi kami


*Terima kasih*' ?></textarea>
                                            <span class="card-title-desc"><code>Variable yang bisa dipakai untuk notif ini :</code></span>
                                            <br> <span class="card-title-desc"><code>{company}, {customer}, {paket}, {harga}, {bandwidth}, {tanggal}, {no_layanan}, {phone}</code></span>
                                            <br>
                                            <span class="card-title-desc"><code>{tanggal}</code> tentukan tanggal jatuh tempo pada pengaturan ini <a href="<?= site_url('settings/applications') ?>">Aplikasi</a> </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-xl-12 col-md-12 col-sm-12">
                                        <div class="mb-3">
                                            <label for="isolir_reminder" class="col-form-label">Notif Saat Isolir Pelanggan<span
                                                    class="text-danger">*</span></label> <textarea class="form-control "
                                                name="isolir_reminder" id="isolir_reminder" cols="30"
                                                rows="8"><?= isset($templates['isolir_reminder']) ? esc($templates['isolir_reminder']) : '```{company}```

_Halo {customer},_

Layanan Internet Anda telah di isolir otomatis oleh sistem, karena anda belum melakukan pembayaran tagihan.
berikut informasi tagihan Anda :

*Paket* : {paket},
*Total Tagihan*: {tagihan}
*Periode*: {periode}
*Tanggal Jatuh tempo* :  {tanggal}

Info Pembayaran, transfer sesuai tagihan yang tertera pada pesan ini : 

{bank_data}

{link_payment}

atau bisa melakukan pembayaran tagihan secara langsung ke rumah.
_Segera lakukan pembayaran, agar internet Anda bisa digunakan kembali_


*Terima kasih*' ?></textarea>
                                            <span class="card-title-desc"><code>{bank_data}</code> adalah yang ada pada pengaturan <a href="https://wifinetbill.com/settings/bank-accounts">Banks</a> </span>
                                            <br>
                                            <span class="card-title-desc"><code>{link_payment}</code> akan tampil jika semua setup payment gateway sudah di isi <a href="https://wifinetbill.com/settings/payment">Settings</a> </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-xl-12 col-md-12 col-sm-12">
                                        <div class="mb-3">
                                            <label for="isolir_open" class="col-form-label">Notif Saat Pembukaan Isolir Pelanggan<span
                                                    class="text-danger">*</span></label> <textarea class="form-control "
                                                name="isolir_open" id="isolir_open" cols="30"
                                                rows="8"><?= isset($templates['isolir_open']) ? esc($templates['isolir_open']) : '```{company}```

_Halo {customer},_

Terima kasih sudah melakukan pembayaran, sistem telah membuka isolir internet Anda .


*Terima kasih*' ?></textarea>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <hr>
                            </div>
                            <div class="col-lg-4">
                                <input type="submit" class="btn btn-primary" value="Simpan" id="saveBtn">

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->


<script>
    $(document).on({
        ajaxStart: function() {
            $('#myLoading').removeClass("dontDisplay").addClass("d-flex");
        },
        ajaxStop: function() {
            $('#myLoading').removeClass("d-flex").addClass("dontDisplay");
        }
    });

    $(document).ready(function() {
        // Handle form submission with loading indicator
        $('#saveBtn').on('click', function() {
            this.disabled = true;
            this.value = "Menyimpan...";
            showInfoToast('Menyimpan template...', 'Mohon tunggu', 'info');
            this.form.submit();
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Show initial notification about template system
        <?php if (!session()->getFlashdata('success') && !session()->getFlashdata('error')): ?>
            showInfoToast('Template WhatsApp siap digunakan', 'Sistem Template', 'info');
        <?php endif; ?>
    });

    function showInfoToast(message, title, type = 'info') {
        if (typeof Notify !== "undefined") {
            Notify({
                status: type,
                title: title || 'Informasi',
                text: message,
                effect: 'slide',
                speed: 600,
                customClass: '',
                customIcon: '',
                showIcon: true,
                showCloseButton: true,
                autoclose: true,
                autotimeout: 8000,
                gap: 10,
                distance: 10,
                type: 1,
                position: 'right top',
                customWrapper: '',
            });
        } else {
            // Fallback untuk browser yang tidak support
            console.log(`${title}: ${message}`);
        }
    }

    // Show notification for flash messages
    <?php if (session()->getFlashdata('success')): ?>
        showInfoToast('<?= esc(session()->getFlashdata('success')) ?>', 'Berhasil', 'success');
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        showInfoToast('<?= esc(session()->getFlashdata('error')) ?>', 'Error', 'error');
    <?php endif; ?>

    <?php if (session()->getFlashdata('info')): ?>
        showInfoToast('<?= esc(session()->getFlashdata('info')) ?>', 'Informasi', 'info');
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        showInfoToast('<?= esc(session()->getFlashdata('warning')) ?>', 'Peringatan', 'warning');
    <?php endif; ?>
</script>

<?= $this->endSection() ?>