<?php
// This script should be run via system cron every minute
// e.g. * * * * * php /path/to/inventory/cron.php

// Define ROOT_PATH manually if run from CLI
if (php_sapi_name() === 'cli') {
    define('ROOT_PATH', __DIR__ . '/');
} else {
    // Prevent direct web access for security, or use a secret key
    if (!isset($_GET['key']) || $_GET['key'] !== 'secret_cron_key') {
        die("Access Denied");
    }
    define('ROOT_PATH', __DIR__ . '/');
}

require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'config/db.php';
require_once ROOT_PATH . 'includes/mailer.php';
require_once ROOT_PATH . 'includes/CronHelper.php';

// Fetch Active Tasks
$stmt = $pdo->query("SELECT * FROM automation_tasks WHERE is_active = 1");
$tasks = $stmt->fetchAll();

foreach ($tasks as $task) {
    if (CronHelper::isDue($task['schedule_cron'])) {
        // Execute Task
        echo "Running task: " . $task['name'] . "\n";
        
        $success = false;
        
        switch ($task['task_type']) {
            case 'email_alert':
                // Check for low stock
                $stmt_stock = $pdo->query("SELECT name, sku, quantity FROM inventory WHERE quantity <= low_stock_threshold");
                $low_stock_items = $stmt_stock->fetchAll();
                
                if (count($low_stock_items) > 0) {
                    $admin_email = 'admin@example.com'; // Should fetch from settings or admin users
                    $body = get_email_template('low_stock', $low_stock_items);
                    $success = send_email($admin_email, 'Low Stock Alert', $body);
                } else {
                    $success = true; // No low stock, effectively a success
                }
                break;
                
            case 'report_generation':
                // Generate Daily Report
                $today = date('Y-m-d');
                $stmt_rep = $pdo->query("SELECT COUNT(*) as c, SUM(total_amount) as s FROM receipts WHERE DATE(receipt_date) = '$today'");
                $res = $stmt_rep->fetch();
                
                $data = [
                    'receipt_count' => $res['c'],
                    'revenue' => $res['s'] ? $res['s'] : 0
                ];
                
                $body = get_email_template('daily_report', $data);
                $success = send_email('admin@example.com', 'Daily Report', $body);
                break;
                
            case 'backup':
                // Simple Database Backup (Dump to file)
                $backup_file = ROOT_PATH . 'database/backup_' . date('Y-m-d_H-i-s') . '.sql';
                // Note: This requires mysqldump to be in path and accessible
                // For PHP implementation without mysqldump, it's more complex.
                // We'll simulate a backup log here.
                $content = "-- Backup created at " . date('Y-m-d H:i:s') . "\n";
                // In real app, iterate tables and dump data
                if (file_put_contents($backup_file, $content)) {
                    $success = true;
                }
                break;
        }
        
        if ($success) {
            $stmt_update = $pdo->prepare("UPDATE automation_tasks SET last_run = NOW() WHERE id = ?");
            $stmt_update->execute([$task['id']]);
            echo "Task completed.\n";
        } else {
            echo "Task failed or no action needed.\n";
        }
    }
}
?>
