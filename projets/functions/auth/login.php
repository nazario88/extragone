<?php
include '../../../includes/config.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: connexion');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: connexion');
    exit;
}

// Récupération des données
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation basique
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Tous les champs sont requis.';
    header('Location: connexion');
    exit;
}

// Authentification
$result = authenticateUser($email, $password);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    header('Location: connexion');
    exit;
}

// Connexion réussie
loginUser($result['user']['id']);

$_SESSION['success'] = 'Bienvenue ' . htmlspecialchars($result['user']['display_name']) . ' !';

// Redirection
$redirect = $_SESSION['redirect_after_login'] ?? '/';
unset($_SESSION['redirect_after_login']);

header('Location:  . $redirect);
exit;
?>