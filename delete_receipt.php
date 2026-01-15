<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if (!isset($_GET['id'])) {
    redirect('receipts.php');
}

$id = (int)$_GET['id'];

// Fetch receipt items
$stmt = $pdo->prepare("SELECT inventory_id, quantity FROM receipt_items WHERE receipt_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

// Reincrease inventory for each item
foreach ($items as $item) {
    $pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE id = ?")
        ->execute([$item['quantity'], $item['inventory_id']]);
}

// Delete receipt items
$stmt = $pdo->prepare("DELETE FROM receipt_items WHERE receipt_id = ?");
$stmt->execute([$id]);

// Delete receipt
$stmt = $pdo->prepare("DELETE FROM receipts WHERE id = ?");
$stmt->execute([$id]);

redirect('receipts.php');
