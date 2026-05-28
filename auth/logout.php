<?php
// ============================================================
//  auth/logout.php
//  MODIFIÉ : supprime aussi le cookie remember_token
// ============================================================
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
if (session_status() === PHP_SESSION_NONE) session_start();

// AJOUT : invalide le token "remember me" en base
if (isset($_SESSION['user_id'])) {
    $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?")
        ->execute([$_SESSION['user_id']]);
}

// AJOUT : supprime le cookie côté navigateur
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

session_unset();
session_destroy();
header("Location: " . BASE_URL . "auth/login.php");
exit();