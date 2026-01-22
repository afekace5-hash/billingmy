<?php
$mysqli = new mysqli('localhost', 'root', '', 'kimonet');

if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error;
    exit;
}

echo "Checking columns in customers table...\n";
$result = $mysqli->query('DESCRIBE customers');
$found_biaya = false;
$found_additional = false;

while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'biaya_pasang') {
        $found_biaya = true;
        echo "✓ biaya_pasang EXISTS: " . $row['Type'] . "\n";
    }
    if ($row['Field'] === 'additional_fee_id') {
        $found_additional = true;
        echo "✓ additional_fee_id EXISTS: " . $row['Type'] . "\n";
    }
}

if (!$found_biaya) {
    echo "✗ biaya_pasang NOT FOUND - needs to be added\n";
}
if (!$found_additional) {
    echo "✗ additional_fee_id NOT FOUND - needs to be added\n";
}

$mysqli->close();
