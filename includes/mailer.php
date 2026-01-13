<?php
// Simple Mailer Wrapper
// In a real production environment, use PHPMailer or SwiftMailer

function send_email($to, $subject, $body, $attachments = []) {
    // Fetch settings (mock)
    $from = 'noreply@inventorysystem.com';
    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // For local development, we'll log the email instead of trying to send it via SMTP which might not be configured.
    // To actually send, uncomment the mail() function below and configure php.ini
    
    // $result = mail($to, $subject, $body, $headers);
    
    // Logging for demonstration/debugging
    $log_entry = "--- EMAIL LOG ---\n";
    $log_entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $log_entry .= "To: $to\n";
    $log_entry .= "Subject: $subject\n";
    $log_entry .= "Body: " . strip_tags($body) . "\n"; // Strip tags for log readability
    $log_entry .= "-----------------\n\n";
    
    file_put_contents(ROOT_PATH . 'uploads/email_log.txt', $log_entry, FILE_APPEND);
    
    return true; // Simulate success
}

function get_email_template($type, $data = []) {
    $template = "";
    if ($type === 'low_stock') {
        $template = "<h2>Low Stock Alert</h2>";
        $template .= "<p>The following items are running low:</p><ul>";
        foreach ($data as $item) {
            $template .= "<li><strong>{$item['name']}</strong> (SKU: {$item['sku']}): {$item['quantity']} remaining</li>";
        }
        $template .= "</ul><p>Please restock soon.</p>";
    } elseif ($type === 'daily_report') {
        $template = "<h2>Daily Report</h2>";
        $template .= "<p>Date: " . date('Y-m-d') . "</p>";
        $template .= "<p>Total Receipts Today: {$data['receipt_count']}</p>";
        $template .= "<p>Total Revenue Today: {$data['revenue']}</p>";
    }
    return $template;
}
?>
