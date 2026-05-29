<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user_id = (int)$_SESSION['user_id'];
$id      = (int)($_GET['id'] ?? 0);


$csrf = $_GET['csrf'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(403);
    die("Action non autorisée (CSRF).");
}


$pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?")
    ->execute([$id, $user_id]);

header("Location: " . BASE_URL . "tasks/dashboard.php");
exit();