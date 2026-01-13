<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// App Configuration
define('APP_NAME', 'Inventory Manager');
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
