<?php
$db = new mysqli('localhost', 'root', '', 'kimonet');

// Assign ODP 12 to customer 56
$result = $db->query("UPDATE customers SET odp_id = 12 WHERE id_customers = 56");

if ($result) {
    echo "✅ Customer 56 ODP updated successfully!\n\n";

    // Verify
    $check = $db->query("
        SELECT c.id_customers, c.nama_pelanggan, c.odp_id, o.odp_name, a.area_name
        FROM customers c
        LEFT JOIN odps o ON o.id = c.odp_id
        LEFT JOIN areas a ON a.id = o.area_id
        WHERE c.id_customers = 56
    ");

    $row = $check->fetch_assoc();
    echo "Customer: " . $row['nama_pelanggan'] . "\n";
    echo "ODP ID: " . $row['odp_id'] . "\n";
    echo "ODP Name: " . ($row['odp_name'] ?? 'NULL') . "\n";
    echo "Area: " . ($row['area_name'] ?? 'NULL') . "\n";
} else {
    echo "Error: " . $db->error . "\n";
}
