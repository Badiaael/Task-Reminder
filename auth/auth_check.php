<?php
// ============================================================
//  auth/auth_check.php
//  MODIFIÉ : session_start() sécurisé + support cookie "remember me"
// ============================================================

// AJOUT : paramètres de session sécurisés
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => false,   // mettre true si HTTPS
    'httponly' => true,    // AJOUT : empêche JS d'accéder au cookie de session
    'samesite' => 'Strict' // AJOUT : protection CSRF supplémentaire
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// AJOUT : support du cookie "Se souvenir de moi"
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../config/db.php';
    $token = $_COOKIE['remember_token'];
    $stmt  = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

// AJOUT : génération token CSRF si absent
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}