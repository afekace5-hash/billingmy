<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<title>Clus &mdash; SDN Krengseng 02</title>
<?= $this->endSection() ?>

<?= $this->section('css') ?>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="page-content">
  <div class="container-fluid" data-select2-id="15">
    <!-- start page title -->
    <!-- <div class="vh-100 align-items-center justify-content-center loadingoverlay dontDisplay" id="myLoading">
      <div class="align-items-center">
        <section class="sec-loading">
          <div class="one">
          </div>
        </section>
      </div>
    </div> -->
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0 font-size-18">Clustering</h4>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><a href="<?= site_url('customers') ?>">Clustering</a></li>
              <li class="breadcrumb-item active">Clustering</li>
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
            <div class="row mb-3">
              <div class="col-md-6">
                <a href="<?= site_url('clustering/create') ?>"
                  class="btn btn-primary waves-effect btn-label waves-light">
                  <i class="bx bx-plus label-icon"></i>
                  Tambah Cluster
                </a>
              </div>
              <div class="col-md-6 text-end">
                <button type="button" id="reloadTable"
                  class="btn btn-danger waves-effect btn-label waves-light">
                  <i class="bx bx-reset label-icon"></i>
                  Refresh
                </button>
              </div>
            </div>
            <table class="table table-striped table-hover align-middle table-bordered user_datatable" style="min-width:1200px; width:100%">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nama</th>
                  <th>Jenis</th>
                  <th>Lokasi Server</th>
                  <th>Jumlah Port</th>
                  <th>Port yang tersisa</th>
                  <th>Koordinat</th>
                  <th>Alamat</th>
                  <th width="100px">Tindakan</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SweetAlert2 for Swal -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // CSRF token setup for AJAX
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';

    $(document).ready(function() {
      // Handle Delete button click
      $('.user_datatable').on('click', '.deleteCluster', function() {
        var id = $(this).data('id');
        Swal.fire({
          title: 'Hapus Cluster?',
          text: 'Data cluster akan dihapus permanen!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Ya, Hapus!',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: '<?= site_url('clustering') ?>/' + id,
              type: 'DELETE',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [csrfName]: csrfHash
              },
              success: function(response) {
                if (response.success) {
                  Swal.fire('Berhasil!', 'Cluster berhasil dihapus.', 'success');
                  table.ajax.reload(null, false);
                } else {
                  Swal.fire('Gagal!', response.message || 'Gagal menghapus cluster.', 'error');
                }
              },
              error: function(xhr) {
                Swal.fire('Gagal!', 'Terjadi kesalahan server.', 'error');
              }
            });
          }
        });
      });

      var table = initServerSideDataTable('.user_datatable', {
        ajax: {
          url: '<?= site_url('clustering') ?>',
          type: 'GET'
        },
        columns: [{
            data: 'DT_RowIndex',
            orderable: false,
            searchable: false
          },
          {
            data: 'name'
          },
          {
            data: 'type_option'
          },
          {
            data: 'lokasi_server_id'
          },
          {
            data: 'number_of_ports'
          },
          {
            data: 'remaining_ports',
            defaultContent: '-'
          },
          {
            data: 'coordinate',
            render: function(data, type, row) {
              if (!data) return '';
              var coords = data.split(',');
              if (coords.length === 2 && !isNaN(coords[0]) && !isNaN(coords[1])) {
                return parseFloat(coords[0]).toFixed(5) + ',' + parseFloat(coords[1]).toFixed(5);
              }
              return data;
            }
          },
          {
            data: 'address'
          },
          {
            data: 'action',
            orderable: false,
            searchable: false,
            defaultContent: '-'
          }
        ],
        scrollX: true,
        autoWidth: false,
        responsive: false,
        fixedHeader: true
      });

      // Force DataTables to recalculate column widths and redraw on window resize
      $(window).on('resize', function() {
        table.columns.adjust();
      });

      // Optional: manual reload button
      $('#reloadTable').on('click', function() {
        table.ajax.reload(null, false);
      });
    });
  </script>

  <script src="<?= base_url() ?>backend/assets/js/custom.js"></script>
  <?= $this->endSection() ?>