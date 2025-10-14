<?php
include '../../includes/config.php';
include '../includes/auth.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /reglages');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: /reglages');
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
    header('Location: /reglages');
    exit;
}

if (strlen($new_password) < 8) {
    $_SESSION['error'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
    header('Location: /reglages');
    exit;
}

if ($new_password !== $confirm_password) {
    $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
    header('Location: /reglages');
    exit;
}

// Vérifier le mot de passe actuel
if (!password_verify($current_password, $user['password_hash'])) {
    $_SESSION['error'] = 'Le mot de passe actuel est incorrect.';
    header('Location: /reglages');
    exit;
}

try {
    // Hasher le nouveau mot de passe
    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Mettre à jour le mot de passe
    $stmt = $pdo->prepare('
        UPDATE extra_proj_users 
        SET password_hash = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ');
    
    $stmt->execute([$new_password_hash, $user['id']]);
    
    // Log de l'action
    logAction('change_password', $user['id']);
    
    $_SESSION['success'] = 'Mot de passe changé avec succès !';
    header('Location: /reglages');
    exit;
    
} catch (Exception $e) {
    error_log('Change password error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors du changement de mot de passe.';
    header('Location: /reglages');
    exit;
}
?>