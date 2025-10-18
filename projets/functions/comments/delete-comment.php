<?php
include '../../../includes/config.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Token invalide']);
    exit;
}

$user = getCurrentUser();
$comment_id = (int)($_POST['comment_id'] ?? 0);

// Vérifier que le commentaire appartient à l'utilisateur
if (!canEditComment($comment_id, $user['id'])) {
    echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas supprimer ce commentaire']);
    exit;
}

try {
    // Supprimer le commentaire
    $stmt = $pdo->prepare('DELETE FROM extra_proj_comments WHERE id = ? AND user_id = ?');
    $stmt->execute([$comment_id, $user['id']]);
    
    // Log de l'action
    logAction('delete_comment', $user['id'], null, ['comment_id' => $comment_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log('Delete comment error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
}
?>