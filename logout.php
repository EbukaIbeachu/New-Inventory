<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
	log_activity('logout', 'User logged out', (int)$_SESSION['user_id']);
}

session_destroy();
redirect('login.php');
