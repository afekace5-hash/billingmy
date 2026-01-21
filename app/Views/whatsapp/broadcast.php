<?= $this->extend('layout/default') ?>
<?= $this->section('title') ?>
<title>Broadcast Notifications &mdash; WhatsApp Gateway</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Broadcast Notifications</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBroadcastModal">
                            <i class="bx bx-plus"></i> Create Broadcast
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="broadcastTable" class="table table-bordered dt-responsive nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Action</th>
                                        <th>Type</th>
                                        <th>Branch</th>
                                        <th>Area</th>
                                        <th>Title</th>
                                        <th>Image</th>
                                        <th>Scheduled at</th>
                                        <th>Total Users</th>
                                        <th>Created By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($broadcasts) && !empty($broadcasts)): ?>
                                        <?php foreach ($broadcasts as $broadcast): ?>
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewBroadcast(<?= $broadcast['id'] ?>)">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteBroadcast(<?= $broadcast['id'] ?>)">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                                <td><?= esc($broadcast['type']) ?></td>
                                                <td><?= esc($broadcast['branch'] ?? '-') ?></td>
                                                <td><?= esc($broadcast['area'] ?? '-') ?></td>
                                                <td><?= esc($broadcast['title']) ?></td>
                                                <td>
                                                    <?php if (!empty($broadcast['image'])): ?>
                                                        <img src="<?= base_url('uploads/broadcast/' . $broadcast['image']) ?>" alt="Image" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d M Y H:i', strtotime($broadcast['scheduled_at'])) ?></td>
                                                <td><?= number_format($broadcast['total_users']) ?></td>
                                                <td><?= esc($broadcast['created_by']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Broadcast Modal -->
<div class="modal fade" id="createBroadcastModal" tabindex="-1" aria-labelledby="createBroadcastModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createBroadcastForm" action="<?= base_url('whatsapp/broadcast/create') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createBroadcastModalLabel">Create Broadcast</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Broadcast Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="promosi">Promosi</option>
                                    <option value="informasi_general">Informasi General</option>
                                    <option value="informasi_gangguan">Informasi Gangguan</option>
                                    <option value="informasi_jadwal_pemeliharaan">Informasi Jadwal Pemeliharaan</option>
                                    <option value="informasi_event">Informasi Event</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="area" class="form-label">Area</label>
                                <select class="form-select" id="area" name="area">
                                    <option value="all">All</option>
                                    <!-- Tambahkan opsi area lain jika ada -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="4" placeholder="your message" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="branch" class="form-label">Branch</label>
                                <select class="form-select" id="branch" name="branch">
                                    <option value="all">All</option>
                                    <?php if (isset($branches) && is_array($branches)): ?>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?= esc($branch['id']) ?>"><?= esc($branch['branch_name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="ex: Promo Januari" required>
                            </div>
                            <div class="mb-3">
                                <label for="scheduled_at" class="form-label">Scheduled at (leave blank for now)</label>
                                <input type="text" class="form-control" id="scheduled_at" name="scheduled_at" placeholder="dd/mm/yyyy, --:--, 51">
                            </div>
                            <div class="mb-3">
                                <label for="receiver" class="form-label">Receiver</label>
                                <label for="receiver" class="form-label">Receiver</label>
                                <textarea class="form-control" id="receiver" name="receiver" rows="4" readonly placeholder="Daftar pelanggan akan muncul di sini"></textarea>
                                <?= $this->section('scripts') ?>
                                <script>
                                    $(document).ready(function() {
                                        $('#branch').on('change', function() {
                                            var branchId = $(this).val();
                                            $('#receiver').val('Memuat data pelanggan...');
                                            $.ajax({
                                                url: '<?= base_url('whatsapp/getCustomersByBranch') ?>',
                                                method: 'GET',
                                                data: {
                                                    branch_id: branchId
                                                },
                                                success: function(res) {
                                                    if (res.length === 0) {
                                                        $('#receiver').val('Tidak ada pelanggan pada branch ini.');
                                                    } else {
                                                        let text = res.map(function(cust) {
                                                            return cust.no + '. ' + cust.name + ' - ' + cust.phone;
                                                        }).join('\n');
                                                        $('#receiver').val(text);
                                                    }
                                                },
                                                error: function() {
                                                    $('#receiver').val('Gagal memuat data pelanggan.');
                                                }
                                            });
                                        });
                                    });
                                </script>
                                <?= $this->endSection() ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Broadcast</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>

<script>
    $(document).ready(function() {
        $('#broadcastTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "searching": true,
            "language": {
                "emptyTable": "No data available in table",
                "zeroRecords": "No matching records found"
            }
        });
    });

    function viewBroadcast(id) {
        // Implement view functionality
        window.location.href = '<?= base_url('whatsapp/broadcast/view/') ?>' + id;
    }

    function deleteBroadcast(id) {
        Swal.fire({
            title: 'Delete Broadcast?',
            text: 'Are you sure you want to delete this broadcast?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('<?= base_url('whatsapp/broadcast/delete/') ?>' + id, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', 'Broadcast has been deleted.', 'success');
                            location.reload();
                        } else {
                            Swal.fire('Failed!', data.message || 'An error occurred.', 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'An error occurred while deleting.', 'error');
                    });
            }
        });
    }
</script>
<?= $this->endSection() ?>