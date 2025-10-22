<?php
include '../../../includes/config.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: '.$base.'reglages');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: '.$base.'reglages');
    exit;
}

$user = getCurrentUser();

// Récupération des préférences (checkboxes)
$email_notif_project_published = isset($_POST['email_notif_project_published']) ? 1 : 0;
$email_notif_new_comment = isset($_POST['email_notif_new_comment']) ? 1 : 0;
$email_notif_new_review_available = isset($_POST['email_notif_new_review_available']) ? 1 : 0;

try {
    // Mettre à jour les préférences
    $stmt = $pdo->prepare('
        UPDATE extra_proj_users 
        SET email_notif_project_published = ?,
            email_notif_new_comment = ?,
            email_notif_new_review_available = ?,
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ');
    
    $stmt->execute([
        $email_notif_project_published,
        $email_notif_new_comment,
        $email_notif_new_review_available,
        $user['id']
    ]);
    
    // Log de l'action
    logAction('update_notifications', $user['id']);
    
    $_SESSION['success'] = 'Préférences de notifications mises à jour !';
    header('Location: '.$base.'reglages');
    exit;
    
} catch (Exception $e) {
    error_log('Update notifications error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de la mise à jour des préférences.';
    header('Location: '.$base.'reglages');
    exit;
}
?>