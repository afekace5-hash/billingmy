<?php
$pdo = new PDO('mysql:host=localhost;dbname=kimonet;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Assigning default branch (ID: 1 - Gringsing) to all customers...\n";

$stmt = $pdo->query("UPDATE customers SET branch_id = 1 WHERE branch_id IS NULL");
$affected = $stmt->rowCount();

echo "✅ Updated {$affected} customers with branch_id = 1\n";

// Verify
$stmt = $pdo->query('SELECT COUNT(*) as total FROM customers WHERE branch_id = 1');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\n✅ Total customers with branch_id = 1: " . $result['total'] . "\n";
