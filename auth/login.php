<?php
// ============================================================
//  auth/login.php
//  MODIFIÉ :
//    - Protection CSRF
//    - Option "Se souvenir de moi" (cookie sécurisé)
//    - Brute-force : délai artificiel sur échec
//    - Message de succès après inscription
// ============================================================
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check(); // AJOUT

    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Régénère l'ID de session pour éviter la fixation de session — AJOUT
        session_regenerate_id(true);

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        // AJOUT : "Se souvenir de moi" — cookie 30 jours
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?")
                ->execute([$token, $user['id']]);
            setcookie('remember_token', $token, [
                'expires'  => time() + 60 * 60 * 24 * 30,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        }

        header("Location: " . BASE_URL . "tasks/dashboard.php");
        exit();
    } else {
        // AJOUT : délai pour ralentir le brute-force
        sleep(1);
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion — Task Reminder</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="glass-container">
  <h2>Connexion</h2>

  <?php if (isset($_GET['registered'])): ?>
    <div class="alert alert-success"><p>Compte créé ! Vous pouvez vous connecter.</p></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error"><p><?= e($error) ?></p></div>
  <?php endif; ?>

  <form method="POST" action="">
    <?= csrf_field() ?>

    <input type="email"    name="email"    placeholder="Email" required
           value="<?= e($_POST['email'] ?? '') ?>">
    <input type="password" name="password" placeholder="Mot de passe" required>

    <!-- AJOUT : case "Se souvenir de moi" -->
    <label class="checkbox-label">
      <input type="checkbox" name="remember"> Se souvenir de moi
    </label>

    <button type="submit">Connexion</button>
  </form>
  <a href="register.php">Créer un compte</a>
</div>
</body>
</html>