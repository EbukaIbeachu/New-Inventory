<?php
// Harden session settings before starting the session
if (php_sapi_name() !== 'cli') {
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.use_strict_mode', '1');
    // Lax helps protect against CSRF on cross-site navigations while preserving typical app flows
    @ini_set('session.cookie_samesite', 'Lax');
    // Only set Secure flag when served over HTTPS
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        @ini_set('session.cookie_secure', '1');
    }
}
session_start();

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        die("Access Denied: Admin privileges required.");
    }
}

function flash($name, $message = '', $class = 'success') {
    if (!empty($message)) {
        $_SESSION[$name] = $message;
        $_SESSION[$name . '_class'] = $class;
    } elseif (empty($message) && isset($_SESSION[$name])) {
        $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'success';
        echo '<div class="alert alert-' . $class . '">' . $_SESSION[$name] . '</div>';
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_class']);
    }
}

/**
 * Log an activity for the current or specified user.
 * This is a best-effort helper and will silently fail if the table does not exist.
 */
function log_activity($action_type, $description = '', $userId = null) {
    if (php_sapi_name() === 'cli') {
        return; // skip logging for CLI tasks like cron
    }
    if (!in_array($action_type, ['login','logout','add_item','edit_item','delete_item','create_receipt','delete_receipt','update_receipt_status','user_approve','user_reject','user_make_admin','user_revoke_admin','register','register_admin'], true)) {
        // Allow only known types to avoid noisy logs
        $action_type = 'other';
    }
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = (int)$_SESSION['user_id'];
    }
    try {
        global $pdo;
        if (!isset($pdo)) {
            return;
        }
        $ip = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : null;
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null;
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId ?: null, $action_type, $description, $ip, $ua]);
    } catch (Exception $e) {
        // Swallow errors to avoid breaking the main flow if logging fails
    }
}
?>
