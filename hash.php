<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Restrict access: only admin users may use this helper in a controlled environment
if (!is_logged_in() || !is_admin()) {
	http_response_code(403);
	die('Access Denied');
}

// Simple helper to generate password hashes (useful for administrators)
header('Content-Type: text/plain; charset=UTF-8');
echo password_hash('Admin@1234', PASSWORD_DEFAULT), "\n";
echo password_hash('User@1234', PASSWORD_DEFAULT), "\n";