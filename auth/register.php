<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "tasks/dashboard.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password'] ?? '';

    if (strlen($username) < 2 || strlen($username) > 100)
        $errors[] = "Le nom doit contenir entre 2 et 100 caractères.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Adresse email invalide.";
    if (strlen($password) < 8)
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = "Un compte existe déjà avec cet email.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)")
                ->execute([$username, $email, $hash]);
            header("Location: " . BASE_URL . "auth/login.php?registered=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscription — Task Reminder</title>
  <!-- Chemin relatif depuis auth/ vers assets/css/ -->
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="glass-container">
  <h2>Créer un compte</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <?php foreach ($errors as $err): ?>
        <p><?= e($err) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <?= csrf_field() ?>
    <input type="text"     name="username" placeholder="Nom d'utilisateur"
           value="<?= e($_POST['username'] ?? '') ?>" required minlength="2" maxlength="100">
    <input type="email"    name="email"    placeholder="Email"
           value="<?= e($_POST['email'] ?? '') ?>" required>
    <input type="password" name="password" placeholder="Mot de passe (8 car. min.)"
           required minlength="8">
    <button type="submit">S'inscrire</button>
  </form>
  <a href="login.php">Déjà un compte ? Connexion</a>
</div>
</body>
</html>