<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user_id = (int)$_SESSION['user_id'];
$id      = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    http_response_code(403);
    die("Accès refusé.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check();

    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority    = valid_priority($_POST['priority'] ?? 'medium');  // ← manquait
    $status      = valid_status($_POST['status']     ?? 'pending'); // ← manquait

    if (strlen($title) < 1 || strlen($title) > 255) { // ← manquait le if
        die("Titre invalide.");
    }

    $pdo->prepare(
        "UPDATE tasks
         SET title = ?, description = ?, priority = ?, status = ?
         WHERE id = ? AND user_id = ?"
    )->execute([$title, $description, $priority, $status, $id, $user_id]);

    header("Location: " . BASE_URL . "tasks/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Modifier la tâche — Task Reminder</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="glass-container">
  <h2>Modifier la tâche</h2>

  <form method="POST" action="">
    <?= csrf_field() ?>

    <input type="text" name="title"
           value="<?= e($task['title']) ?>"
           required maxlength="255">

    <textarea name="description" rows="4"><?= e($task['description']) ?></textarea>

    <select name="priority">
      <option value="low"    <?= $task['priority'] === 'low'    ? 'selected' : '' ?>>🟢 Faible</option>
      <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>🟡 Moyenne</option>
      <option value="high"   <?= $task['priority'] === 'high'   ? 'selected' : '' ?>>🔴 Élevée</option>
    </select>

    <select name="status">
      <option value="pending"   <?= $task['status'] === 'pending'   ? 'selected' : '' ?>>⏳ En attente</option>
      <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>✅ Terminée</option>
    </select>

    <button type="submit">Enregistrer</button>
    <a href="<?= BASE_URL ?>tasks/dashboard.php" class="btn-cancel">Annuler</a>
  </form>
</div>
</body>
</html>