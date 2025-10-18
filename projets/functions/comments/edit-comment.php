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
$content = sanitizeInput($_POST['content'] ?? '');

// Validation
if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Le commentaire ne peut pas être vide']);
    exit;
}

if (strlen($content) > 2000) {
    echo json_encode(['success' => false, 'error' => 'Le commentaire ne peut pas dépasser 2000 caractères']);
    exit;
}

// Vérifier que le commentaire appartient à l'utilisateur
if (!canEditComment($comment_id, $user['id'])) {
    echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas modifier ce commentaire']);
    exit;
}

try {
    // Mettre à jour le commentaire
    $stmt = $pdo->prepare('
        UPDATE extra_proj_comments 
        SET content = ?, is_edited = 1, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ? AND user_id = ?
    ');
    $stmt->execute([$content, $comment_id, $user['id']]);
    
    // Log de l'action
    logAction('edit_comment', $user['id'], null, ['comment_id' => $comment_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log('Edit comment error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la modification']);
}
?>