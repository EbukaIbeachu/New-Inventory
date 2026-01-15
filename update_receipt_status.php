<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');
require_login();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$id || !in_array($status, ['Paid', 'Unpaid', 'Overdue'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$stmt = $pdo->prepare("UPDATE receipts SET status = ? WHERE id = ?");
$success = $stmt->execute([$status, $id]);

echo json_encode(['success' => $success]);
