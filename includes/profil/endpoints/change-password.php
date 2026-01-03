<?php
/**
 * Endpoint : Changement de mot de passe
 * POST /includes/profil/endpoints/change-password.php
 */

session_start();

include '../../config.php';
include '../../auth.php'; // Fonctions auth existantes
include '../functions.php';

// Vérifier que l'utilisateur est connecté
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Vous devez être connecté pour effectuer cette action.';
    header('Location: https://www.extrag.one/connexion');
    exit;
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://www.extrag.one/reglages');
    exit;
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: https://www.extrag.one/reglages');
    exit;
}

$user = getCurrentUser();

// Récupération des données
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error'] = 'Tous les champs sont requis.';
    header('Location: https://www.extrag.one/reglages');
    exit;
}

if ($new_password !== $confirm_password) {
    $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
    header('Location: https://www.extrag.one/reglages');
    exit;
}

// Changement du mot de passe
$result = changeUserPassword($user['id'], $current_password, $new_password);

if ($result['success']) {
    // Log de l'action
    $stmt = $pdo->prepare('INSERT INTO extra_proj_logs (action, user_id, created_at) VALUES (?, ?, NOW())');
    $stmt->execute(['change_password', $user['id']]);
    
    $_SESSION['success'] = 'Mot de passe changé avec succès !';
    header('Location: https://www.extrag.one/reglages');
} else {
    $_SESSION['error'] = $result['error'];
    header('Location: https://www.extrag.one/reglages');
}

exit;