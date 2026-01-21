<?php
/**
 * GitHub Webhook Auto Deploy
 * Place this file at: /www/wwwroot/epay.difihome.my.id/deploy.php
 * 
 * GitHub Settings:
 * 1. Go to repo → Settings → Webhooks → Add webhook
 * 2. Payload URL: https://epay.difihome.my.id/deploy.php
 * 3. Content type: application/json
 * 4. Secret: (generate strong secret, paste in this file below)
 * 5. Events: Push events
 */

// SECURITY: Change this to your webhook secret from GitHub
$webhook_secret = 'your-webhook-secret-change-me';

// Get the payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// Verify the signature
if (empty($signature)) {
    http_response_code(403);
    die('No signature provided');
}

$hash = 'sha256=' . hash_hmac('sha256', $payload, $webhook_secret);
if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    die('Invalid signature');
}

// Parse JSON payload
$data = json_decode($payload, true);

// Only deploy on main branch push
if ($data['ref'] !== 'refs/heads/main') {
    die('Not main branch');
}

// Log file
$log_file = __DIR__ . '/writable/logs/deploy.log';

// Execute git pull
$output = [];
$return_var = 0;

// Change to repo directory and pull
exec('cd ' . escapeshellarg(__DIR__) . ' && git pull origin main 2>&1', $output, $return_var);

// Log the deployment
$log_message = date('Y-m-d H:i:s') . ' - ';
if ($return_var === 0) {
    $log_message .= "✓ Deployment SUCCESS\n";
} else {
    $log_message .= "✗ Deployment FAILED (Exit code: $return_var)\n";
}
$log_message .= "Output: " . implode("\n", $output) . "\n";
$log_message .= "---\n";

file_put_contents($log_file, $log_message, FILE_APPEND);

// Return response
http_response_code($return_var === 0 ? 200 : 500);
echo json_encode([
    'status' => $return_var === 0 ? 'success' : 'failed',
    'message' => implode("\n", $output),
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
