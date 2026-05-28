<?php
session_start();
require '../config/db.php';

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $sql->execute([$email]);

    $user = $sql->fetch();

    if($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header("Location: ../tasks/dashboard.php");

    } else {
        $message = "Email ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="glass-container">
    <h2>Connexion</h2>

    <p><?= $message ?></p>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Connexion</button>
    </form>

    <a href="register.php">Créer un compte</a>
</div>

</body>
</html>