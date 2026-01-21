<!-- app/Views/settings/master_bank.php -->
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="mdi mdi-bank text-primary me-2"></i> Master Bank</h4>
                    </div>
                    <div class="card-body">
                        <p>Halaman pengelolaan data bank untuk pembayaran.</p>
                        <?php if (session('success')): ?>
                            <div class="alert alert-success"> <?= session('success') ?> </div>
                        <?php endif; ?>
                        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalBank">Tambah Bank</button>

                        <!-- Modal Tambah Bank -->
                        <div class="modal fade" id="modalBank" tabindex="-1" aria-labelledby="modalBankLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" action="<?= site_url('settings/master-bank/create') ?>">
                                        <?= csrf_field() ?>
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalBankLabel">Tambah Bank</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Nama Bank</label>
                                                <select name="bank_name" class="form-select" required>
                                                    <option value="">Pilih Bank</option>
                                                    <option value="BANK JATENG">BANK JATENG</option>
                                                    <option value="BANK LAINYA">BANK LAINYA</option>
                                                    <option value="BCA">BCA</option>
                                                    <option value="blu BCA Digital">blu BCA Digital</option>
                                                    <option value="BNI 46">BNI 46</option>
                                                    <option value="BNI Syariah">BNI Syariah</option>
                                                    <option value="BRI">BRI</option>
                                                    <option value="BRI Syariah">BRI Syariah</option>
                                                    <option value="BSI">BSI</option>
                                                    <option value="BTN">BTN</option>
                                                    <option value="Bukopin">Bukopin</option>
                                                    <option value="CIMB">CIMB</option>
                                                    <option value="Jatim Syariah">Jatim Syariah</option>
                                                    <option value="Mandiri">Mandiri</option>
                                                    <option value="Mandiri Syariah">Mandiri Syariah</option>
                                                    <option value="Muamalat">Muamalat</option>
                                                    <option value="PANIN BANK">PANIN BANK</option>
                                                    <option value="SEABANK">SEABANK</option>
                                                    <option value="SINARMAS">SINARMAS</option>
                                                    <option value="TF-DANA">TF-DANA</option>
                                                    <option value="TF-GOPAY">TF-GOPAY</option>
                                                    <option value="TF-OVO">TF-OVO</option>
                                                    <option value="TF-SHOPEEPAY">TF-SHOPEEPAY</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>No. Rekening</label>
                                                <input type="text" name="account_number" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Atas Nama</label>
                                                <input type="text" name="account_holder" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Status</label>
                                                <select name="is_active" class="form-select">
                                                    <option value="1">Aktif</option>
                                                    <option value="0">Nonaktif</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Bank</th>
                                        <th>No. Rekening</th>
                                        <th>Atas Nama</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $bankList = [
                                        'BANK JATENG' => 'BANK JATENG',
                                        'BANK LAINYA' => 'BANK LAINYA',
                                        'BCA' => 'BCA',
                                        'blu BCA Digital' => 'blu BCA Digital',
                                        'BNI 46' => 'BNI 46',
                                        'BNI Syariah' => 'BNI Syariah',
                                        'BRI' => 'BRI',
                                        'BRI Syariah' => 'BRI Syariah',
                                        'BSI' => 'BSI',
                                        'BTN' => 'BTN',
                                        'Bukopin' => 'Bukopin',
                                        'CIMB' => 'CIMB',
                                        'Jatim Syariah' => 'Jatim Syariah',
                                        'Mandiri' => 'Mandiri',
                                        'Mandiri Syariah' => 'Mandiri Syariah',
                                        'Muamalat' => 'Muamalat',
                                        'PANIN BANK' => 'PANIN BANK',
                                        'SEABANK' => 'SEABANK',
                                        'SINARMAS' => 'SINARMAS',
                                        'TF-DANA' => 'TF-DANA',
                                        'TF-GOPAY' => 'TF-GOPAY',
                                        'TF-OVO' => 'TF-OVO',
                                        'TF-SHOPEEPAY' => 'TF-SHOPEEPAY',
                                    ];
                                    ?>
                                    <?php if (!empty($banks)): $no = 1;
                                        foreach ($banks as $bank): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= esc($bankList[$bank['bank_name']] ?? $bank['bank_name']) ?></td>
                                                <td><?= esc($bank['account_number']) ?></td>
                                                <td><?= esc($bank['account_holder']) ?></td>
                                                <td>
                                                    <?php if ($bank['is_active']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditBank"
                                                        data-id="<?= $bank['id'] ?>"
                                                        data-bank_name="<?= esc($bank['bank_name']) ?>"
                                                        data-account_number="<?= esc($bank['account_number']) ?>"
                                                        data-account_holder="<?= esc($bank['account_holder']) ?>"
                                                        data-is_active="<?= $bank['is_active'] ?>">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <a href="<?= site_url('settings/master-bank/delete/' . $bank['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus bank ini?')"><i class="mdi mdi-delete"></i></a>
                                                </td>
                                                <!-- Modal Edit Bank -->
                                                <div class="modal fade" id="modalEditBank" tabindex="-1" aria-labelledby="modalEditBankLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form method="post" id="formEditBank">
                                                                <?= csrf_field() ?>
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="modalEditBankLabel">Edit Bank</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id" id="edit_id">
                                                                    <div class="mb-3">
                                                                        <label>Nama Bank</label>
                                                                        <select name="bank_name" id="edit_bank_name" class="form-select" required>
                                                                            <option value="">Pilih Bank</option>
                                                                            <option value="BANK JATENG">BANK JATENG</option>
                                                                            <option value="BANK LAINYA">BANK LAINYA</option>
                                                                            <option value="BCA">BCA</option>
                                                                            <option value="blu BCA Digital">blu BCA Digital</option>
                                                                            <option value="BNI 46">BNI 46</option>
                                                                            <option value="BNI Syariah">BNI Syariah</option>
                                                                            <option value="BRI">BRI</option>
                                                                            <option value="BRI Syariah">BRI Syariah</option>
                                                                            <option value="BSI">BSI</option>
                                                                            <option value="BTN">BTN</option>
                                                                            <option value="Bukopin">Bukopin</option>
                                                                            <option value="CIMB">CIMB</option>
                                                                            <option value="Jatim Syariah">Jatim Syariah</option>
                                                                            <option value="Mandiri">Mandiri</option>
                                                                            <option value="Mandiri Syariah">Mandiri Syariah</option>
                                                                            <option value="Muamalat">Muamalat</option>
                                                                            <option value="PANIN BANK">PANIN BANK</option>
                                                                            <option value="SEABANK">SEABANK</option>
                                                                            <option value="SINARMAS">SINARMAS</option>
                                                                            <option value="TF-DANA">TF-DANA</option>
                                                                            <option value="TF-GOPAY">TF-GOPAY</option>
                                                                            <option value="TF-OVO">TF-OVO</option>
                                                                            <option value="TF-SHOPEEPAY">TF-SHOPEEPAY</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>No. Rekening</label>
                                                                        <input type="text" name="account_number" id="edit_account_number" class="form-control" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Atas Nama</label>
                                                                        <input type="text" name="account_holder" id="edit_account_holder" class="form-control" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Status</label>
                                                                        <select name="is_active" id="edit_is_active" class="form-select">
                                                                            <option value="1">Aktif</option>
                                                                            <option value="0">Nonaktif</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <script>
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        var modalEditBank = document.getElementById('modalEditBank');
                                                        modalEditBank.addEventListener('show.bs.modal', function(event) {
                                                            var button = event.relatedTarget;
                                                            document.getElementById('edit_id').value = button.getAttribute('data-id');
                                                            document.getElementById('edit_bank_name').value = button.getAttribute('data-bank_name');
                                                            document.getElementById('edit_account_number').value = button.getAttribute('data-account_number');
                                                            document.getElementById('edit_account_holder').value = button.getAttribute('data-account_holder');
                                                            document.getElementById('edit_is_active').value = button.getAttribute('data-is_active');
                                                            document.getElementById('formEditBank').action = "<?= site_url('settings/master-bank/update/') ?>" + button.getAttribute('data-id');
                                                        });
                                                    });
                                                </script>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada data bank.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= $this->endSection() ?>