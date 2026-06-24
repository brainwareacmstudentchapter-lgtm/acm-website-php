<?php
session_start();
require_once 'db.php';
require_once 'config.php';
require_once 'functions.php';

function loginUser($username, $password) {
    $db = Database::getInstance();
    $sql = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    
    return false;
}

function logoutUser() {
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin() && !isSuperAdmin()) {
        redirect(SITE_URL . '/admin/');
    }
}

function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        redirect(SITE_URL . '/admin/');
    }
}
?>
