<?php
// Single-admin auth: no database, just one email/password pair stored as a
// hash in config.php (see config.example.php for how to generate it). A
// login is a PHP session — nothing fancier is needed for one editor.

function admin_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function is_logged_in() {
    admin_session_start();
    return !empty($_SESSION['admin_authenticated']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Constant-time-safe: password_verify() already resists timing attacks on
// the hash comparison; the email check below is a plain compare, which is
// fine since it's not a secret (published in the account owner's inbox).
function attempt_login($email, $password) {
    admin_session_start();
    if ($email === ADMIN_EMAIL && password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_email'] = $email;
        return true;
    }
    return false;
}

function do_logout() {
    admin_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function csrf_token() {
    admin_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_check() {
    admin_session_start();
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Invalid or expired form submission — go back and try again.');
    }
}
