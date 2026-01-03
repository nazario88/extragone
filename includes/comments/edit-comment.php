<?php
/**
 * Endpoint : Éditer un commentaire d'outil
 * POST /includes/comments/edit-comment.php
 */

session_start();

include('../config.php');
include('../auth.php');
include('functions.php');

header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté']);
    exit;
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token invalide']);
    exit;
}

$user = getCurrentUser();
$comment_id = (int)($_POST['comment_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = sanitizeInput($_POST['comment'] ?? '');

// Mettre à jour le commentaire
$result = updateToolComment($comment_id, $user['id'], $rating, $comment);

if (!$result['success']) {
    http_response_code(400);
    echo json_encode($result);
    exit;
}

// Log de l'action
logAction('edit_tool_comment', $user['id'], null, ['comment_id' => $comment_id]);

echo json_encode($result);