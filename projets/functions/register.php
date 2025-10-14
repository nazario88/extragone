<?php
include '../includes/config.php';
include 'includes/auth.php';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST METHOD'] !== 'POST') {
    header('Location: /connexion');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: /connexion');
    exit;
}

// Récupération des données
$email = sanitizeInput($_POST['email'] ?? '');
$username = sanitizeInput($_POST['username'] ?? '');
$display_name = sanitizeInput($_POST['display_name'] ?? '');
$password = $_POST['password'] ?? '';

// Validation basique
if (empty($email) || empty($username) || empty($password)) {
    $_SESSION['error'] = 'Tous les champs obligatoires doivent être remplis.';
    header('Location: /connexion');
    exit;
}

// Inscription
$result = registerUser($email, $password, $username, $display_name);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    header('Location: /connexion');
    exit;
}

// Connexion automatique après inscription
loginUser($result['user_id']);

$_SESSION['success'] = 'Compte créé avec succès ! Bienvenue sur Projets eXtragone.';

header('Location: /');
exit;
?>