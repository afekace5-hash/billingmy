<!-- app/Views/settings/master_bank_edit.php -->
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="mdi mdi-bank text-primary me-2"></i> Edit Bank</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= site_url('settings/master-bank/update/' . $bank['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label>Nama Bank</label>
                                <select name="bank_name" class="form-select" required>
                                    <option value="">Pilih Bank</option>
                                    <option value="BANK JATENG" <?= $bank['bank_name'] == 'BANK JATENG' ? 'selected' : '' ?>>BANK JATENG</option>
                                    <option value="BANK LAINYA" <?= $bank['bank_name'] == 'BANK LAINYA' ? 'selected' : '' ?>>BANK LAINYA</option>
                                    <option value="BCA" <?= $bank['bank_name'] == 'BCA' ? 'selected' : '' ?>>BCA</option>
                                    <option value="blu BCA Digital" <?= $bank['bank_name'] == 'blu BCA Digital' ? 'selected' : '' ?>>blu BCA Digital</option>
                                    <option value="BNI 46" <?= $bank['bank_name'] == 'BNI 46' ? 'selected' : '' ?>>BNI 46</option>
                                    <option value="BNI Syariah" <?= $bank['bank_name'] == 'BNI Syariah' ? 'selected' : '' ?>>BNI Syariah</option>
                                    <option value="BRI" <?= $bank['bank_name'] == 'BRI' ? 'selected' : '' ?>>BRI</option>
                                    <option value="BRI Syariah" <?= $bank['bank_name'] == 'BRI Syariah' ? 'selected' : '' ?>>BRI Syariah</option>
                                    <option value="BSI" <?= $bank['bank_name'] == 'BSI' ? 'selected' : '' ?>>BSI</option>
                                    <option value="BTN" <?= $bank['bank_name'] == 'BTN' ? 'selected' : '' ?>>BTN</option>
                                    <option value="Bukopin" <?= $bank['bank_name'] == 'Bukopin' ? 'selected' : '' ?>>Bukopin</option>
                                    <option value="CIMB" <?= $bank['bank_name'] == 'CIMB' ? 'selected' : '' ?>>CIMB</option>
                                    <option value="Jatim Syariah" <?= $bank['bank_name'] == 'Jatim Syariah' ? 'selected' : '' ?>>Jatim Syariah</option>
                                    <option value="Mandiri" <?= $bank['bank_name'] == 'Mandiri' ? 'selected' : '' ?>>Mandiri</option>
                                    <option value="Mandiri Syariah" <?= $bank['bank_name'] == 'Mandiri Syariah' ? 'selected' : '' ?>>Mandiri Syariah</option>
                                    <option value="Muamalat" <?= $bank['bank_name'] == 'Muamalat' ? 'selected' : '' ?>>Muamalat</option>
                                    <option value="PANIN BANK" <?= $bank['bank_name'] == 'PANIN BANK' ? 'selected' : '' ?>>PANIN BANK</option>
                                    <option value="SEABANK" <?= $bank['bank_name'] == 'SEABANK' ? 'selected' : '' ?>>SEABANK</option>
                                    <option value="SINARMAS" <?= $bank['bank_name'] == 'SINARMAS' ? 'selected' : '' ?>>SINARMAS</option>
                                    <option value="TF-DANA" <?= $bank['bank_name'] == 'TF-DANA' ? 'selected' : '' ?>>TF-DANA</option>
                                    <option value="TF-GOPAY" <?= $bank['bank_name'] == 'TF-GOPAY' ? 'selected' : '' ?>>TF-GOPAY</option>
                                    <option value="TF-OVO" <?= $bank['bank_name'] == 'TF-OVO' ? 'selected' : '' ?>>TF-OVO</option>
                                    <option value="TF-SHOPEEPAY" <?= $bank['bank_name'] == 'TF-SHOPEEPAY' ? 'selected' : '' ?>>TF-SHOPEEPAY</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>No. Rekening</label>
                                <input type="text" name="account_number" class="form-control" value="<?= esc($bank['account_number']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Atas Nama</label>
                                <input type="text" name="account_holder" class="form-control" value="<?= esc($bank['account_holder']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" <?= $bank['is_active'] ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= !$bank['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="<?= site_url('settings/master-bank') ?>" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>