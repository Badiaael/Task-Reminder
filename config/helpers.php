<?php
// ============================================================
//  config/helpers.php  — NOUVEAU FICHIER
//  Fonctions utilitaires centralisées
// ============================================================

// URL de base du projet (à adapter si sous-dossier différent)
define('BASE_URL', '/TaskReminder/');

/**
 * Échappe une valeur pour l'affichage HTML.
 * Remplace tous les echo $var directs dans les vues.
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Vérifie le token CSRF — arrête le script si invalide.
 * À appeler en tête de chaque action POST.
 */
function csrf_check(): void {
    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        die("Action non autorisée (CSRF).");
    }
}

/**
 * Génère le champ HTML caché du token CSRF.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

/**
 * Valide et nettoie une priorité.
 */
function valid_priority(string $v): string {
    return in_array($v, ['low', 'medium', 'high']) ? $v : 'medium';
}

/**
 * Valide un statut de tâche.
 */
function valid_status(string $v): string {
    return in_array($v, ['pending', 'completed']) ? $v : 'pending';
}