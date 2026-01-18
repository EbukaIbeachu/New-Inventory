<?php
date_default_timezone_set('Africa/Lagos'); // Set application timezone
// Database Configuration (read from environment with safe defaults)
$__env_db_host = getenv('DB_HOST');
$__env_db_name = getenv('DB_NAME');
$__env_db_user = getenv('DB_USER');
$__env_db_pass = getenv('DB_PASS');

define('DB_HOST', $__env_db_host !== false ? $__env_db_host : 'localhost');
define('DB_NAME', $__env_db_name !== false ? $__env_db_name : 'inventory_system');
define('DB_USER', $__env_db_user !== false ? $__env_db_user : 'root');
define('DB_PASS', $__env_db_pass !== false ? $__env_db_pass : '');

// App Configuration
define('APP_NAME', 'Inventory Manager');
// Cron secret (read from env, fallback preserves current value)
$__env_cron_secret = getenv('CRON_SECRET');
if (!defined('CRON_SECRET')) {
	define('CRON_SECRET', $__env_cron_secret !== false ? $__env_cron_secret : 'secret_cron_key');
}
// Dynamically determine BASE_URL so links/redirects work from other devices (mobile, LAN)
if (!defined('BASE_URL')) {
	$defaultBase = 'http://localhost/new%20inventory/inventory/';
	if (!empty($_SERVER['HTTP_HOST'])) {
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$host = $_SERVER['HTTP_HOST'];
		$dir = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), "/\\");
		if ($dir === '') {
			$encodedPath = '/';
		} else {
			$segments = explode('/', $dir);
			$encodedPath = '/' . implode('/', array_map('rawurlencode', $segments)) . '/';
		}
		define('BASE_URL', $scheme . '://' . $host . $encodedPath);
	} else {
		// Fallback for CLI or environments without server vars
		define('BASE_URL', $defaultBase);
	}
}

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');

// Error handling: hide errors from users and log them instead
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
error_reporting(E_ALL); // Capture all errors in logs
@ini_set('log_errors', '1');
@ini_set('error_log', UPLOAD_PATH . 'php_error.log');
