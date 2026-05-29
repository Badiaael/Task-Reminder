<?php

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$user_id = (int)$_SESSION['user_id'];
$search  = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM tasks
         WHERE user_id = ? AND title LIKE ?
         ORDER BY reminder_datetime ASC"
    );
    $stmt->execute([$user_id, '%' . $search . '%']);
} else {
    $stmt = $pdo->prepare(
        "SELECT * FROM tasks
         WHERE user_id = ?
         ORDER BY reminder_datetime ASC"
    );
    $stmt->execute([$user_id]);
}
$tasks = $stmt->fetchAll();


$tasks_for_js = array_map(fn($t) => [
    'id'       => $t['id'],
    'title'    => $t['title'],
    'reminder' => $t['reminder_datetime'],
    'status'   => $t['status'],
], $tasks);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — Task Reminder</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="dashboard">

  <div class="top-bar">
    <h1>Task Reminder</h1>
    <div class="top-bar-right">
      <span class="welcome">Bonjour, <?= e($_SESSION['username']) ?> 👋</span>
      <a href="<?= BASE_URL ?>auth/logout.php" class="logout-btn">Déconnexion</a>
    </div>
  </div>

 
  <form method="GET" class="search-form" action="">
    <input type="text" name="search" placeholder="Rechercher une tâche..."
           value="<?= e($search) ?>">
    <button type="submit">Rechercher</button>
    <?php if ($search): ?>
      <a href="dashboard.php" class="btn-reset">✕ Effacer</a>
    <?php endif; ?>
  </form>

  
  <div class="task-form">
    <h3>Nouvelle tâche</h3>
    <form action="add_task.php" method="POST">
      <?= csrf_field() /* AJOUT token CSRF */ ?>

      <input type="text"          name="title"             placeholder="Titre" required maxlength="255">
      <textarea                   name="description"       placeholder="Description (optionnel)" rows="3"></textarea>
      <input type="datetime-local" name="reminder_datetime" required>

      <select name="priority">
        <option value="low">🟢 Faible</option>
        <option value="medium" selected>🟡 Moyenne</option>
        <option value="high">🔴 Élevée</option>
      </select>

      <button type="submit">+ Ajouter la tâche</button>
    </form>
  </div>

  <!-- Liste des tâches -->
  <div class="tasks-container">
    <?php if (empty($tasks)): ?>
      <p class="no-tasks">Aucune tâche trouvée. Commencez par en ajouter une !</p>
    <?php endif; ?>

    <?php foreach ($tasks as $task): ?>
      <?php
        $priority_class = 'priority-' . $task['priority'];
        $status_class   = $task['status'] === 'completed' ? 'task-done' : '';
      ?>
      <div class="task-card <?= $priority_class ?> <?= $status_class ?>">
        <div class="task-header">
          <h3><?= e($task['title']) /* MODIFIÉ : protégé avec e() */ ?></h3>
          <span class="badge badge-<?= e($task['priority']) ?>"><?= e($task['priority']) ?></span>
        </div>

        <?php if ($task['description']): ?>
          <p class="task-desc"><?= e($task['description']) ?></p>
        <?php endif; ?>

        <p class="task-date">
          📅 <?= e($task['reminder_datetime'] ?? '—') ?>
        </p>
        <p class="task-status">
          <?= $task['status'] === 'completed' ? '✅ Terminée' : '⏳ En attente' ?>
        </p>

        <div class="buttons">
          <a href="edit_task.php?id=<?= (int)$task['id'] ?>">✏️ Modifier</a>
          <a href="delete_task.php?id=<?= (int)$task['id'] ?>&csrf=<?= e($_SESSION['csrf_token']) ?>"
             onclick="return confirm('Supprimer cette tâche ?')"
             class="btn-delete">🗑️ Supprimer</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- AJOUT : passe les tâches à JS pour les vraies notifications -->
<script>
  const TASKS = <?= json_encode($tasks_for_js, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>