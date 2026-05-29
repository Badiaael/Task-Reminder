<?php
// ============================================================
//  config/db.php
//  MODIFIÉ : charset UTF-8, erreurs masquées en prod
// ============================================================

$host   = "localhost";
$dbname = "task_reminder";
$dbuser = "root";      // ← changer en production
$dbpass = "";          // ← changer en production

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",  // AJOUT charset
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // AJOUT fetch assoc par défaut
            PDO::ATTR_EMULATE_PREPARES   => false,              // AJOUT sécurité requêtes
        ]
    );
} catch (PDOException $e) {
    // MODIFIÉ : Ne jamais afficher l'erreur à l'utilisateur en prod
    error_log("DB Error: " . $e->getMessage());
    die("Erreur de connexion à la base de données.");
}