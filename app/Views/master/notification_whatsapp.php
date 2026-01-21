<?= $this->extend('layout/default') ?>
<?= $this->section('title') ?>
<title>Notification WhatsApp &mdash; Master Data</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Notification WhatsApp</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Notification WhatsApp</li>
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
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-2"></i>
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table id="notificationTable" class="table table-bordered dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Title</th>
                                        <th>Phone</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="viewDetail(<?= $log['id'] ?>)" title="View Detail">
                                                        <i class='bx bx-show'></i>
                                                    </button>
                                                    <?php if ($log['status'] === 'failed'): ?>
                                                        <a href="<?= site_url('master/notification-whatsapp/retry/' . $log['id']) ?>"
                                                            class="btn btn-sm btn-warning"
                                                            onclick="return confirm('Kirim ulang notifikasi ini?')"
                                                            title="Kirim Ulang">
                                                            <i class='bx  bx-refresh-cw'></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteNotification(<?= $log['id'] ?>)" title="Hapus">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <?php
                                                    $categoryBadge = [
                                                        'invoice' => '<span class="badge bg-info">Invoice</span>',
                                                        'payment' => '<span class="badge bg-success">Payment</span>',
                                                        'reminder' => '<span class="badge bg-warning">Reminder</span>',
                                                        'general' => '<span class="badge bg-secondary">General</span>',
                                                    ];
                                                    echo $categoryBadge[$log['template_type']] ?? '<span class="badge bg-secondary">Other</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($log['status'] === 'sent'): ?>
                                                        <span class="badge bg-success">Sent</span>
                                                    <?php elseif ($log['status'] === 'pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Failed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Extract title from message (first line or first 50 chars)
                                                    $message = $log['message_content'];
                                                    $firstLine = strtok($message, "\n");
                                                    $title = strlen($firstLine) > 60 ? substr($firstLine, 0, 60) . '...' : $firstLine;
                                                    echo esc($title);
                                                    ?>
                                                </td>
                                                <td><?= esc($log['phone_number']) ?></td>
                                                <td>
                                                    <?php
                                                    // Show truncated message
                                                    $shortMessage = strlen($log['message_content']) > 100
                                                        ? substr($log['message_content'], 0, 100) . '...'
                                                        : $log['message_content'];
                                                    echo nl2br(esc($shortMessage));
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data notifikasi</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pager): ?>
                            <div class="mt-3">
                                <?= $pager->links('default', 'default_full') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Notifikasi WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Phone:</strong></div>
                    <div class="col-md-8" id="detail-phone"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Customer Name:</strong></div>
                    <div class="col-md-8" id="detail-customer"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Category:</strong></div>
                    <div class="col-md-8" id="detail-category"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Status:</strong></div>
                    <div class="col-md-8" id="detail-status"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Sent At:</strong></div>
                    <div class="col-md-8" id="detail-sent"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Error Message:</strong></div>
                    <div class="col-md-8" id="detail-error"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12"><strong>Message Content:</strong></div>
                    <div class="col-md-12 mt-2">
                        <pre id="detail-message" class="border p-3 bg-light" style="white-space: pre-wrap;"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#notificationTable').DataTable({
            "order": [
                [3, "desc"]
            ],
            "pageLength": 10
        });
    });

    function viewDetail(id) {
        $.ajax({
            url: '<?= site_url('master/notification-whatsapp/detail/') ?>' + id,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#detail-phone').text(data.phone_number || '-');
                    $('#detail-customer').text(data.customer_name || '-');
                    $('#detail-category').html(getCategoryBadge(data.template_type));
                    $('#detail-status').html(getStatusBadge(data.status));
                    $('#detail-sent').text(data.sent_at || '-');
                    $('#detail-error').text(data.error_message || '-');
                    $('#detail-message').text(data.message_content);

                    $('#detailModal').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal memuat detail notifikasi', 'error');
            }
        });
    }

    function getCategoryBadge(type) {
        const badges = {
            'invoice': '<span class="badge bg-info">Invoice</span>',
            'payment': '<span class="badge bg-success">Payment</span>',
            'reminder': '<span class="badge bg-warning">Reminder</span>',
            'general': '<span class="badge bg-secondary">General</span>'
        };
        return badges[type] || '<span class="badge bg-secondary">Other</span>';
    }

    function getStatusBadge(status) {
        const badges = {
            'sent': '<span class="badge bg-success">Sent</span>',
            'pending': '<span class="badge bg-warning">Pending</span>',
            'failed': '<span class="badge bg-danger">Failed</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
    }

    function deleteNotification(id) {
        Swal.fire({
            title: 'Hapus Notifikasi?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= site_url('master/notification-whatsapp/delete/') ?>' + id;
            }
        });
    }
</script>
<?= $this->endSection() ?>