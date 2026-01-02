<?php
include '../../includes/config.php';
include '../../includes/auth.php';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://www.extrag.one/connexion');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: https://www.extrag.one/connexion');
    exit;
}

// Récupération des données
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$redirect = $_POST['redirect'] ?? '';

// Validation basique
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Tous les champs sont requis.';
    header('Location: https://www.extrag.one/connexion' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// Authentification
$result = authenticateUser($email, $password);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    header('Location: https://www.extrag.one/connexion' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// Connexion réussie
loginUser($result['user']['id']);

$_SESSION['success'] = 'Bienvenue ' . htmlspecialchars($result['user']['display_name']) . ' !';

// Gestion de la redirection
if ($redirect) {
    // Redirections vers les différents domaines
    if (str_starts_with($redirect, 'projets/')) {
        header('Location: https://projets.extrag.one/' . substr($redirect, 8));
    } else {
        header('Location: https://www.extrag.one/' . $redirect);
    }
} else {
    // Redirection par défaut
    header('Location: https://www.extrag.one');
}
exit;