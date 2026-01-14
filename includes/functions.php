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
?>
