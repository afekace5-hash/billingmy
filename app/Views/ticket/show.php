<?= $this->extend('layout/default'); ?>

<?= $this->section('styles') ?>
<style>
    .card {
        border-radius: 0 !important;
        border: none;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    }

    .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        border-radius: 0 !important;
    }

    .status-badge {
        padding: 8px 16px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .info-item {
        padding: 12px;
        background: #f8f9fa;
        border-left: 3px solid #007bff;
        margin-bottom: 10px;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Detail Tiket</h4>
                    <div class="page-title-right no-print">
                        <a href="<?= base_url('ticket'); ?>" class="btn btn-secondary">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bx bx-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Header -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1"><?= esc($ticket['subject']) ?></h5>
                                <p class="text-muted mb-0">
                                    <span class="badge bg-secondary">#<?= $ticket['ticket_number'] ?? $ticket['id'] ?></span>
                                    <span class="ms-2">
                                        <i class="bx bx-calendar"></i> <?= date('d M Y H:i', strtotime($ticket['created_at'])) ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php
                                $statusColors = [
                                    'open' => 'success',
                                    'in_progress' => 'primary',
                                    'resolved' => 'warning',
                                    'closed' => 'secondary'
                                ];
                                $priorityColors = [
                                    'urgent' => 'danger',
                                    'high' => 'warning',
                                    'medium' => 'info',
                                    'low' => 'success'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColors[$ticket['status']] ?? 'secondary' ?> status-badge">
                                    <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                                </span>
                                <span class="badge bg-<?= $priorityColors[$ticket['priority']] ?? 'info' ?> status-badge ms-2">
                                    <?= ucfirst($ticket['priority']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main Content -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Description -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-file-blank"></i> Deskripsi Masalah</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0"><?= nl2br(esc($ticket['description'])) ?></p>
                    </div>
                </div>

                <!-- Attachment -->
                <?php if (!empty($ticket['attachment'])) : ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bx bx-paperclip"></i> Lampiran</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $ext = strtolower(pathinfo($ticket['attachment'], PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                            ?>
                            <?php if ($isImage): ?>
                                <img src="<?= base_url('uploads/tickets/' . $ticket['attachment']); ?>"
                                    class="img-fluid rounded mb-2"
                                    alt="Attachment"
                                    style="max-height: 400px;">
                            <?php endif; ?>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-file text-primary" style="font-size: 2rem;"></i>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="mb-0"><?= esc($ticket['attachment']) ?></h6>
                                    <small class="text-muted"><?= strtoupper($ext) ?> File</small>
                                </div>
                                <a href="<?= base_url('uploads/tickets/' . $ticket['attachment']); ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-primary">
                                    <i class="bx bx-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Activity Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-history"></i> Timeline Aktivitas</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="info-item mb-2">
                                <small class="text-muted d-block">
                                    <i class="bx bx-time"></i> <?= date('d M Y H:i', strtotime($ticket['created_at'])) ?>
                                </small>
                                <div>Tiket dibuat dengan status: <strong><?= ucfirst($ticket['status']) ?></strong></div>
                            </div>

                            <?php if ($ticket['updated_at'] != $ticket['created_at']): ?>
                                <div class="info-item mb-2" style="border-left-color: #17a2b8;">
                                    <small class="text-muted d-block">
                                        <i class="bx bx-time"></i> <?= date('d M Y H:i', strtotime($ticket['updated_at'])) ?>
                                    </small>
                                    <div>Tiket diperbarui</div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($ticket['resolved_at'])): ?>
                                <div class="info-item" style="border-left-color: #28a745;">
                                    <small class="text-muted d-block">
                                        <i class="bx bx-time"></i> <?= date('d M Y H:i', strtotime($ticket['resolved_at'])) ?>
                                    </small>
                                    <div>Tiket selesai</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Customer Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-user"></i> Informasi Customer</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Nama Pelanggan</label>
                            <h6><?= esc($ticket['nama_pelanggan'] ?? '-') ?></h6>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Nomor Layanan</label>
                            <h6><?= esc($ticket['nomor_layanan'] ?? '-') ?></h6>
                        </div>
                        <?php if (!empty($ticket['email'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small">Email</label>
                                <h6><?= esc($ticket['email']) ?></h6>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['no_wa'])): ?>
                            <div class="mb-3">
                                <label class="text-muted small">WhatsApp</label>
                                <h6><?= esc($ticket['no_wa']) ?></h6>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($ticket['alamat'])): ?>
                            <div class="mb-0">
                                <label class="text-muted small">Alamat</label>
                                <p class="mb-0"><?= esc($ticket['alamat']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="card mb-3 no-print">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-edit"></i> Update Status</h5>
                    </div>
                    <div class="card-body">
                        <form id="updateStatusForm">
                            <div class="mb-3">
                                <label for="newStatus" class="form-label">Status</label>
                                <select class="form-select" id="newStatus" name="status">
                                    <option value="open" <?= $ticket['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                                    <option value="in_progress" <?= $ticket['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="resolved" <?= $ticket['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                    <option value="closed" <?= $ticket['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary w-100" onclick="updateTicketStatus(<?= $ticket['id'] ?>)">
                                <i class="bx bx-check"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Category & Priority -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-info-circle"></i> Detail Tiket</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Kategori</label>
                            <h6><span class="badge bg-info"><?= ucfirst($ticket['category']) ?></span></h6>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small">Prioritas</label>
                            <h6>
                                <?php
                                $priorityColors = [
                                    'urgent' => 'danger',
                                    'high' => 'warning',
                                    'medium' => 'info',
                                    'low' => 'success'
                                ];
                                ?>
                                <span class="badge bg-<?= $priorityColors[$ticket['priority']] ?? 'info' ?>">
                                    <?= ucfirst($ticket['priority']) ?>
                                </span>
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts'); ?>
<script>
    function updateTicketStatus(ticketId) {
        const newStatus = $('#newStatus').val();

        if (!newStatus) {
            alert('Pilih status baru');
            return;
        }

        $.ajax({
            url: `<?= base_url('ticket') ?>/${ticketId}/status`,
            method: 'POST',
            data: {
                status: newStatus,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', 'Terjadi kesalahan saat memperbarui status tiket');
            }
        });
    }

    function printTicket() {
        window.print();
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';

        const alert = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bx ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

        $('body').append(alert);

        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }

    // Print styles
    const printStyles = `
    <style media="print">
        .header-actions, .status-update-form { display: none !important; }
        .content-sections { grid-template-columns: 1fr !important; }
        .sidebar { display: none !important; }
        body { background: white !important; }
        .ticket-detail-container { background: white !important; padding: 0 !important; }
        .content-card { box-shadow: none !important; border: 1px solid #ddd !important; }
    </style>
`;

    document.head.insertAdjacentHTML('beforeend', printStyles);
</script>
<?= $this->endSection(); ?>

<?= $this->endSection(); ?>