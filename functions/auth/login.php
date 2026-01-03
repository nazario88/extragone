<?php
/**
 * Traitement de la connexion utilisateur
 * Utilise le système d'authentification centralisé
 */

// Charger la config et l'authentification centralisées
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://projets.extrag.one/connexion');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: https://projets.extrag.one/connexion');
    exit;
}

// Récupérer les données
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation basique
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Tous les champs sont requis.';
    header('Location: https://projets.extrag.one/connexion');
    exit;
}

// Authentification via la fonction centralisée
$result = authenticateUser($email, $password);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    header('Location: https://projets.extrag.one/connexion');
    exit;
}

// ✅ CONNEXION RÉUSSIE
// Appeler loginUser() pour créer la session ET le cookie
loginUser($result['user']['id']);

$_SESSION['success'] = 'Bienvenue ' . htmlspecialchars($result['user']['display_name']) . ' !';

// Redirection
$redirect = $_SESSION['redirect_after_login'] ?? '';
unset($_SESSION['redirect_after_login']);

header('Location: https://projets.extrag.one/' . $redirect);
exit;