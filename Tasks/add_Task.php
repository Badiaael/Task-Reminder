<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "tasks/dashboard.php");
    exit();
}

csrf_check(); 

$user_id          = (int)$_SESSION['user_id'];
$title            = trim($_POST['title']            ?? '');
$description      = trim($_POST['description']      ?? '');
$reminder_datetime = trim($_POST['reminder_datetime'] ?? '');
$priority         = valid_priority($_POST['priority'] ?? 'medium'); // AJOUT validation


$errors = [];
if (strlen($title) < 1 || strlen($title) > 255) {
    $errors[] = "Le titre est requis (max 255 caractères).";
}

if ($reminder_datetime && !DateTime::createFromFormat('Y-m-d\TH:i', $reminder_datetime)) {
    $errors[] = "Date/heure invalide.";
}

if (!empty($errors)) {
    
    die(implode('<br>', array_map('htmlspecialchars', $errors)));
}


$reminder_sql = $reminder_datetime
    ? date('Y-m-d H:i:s', strtotime($reminder_datetime))
    : null;

$pdo->prepare(
    "INSERT INTO tasks (user_id, title, description, reminder_datetime, priority)
     VALUES (?, ?, ?, ?, ?)"
)->execute([$user_id, $title, $description, $reminder_sql, $priority]);

header("Location: " . BASE_URL . "tasks/dashboard.php");
exit();