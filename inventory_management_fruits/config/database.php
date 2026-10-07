<?php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'frutasph_inventory');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

function getSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION;
}

function isLoggedIn() {
    $session = getSession();
    return isset($session['user_id']) && isset($session['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    $session = getSession();
    if ($session['user']['role'] !== 'Admin') {
        header('Location: index.php?error=unauthorized');
        exit;
    }
}