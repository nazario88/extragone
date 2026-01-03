<?php
/**
 * Endpoint : Mise à jour du profil utilisateur
 * POST /includes/profil/endpoints/update-profile.php
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
$data = [
    'display_name' => sanitizeInput($_POST['display_name'] ?? ''),
    'bio' => sanitizeInput($_POST['bio'] ?? ''),
    'external_link' => sanitizeInput($_POST['external_link'] ?? ''),
    'avatar' => $user['avatar'] // Par défaut, on garde l'avatar actuel
];

// Gestion de l'upload d'avatar
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $upload_result = uploadAvatar($_FILES['avatar'], $user['id']);
    
    if (!$upload_result['success']) {
        $_SESSION['error'] = $upload_result['error'];
        header('Location: https://www.extrag.one/reglages');
        exit;
    }
    
    $data['avatar'] = $upload_result['path'];
}

// Mise à jour du profil
$result = updateUserProfile($user['id'], $data);

if ($result['success']) {
    // Log de l'action
    $stmt = $pdo->prepare('INSERT INTO extra_proj_logs (action, user_id, created_at) VALUES (?, ?, NOW())');
    $stmt->execute(['update_profile', $user['id']]);
    
    $_SESSION['success'] = 'Profil mis à jour avec succès !';
    header('Location: https://www.extrag.one/reglages');
} else {
    $_SESSION['error'] = $result['error'];
    header('Location: https://www.extrag.one/reglages');
}

exit;