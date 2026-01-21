<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Payment Gateway - Billing System</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Konfigurasi Payment Gateway</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
                            <li class="breadcrumb-item active">Payment Gateway</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Konfigurasi Payment Gateway</h4>
                        <p class="card-title-desc mb-4">Kelola pengaturan payment gateway untuk sistem pembayaran digital</p>

                        <!-- Gateway Cards Grid -->
                        <div class="row g-4">
                            <!-- Midtrans Card -->
                            <div class="col-md-4">
                                <div class="card border shadow-sm h-100 gateway-card" data-gateway="midtrans">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <img src="<?= base_url('uploads/payment_getway/207674.svg') ?>" alt="Midtrans" style="height: 60px; width: 200px;">
                                        </div>
                                        <h5 class="card-title">Midtrans</h5>
                                        <p class="text-muted small">Leading Payment Gateway Indonesia</p>
                                        <div class="mb-3">
                                            <span class="badge bg-<?= isset($gateways['midtrans']) && $gateways['midtrans']['is_active'] == 1 ? 'success' : 'secondary' ?> status-badge" id="status-midtrans">
                                                <?= isset($gateways['midtrans']) && $gateways['midtrans']['is_active'] == 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="configureGateway('midtrans')">
                                            <i class="bx bx-cog me-1"></i>Konfigurasi
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Duitku Card -->
                            <div class="col-md-4">
                                <div class="card border shadow-sm h-100 gateway-card" data-gateway="duitku">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <img src="<?= base_url('uploads/payment_getway/Duitku.png') ?>" alt="Duitku" style="height: 60px; width: auto;">
                                        </div>
                                        <h5 class="card-title">Duitku</h5>
                                        <p class="text-muted small">Indonesian Payment Gateway</p>
                                        <div class="mb-3">
                                            <span class="badge bg-<?= isset($gateways['duitku']) && $gateways['duitku']['is_active'] == 1 ? 'success' : 'secondary' ?> status-badge" id="status-duitku">
                                                <?= isset($gateways['duitku']) && $gateways['duitku']['is_active'] == 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="configureGateway('duitku')">
                                            <i class="bx bx-cog me-1"></i>Konfigurasi
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Flip Card -->
                            <div class="col-md-4">
                                <div class="card border shadow-sm h-100 gateway-card" data-gateway="flip">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <img src="<?= base_url('uploads/payment_getway/Logo_flip.png') ?>" alt="Flip" style="height: 60px; width: auto;">
                                        </div>
                                        <h5 class="card-title">Flip</h5>
                                        <p class="text-muted small">Bill Payment Indonesia</p>
                                        <div class="mb-3">
                                            <span class="badge bg-<?= isset($gateways['flip']) && $gateways['flip']['is_active'] == 1 ? 'success' : 'secondary' ?> status-badge" id="status-flip">
                                                <?= isset($gateways['flip']) && $gateways['flip']['is_active'] == 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="configureGateway('flip')">
                                            <i class="bx bx-cog me-1"></i>Konfigurasi
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Modal -->
                        <div class="modal fade" id="gatewayConfigModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="modalTitle">
                                            <i class="bx bx-cog me-2"></i>Konfigurasi Gateway
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form id="paymentGatewayForm">
                                        <?= csrf_field() ?>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">Status Gateway</label>
                                                        <div class="form-check form-switch form-switch-lg">
                                                            <input class="form-check-input" type="checkbox" name="is_active" id="gateway_active">
                                                            <label class="form-check-label" for="gateway_active">Aktifkan</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">Environment</label>
                                                        <select class="form-select" name="environment" id="gateway_environment">
                                                            <option value="sandbox">Sandbox/Test</option>
                                                            <option value="production">Production/Live</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Payment Expiry Setting -->
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-medium">
                                                            <i class="bx bx-time-five me-1"></i>Waktu Expired Pembayaran
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" name="payment_expiry_hours"
                                                                id="payment_expiry_hours" min="1" max="168" value="24" required>
                                                            <span class="input-group-text">Jam</span>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="bx bx-info-circle me-1"></i>
                                                            Waktu kadaluarsa kode pembayaran (VA/QRIS/dll). Default: 24 jam. Max: 168 jam (7 hari)
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="gatewayFields"></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="button" class="btn btn-success" onclick="testConnection()">
                                                <i class="bx bx-wifi me-1"></i>Tes Koneksi
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Simpan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <style>
            .gateway-card {
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .gateway-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            }

            .status-badge {
                font-size: 0.75rem;
                padding: 0.35em 0.65em;
            }
        </style>

        <script>
            let currentGateway = null;
            let existingGateways = {};

            // Gateway configurations
            const gatewayConfigs = {
                midtrans: {
                    name: 'Midtrans',
                    fields: ['merchant_id', 'client_key', 'server_key']
                },
                duitku: {
                    name: 'Duitku',
                    fields: ['merchant_code', 'api_key']
                },
                flip: {
                    name: 'Flip',
                    fields: ['secret_key', 'validation_token']
                }
            };

            // Configure gateway function - Opens modal with gateway-specific fields
            function configureGateway(gateway) {
                currentGateway = gateway;
                const config = gatewayConfigs[gateway];
                const modal = new bootstrap.Modal(document.getElementById('gatewayConfigModal'));

                // Update modal title
                document.getElementById('modalTitle').innerHTML = `<i class="bx bx-cog me-2"></i>Konfigurasi ${config.name}`;

                // Reset form first
                document.getElementById('paymentGatewayForm').reset();

                // Reset checkbox explicitly
                document.querySelector('input[name="is_active"]').checked = false;
                document.querySelector('select[name="environment"]').value = 'sandbox';
                document.getElementById('payment_expiry_hours').value = '24'; // Reset to default

                // Generate fields
                const fieldsDiv = document.getElementById('gatewayFields');
                let fieldsHtml = `
                    <input type="hidden" name="gateway_type" value="${gateway}">
                    <input type="hidden" name="gateway_name" value="${config.name}">
                `;

                // Check if data exists first to customize fields
                const hasExistingData = existingGateways[gateway] ? true : false;

                config.fields.forEach(fieldName => {
                    const displayName = fieldName.replace(/_/g, ' ').toUpperCase();
                    const inputType = fieldName.includes('key') || fieldName.includes('secret') || fieldName.includes('token') ? 'text' : 'text';
                    const placeholder = hasExistingData ? '••••••••• (Kosongkan jika tidak ingin mengubah)' : `Masukkan ${displayName}`;
                    const required = hasExistingData ? '' : 'required';

                    fieldsHtml += `
                        <div class="mb-3">
                            <label class="form-label">${displayName} <span class="text-${hasExistingData ? 'muted' : 'danger'}">${hasExistingData ? '(Opsional)' : '*'}</span></label>
                            <input type="${inputType}" class="form-control" name="${fieldName}"
                                   placeholder="${placeholder}" ${required}>
                            ${hasExistingData ? '<small class="text-success">✓ Data sudah tersimpan</small>' : ''}
                        </div>
                    `;
                });

                fieldsDiv.innerHTML = fieldsHtml;

                // Load existing data if available
                console.log('Loading data for gateway:', gateway);
                console.log('Available gateways data:', existingGateways);

                if (existingGateways[gateway]) {
                    const data = existingGateways[gateway];
                    console.log('Found data for', gateway, ':', data);
                    console.log('is_active value:', data.is_active, 'type:', typeof data.is_active);

                    // Set checkbox - handle both string and number
                    const checkboxElement = document.querySelector('input[name="is_active"]');
                    const isActive = data.is_active == '1' || data.is_active == 1 || data.is_active === true;

                    // Use setTimeout to ensure DOM is ready
                    setTimeout(() => {
                        checkboxElement.checked = isActive;
                        console.log('Set checkbox to:', isActive, 'Checkbox checked:', checkboxElement.checked);
                    }, 100);

                    document.querySelector('select[name="environment"]').value = data.environment || 'sandbox';

                    // Set payment expiry hours
                    const expiryInput = document.getElementById('payment_expiry_hours');
                    if (expiryInput && data.payment_expiry_hours) {
                        expiryInput.value = data.payment_expiry_hours;
                        console.log('Set payment_expiry_hours to:', data.payment_expiry_hours);
                    }

                    const reverseMapping = {
                        'midtrans': {
                            'merchant_id': 'merchant_code',
                            'client_key': 'api_secret',
                            'server_key': 'api_key'
                        },
                        'duitku': {
                            'merchant_code': 'merchant_code',
                            'api_key': 'api_key'
                        },
                        'flip': {
                            'secret_key': 'api_key',
                            'validation_token': 'api_secret'
                        }
                    };

                    if (reverseMapping[gateway]) {
                        Object.keys(reverseMapping[gateway]).forEach(fieldName => {
                            const dbField = reverseMapping[gateway][fieldName];
                            const input = document.querySelector(`input[name="${fieldName}"]`);
                            console.log(`Trying to fill ${fieldName} from DB field ${dbField}, value:`, data[dbField]);
                            if (input && data[dbField]) {
                                input.value = data[dbField];
                                console.log(`Set ${fieldName} to:`, input.value);
                            }
                        });
                    }
                } else {
                    console.log('No existing data found for gateway:', gateway);
                }

                modal.show();
            }

            // Load gateway data on page load
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    <?php if (isset($gateways) && !empty($gateways)): ?>
                        existingGateways = <?= json_encode($gateways) ?> || {};
                        console.log('Loaded gateway data:', existingGateways);
                    <?php else: ?>
                        existingGateways = {};
                        console.log('No gateway data found');
                    <?php endif; ?>
                } catch (e) {
                    console.error('Error loading gateway data:', e);
                    existingGateways = {};
                }

                // Form submission handler
                const form = document.getElementById('paymentGatewayForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        saveGatewayConfig();
                    });
                }
            });

            // Save gateway configuration
            function saveGatewayConfig() {
                const form = document.getElementById('paymentGatewayForm');
                if (!form) return;

                const formData = new FormData(form);
                const gatewayType = formData.get('gateway_type');

                // Map gateway-specific fields to controller expected fields
                const fieldMapping = {
                    'midtrans': {
                        'merchant_id': 'merchant_code',
                        'client_key': 'api_secret',
                        'server_key': 'api_key'
                    },
                    'duitku': {
                        'merchant_code': 'merchant_code',
                        'api_key': 'api_key'
                    },
                    'flip': {
                        'secret_key': 'api_key',
                        'validation_token': 'api_secret'
                    }
                };

                // Create mapped FormData
                const mappedFormData = new FormData();
                mappedFormData.append('gateway_type', gatewayType);
                mappedFormData.append('gateway_name', formData.get('gateway_name'));

                // Get checkbox value properly
                const isActiveCheckbox = document.querySelector('input[name="is_active"]');
                const isActiveValue = isActiveCheckbox && isActiveCheckbox.checked ? '1' : '0';
                mappedFormData.append('is_active', isActiveValue);

                mappedFormData.append('environment', formData.get('environment'));
                mappedFormData.append('payment_expiry_hours', formData.get('payment_expiry_hours') || '24');

                // Add CSRF token
                const csrfToken = document.querySelector('input[name="<?= csrf_token() ?>"]');
                if (csrfToken) {
                    mappedFormData.append('<?= csrf_token() ?>', csrfToken.value);
                }

                // Map fields
                if (fieldMapping[gatewayType]) {
                    Object.keys(fieldMapping[gatewayType]).forEach(originalField => {
                        const mappedField = fieldMapping[gatewayType][originalField];
                        const value = formData.get(originalField);
                        if (value) {
                            mappedFormData.append(mappedField, value);
                        }
                    });
                }

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Menyimpan...';
                submitBtn.disabled = true;

                fetch('<?= site_url('settings/payment-getway') ?>', {
                        method: 'POST',
                        body: mappedFormData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                alert('Berhasil: ' + data.message);
                            }

                            // Update status badge on card
                            const statusBadge = document.getElementById(`status-${gatewayType}`);
                            if (statusBadge) {
                                const isActive = mappedFormData.get('is_active') == '1';
                                statusBadge.className = `badge bg-${isActive ? 'success' : 'secondary'} status-badge`;
                                statusBadge.textContent = isActive ? 'Aktif' : 'Tidak Aktif';
                            }

                            // Update existing data with mapped fields
                            if (!existingGateways[gatewayType]) existingGateways[gatewayType] = {};

                            // Store mapped data (as it's stored in database)
                            for (let [key, value] of mappedFormData.entries()) {
                                if (key !== 'gateway_type' && key !== 'gateway_name' && !key.startsWith('<?= csrf_token() ?>')) {
                                    existingGateways[gatewayType][key] = value;
                                }
                            }

                            console.log('Updated existingGateways:', existingGateways);

                            // Close modal and reload page to get fresh data
                            setTimeout(() => {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('gatewayConfigModal'));
                                if (modal) modal.hide();
                                // Reload to get fresh data from server
                                window.location.reload();
                            }, 2000);
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.message
                                });
                            } else {
                                alert('Gagal: ' + data.message);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan saat menyimpan konfigurasi.'
                            });
                        } else {
                            alert('Terjadi kesalahan.');
                        }
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            }

            // Test connection
            function testConnection() {
                if (!currentGateway) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Pilih payment gateway terlebih dahulu!', 'error');
                    } else {
                        alert('Pilih payment gateway terlebih dahulu!');
                    }
                    return;
                }

                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Testing...';
                btn.disabled = true;

                fetch('<?= site_url('payment/test-connection') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            gateway: currentGateway,
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Koneksi Berhasil!',
                                    text: data.message,
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            } else {
                                alert('Koneksi Berhasil: ' + data.message);
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Koneksi Gagal!',
                                    text: data.message
                                });
                            } else {
                                alert('Koneksi Gagal: ' + data.message);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: `Terjadi kesalahan saat testing koneksi.`
                            });
                        } else {
                            alert(`Terjadi kesalahan saat testing koneksi.`);
                        }
                    })
                    .finally(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
            }
        </script>
    </div>
</div>
<?= $this->endSection() ?>