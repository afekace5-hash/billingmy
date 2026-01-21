<?php
// payment_fees.php - Payment Gateway Admin Fee Settings
// This view renders the admin fee settings form for each gateway

/** @var array $gateways */
/** @var string $title */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2><?= esc($title) ?></h2>
        <form id="adminFeeForm" method="post" action="<?= site_url('settings/payment-fees/update') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="gatewayType" class="form-label">Pilih Payment Gateway</label>
                <select class="form-select" id="gatewayType" name="gateway_type" required>
                    <option value="">-- Pilih Gateway --</option>
                    <?php foreach ($gateways as $type => $gateway): ?>
                        <option value="<?= esc($type) ?>"><?= esc($gateway['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="feeFields"></div>
            <button type="submit" class="btn btn-primary">Simpan Biaya Admin</button>
        </form>
        <div id="resultMsg" class="mt-3"></div>
    </div>
    <script>
        const gateways = <?= json_encode($gateways) ?>;
        const feeFieldsDiv = document.getElementById('feeFields');
        const gatewayTypeSelect = document.getElementById('gatewayType');

        function renderFeeFields(type) {
            feeFieldsDiv.innerHTML = '';
            if (!type || !gateways[type]) return;
            const gateway = gateways[type];
            const adminFees = gateway.admin_fees || {};
            // Example payment methods for each gateway
            let methods = [];
            if (type === 'midtrans') {
                methods = [{
                        code: 'credit_card',
                        name: 'Kartu Kredit'
                    },
                    {
                        code: 'bca_va',
                        name: 'BCA VA'
                    },
                    {
                        code: 'bni_va',
                        name: 'BNI VA'
                    },
                    {
                        code: 'bri_va',
                        name: 'BRI VA'
                    },
                    {
                        code: 'mandiri_va',
                        name: 'Mandiri VA'
                    },
                    {
                        code: 'echannel',
                        name: 'Mandiri E-Channel'
                    },
                    {
                        code: 'gopay',
                        name: 'GoPay'
                    },
                    {
                        code: 'qris',
                        name: 'QRIS'
                    }
                ];
            } else if (type === 'duitku') {
                methods = [{
                        code: 'bca_va',
                        name: 'BCA VA'
                    },
                    {
                        code: 'bni_va',
                        name: 'BNI VA'
                    },
                    {
                        code: 'bri_va',
                        name: 'BRI VA'
                    },
                    {
                        code: 'mandiri_va',
                        name: 'Mandiri VA'
                    },
                    {
                        code: 'ovo',
                        name: 'OVO'
                    },
                    {
                        code: 'dana',
                        name: 'DANA'
                    },
                    {
                        code: 'linkaja',
                        name: 'LinkAja'
                    },
                    {
                        code: 'shopeepay',
                        name: 'ShopeePay'
                    },
                    {
                        code: 'qris',
                        name: 'QRIS'
                    }
                ];
            }
            let html = '<h5>Biaya Admin per Metode Pembayaran</h5>';
            methods.forEach(method => {
                const fee = adminFees[method.code] || 0;
                html += `<div class="mb-3">
            <label class="form-label">${method.name} <span class="text-muted">(${method.code})</span></label>
            <input type="number" class="form-control" name="fees[${method.code}]" value="${fee}" min="0" required>
        </div>`;
            });
            feeFieldsDiv.innerHTML = html;
        }

        gatewayTypeSelect.addEventListener('change', function() {
            renderFeeFields(this.value);
        });

        document.getElementById('adminFeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    const msgDiv = document.getElementById('resultMsg');
                    if (data.success) {
                        msgDiv.innerHTML = `<div class='alert alert-success'>${data.message}</div>`;
                    } else {
                        msgDiv.innerHTML = `<div class='alert alert-danger'>${data.message}</div>`;
                    }
                })
                .catch(() => {
                    document.getElementById('resultMsg').innerHTML = `<div class='alert alert-danger'>Gagal menyimpan data.</div>`;
                });
        });
    </script>
</body>

</html>