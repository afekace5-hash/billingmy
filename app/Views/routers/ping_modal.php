<!-- Simple modal content for Router Ping -->
<div class="modal-header">
    <h5 class="modal-title">Test Ping Router</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="ping_modal_form">
        <input type="hidden" id="ping_modal_router_id" value="">
        <div class="mb-3">
            <label for="ping_modal_target_address" class="form-label">Target IP/Hostname</label>
            <input type="text" class="form-control" id="ping_modal_target_address" placeholder="Masukkan IP atau hostname">
        </div>
        <button type="button" class="btn btn-primary" id="ping_modal_execute_button">Ping</button>
    </form>
    <div class="mt-3" id="ping_modal_results"></div>
    <script>
        $(function() {
            // Handler untuk tombol Ping di modal
            $('body').off('click', '#ping_modal_execute_button').on('click', '#ping_modal_execute_button', function() {
                var routerId = $('#ping_modal_router_id').val();
                var targetAddress = $('#ping_modal_target_address').val();
                if (!routerId || !targetAddress) {
                    $('#ping_modal_results').html('<span class="text-danger">Router ID dan target address wajib diisi.</span>');
                    return;
                }
                $('#ping_modal_results').html('Pinging ' + targetAddress + '...');
                $.ajax({
                    url: '/routers/' + routerId + '/ping-mikrotik',
                    type: 'POST',
                    data: {
                        address: targetAddress
                    },
                    dataType: 'json',
                    success: function(response) {
                        var output = '';
                        if (response.error) {
                            output = '<span class="text-danger">Error: ' + response.error + '</span>';
                            showToastMessage('error', response.error, 'Ping Failed');
                        } else if (response.data && Array.isArray(response.data) && response.data.length > 0) {
                            output = '<span class="text-success">Ping sukses!</span><br/>';
                            response.data.forEach(function(reply) {
                                let line = [];
                                if (reply.host) line.push('host: ' + reply.host);
                                if (reply.time) line.push('time: ' + reply.time);
                                if (reply.ttl) line.push('ttl: ' + reply.ttl);
                                if (reply['packet-loss'] !== undefined) line.push('loss: ' + reply['packet-loss']);
                                if (reply.size) line.push('size: ' + reply.size);
                                output += line.join(', ') + '<br/>';
                            });
                            showToastMessage('success', 'Ping sukses ke ' + targetAddress, 'Ping Success');
                        } else {
                            output = '<span class="text-danger">Ping timeout atau tidak ada balasan.</span>';
                            showToastMessage('warning', 'Ping timeout atau tidak ada balasan.', 'Ping Warning');
                        }
                        $('#ping_modal_results').html(output);
                    },
                    error: function(xhr) {
                        var errorMsg = 'Error performing ping.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        $('#ping_modal_results').html('<span class="text-danger">' + errorMsg + '</span>');
                        showToastMessage('error', errorMsg, 'Ping Failed');
                    }
                });
            });
        });
    </script>
</div>