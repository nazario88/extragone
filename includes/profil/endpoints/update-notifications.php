<?php
/**
 * Endpoint : Mise à jour des préférences de notifications
 * POST /includes/profil/endpoints/update-notifications.php
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

// Récupération des préférences (checkboxes)
$prefs = [
    'email_notif_project_published' => isset($_POST['email_notif_project_published']),
    'email_notif_new_comment' => isset($_POST['email_notif_new_comment']),
    'email_notif_new_review_available' => isset($_POST['email_notif_new_review_available'])
];

// Mise à jour des préférences
$result = updateNotificationPreferences($user['id'], $prefs);

if ($result['success']) {
    // Log de l'action
    $stmt = $pdo->prepare('INSERT INTO extra_proj_logs (action, user_id, created_at) VALUES (?, ?, NOW())');
    $stmt->execute(['update_notifications', $user['id']]);
    
    $_SESSION['success'] = 'Préférences de notifications mises à jour !';
    header('Location: https://www.extrag.one/reglages');
} else {
    $_SESSION['error'] = $result['error'];
    header('Location: https://www.extrag.one/reglages');
}

exit;