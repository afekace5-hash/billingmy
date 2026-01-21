<?= $this->extend('layout/default'); ?>

<?= $this->section('styles') ?>
<style>
    /* Remove ALL rounded corners from cards */
    .card,
    .card *,
    .mini-stats-wid,
    .mini-stats-wid *,
    .card.mini-stats-wid,
    .card-body {
        border-radius: 0 !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>
<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Ticket</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Tickets Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Action Buttons -->
                        <div class="mb-3">
                            <a href="<?= base_url('ticket/create'); ?>" class="btn btn-info custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                <i class="bx bx-plus" style="font-size:20px; padding-right:5px;"></i> New Ticket
                            </a>
                            <button type=" button" class="btn btn-secondary custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                <i class="bx bx-filter" style="font-size:20px; padding-right:5px;"></i> Filter
                            </button>
                            <button type=" button" class="btn btn-success custom-radius" style="display:inline-flex;align-items:center;justify-content:center;">
                                <i class="bx bx-download" style="font-size:20px; padding-right:5px;"></i> Export
                            </button>
                        </div>

                        <div class=" table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle nowrap" id="ticketsTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">Action</th>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Categories</th>
                                        <th>Subject</th>
                                        <th>Branch</th>
                                        <th>Layanan</th>
                                        <th class="text-center">Installed at</th>
                                        <th>Technician</th>
                                        <th>Customer</th>
                                        <th>ODP</th>
                                        <th>Address</th>
                                        <th class="text-center">Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= base_url() ?>backend/assets/js/custom.js"></script>
<script>
    $(document).ready(function() {
        console.log('Initializing DataTable for tickets...');
        console.log('Base URL:', "<?= base_url('ticket/data') ?>");

        // Show flash messages
        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({
                title: 'Berhasil!',
                text: "<?= session()->getFlashdata('success') ?>",
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({
                title: 'Gagal!',
                text: "<?= session()->getFlashdata('error') ?>",
                icon: 'error'
            });
        <?php endif; ?>

        // Initialize DataTable
        var table = $('#ticketsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('ticket/data') ?>",
                type: "POST",
                dataSrc: function(json) {
                    console.log('Ajax Success - Response:', json);
                    console.log('Total records:', json.recordsTotal);
                    console.log('Data array length:', json.data ? json.data.length : 0);
                    if (json.data && json.data.length > 0) {
                        console.log('First row:', json.data[0]);
                    }
                    return json.data;
                },
                error: function(xhr, error, code) {
                    console.error('DataTables AJAX Error:', error);
                    console.error('Status:', xhr.status);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading data: ' + error);
                }
            },
            columns: [{
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                                    <button class="btn btn-sm btn-primary" onclick="viewTicket(${row.id})">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTicket(${row.id})">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                `;
                    }
                },
                {
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        var badgeClass = data === 'Open' ? 'bg-success' : 'bg-danger';
                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'category',
                    className: 'text-center',
                    render: function(data) {
                        return `<span class="badge bg-info">${data}</span>`;
                    }
                },
                {
                    data: 'subject'
                },
                {
                    data: 'branch'
                },
                {
                    data: 'layanan',
                    render: function(data, type, row) {
                        return `${row.package}<br><small class="text-muted">${row.speed}</small>`;
                    }
                },
                {
                    data: 'installed_at',
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `${data}<br><small class="text-muted">${row.installed_note || ''}</small>`;
                    }
                },
                {
                    data: 'technician',
                    render: function(data, type, row) {
                        return `${row.technician_name}<br><small class="text-muted">${row.technician_phone || ''}</small>`;
                    }
                },
                {
                    data: 'customer',
                    render: function(data, type, row) {
                        return `<strong>${row.customer_name}</strong><br>
                                        <small>${row.customer_email || ''}</small><br>
                                        <small class="text-muted">${row.customer_phone || ''}</small>`;
                    }
                },
                {
                    data: 'odp'
                },
                {
                    data: 'address'
                },
                {
                    data: 'duration',
                    className: 'text-center'
                }
            ],
            order: [
                [1, 'desc']
            ],
            pageLength: 10,
            responsive: false,
            scrollX: true,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    });

    function viewTicket(id) {
        window.location.href = "<?= base_url('ticket/') ?>" + id;
    }

    function deleteTicket(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data tiket akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('ticket/delete/') ?>" + id,
                    type: "POST",
                    data: {
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Tiket berhasil dihapus',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#ticketsTable').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Gagal!', response.message || 'Gagal menghapus tiket', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan saat menghapus tiket', 'error');
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection(); ?>