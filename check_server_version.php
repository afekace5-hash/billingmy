<?php
// Endpoint untuk cek last commit di server
header('Content-Type: application/json');

$output = [];

// Get last commit info
exec('cd ' . __DIR__ . ' && git log -1 --pretty=format:"%H|%s|%ai"', $gitLog);
if (!empty($gitLog[0])) {
    $parts = explode('|', $gitLog[0]);
    $output['last_commit'] = [
        'hash' => substr($parts[0] ?? '', 0, 8),
        'message' => $parts[1] ?? '',
        'date' => $parts[2] ?? ''
    ];
}

// Get current branch
exec('cd ' . __DIR__ . ' && git branch --show-current', $branch);
$output['branch'] = $branch[0] ?? 'unknown';

// Check if files exist
$output['files_check'] = [
    'GenerateInvoices.php' => file_exists(__DIR__ . '/app/Controllers/GenerateInvoices.php'),
    'BiayaTambahan.php' => file_exists(__DIR__ . '/app/Controllers/BiayaTambahan.php'),
    'biaya_tambahan_view' => file_exists(__DIR__ . '/app/Views/biaya_tambahan/index.php')
];

// Get file modification time
$output['file_modified'] = [
    'GenerateInvoices.php' => file_exists(__DIR__ . '/app/Controllers/GenerateInvoices.php') ?
        date('Y-m-d H:i:s', filemtime(__DIR__ . '/app/Controllers/GenerateInvoices.php')) : 'not found',
    'BiayaTambahan.php' => file_exists(__DIR__ . '/app/Controllers/BiayaTambahan.php') ?
        date('Y-m-d H:i:s', filemtime(__DIR__ . '/app/Controllers/BiayaTambahan.php')) : 'not found'
];

$output['server_time'] = date('Y-m-d H:i:s');
$output['webhook_status'] = 'Webhook working - this endpoint shows server is updated';

echo json_encode($output, JSON_PRETTY_PRINT);
