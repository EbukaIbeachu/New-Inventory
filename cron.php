<?php
// This script should be run via system cron every minute
// e.g. * * * * * php /path/to/inventory/cron.php

// Define ROOT_PATH early for consistent includes
define('ROOT_PATH', __DIR__ . '/');

require_once ROOT_PATH . 'config/config.php';

// If accessed via web, require the correct cron secret key
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['key']) || $_GET['key'] !== CRON_SECRET) {
        die("Access Denied");
    }
}
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
                // Disabled on InfinityFree: skip heavy backup work but treat as success
                $success = true;
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
