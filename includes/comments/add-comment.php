<?php
/**
 * Endpoint : Ajouter un commentaire à un outil
 * POST /includes/comments/add-comment.php
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
$tool_id = (int)($_POST['tool_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = sanitizeInput($_POST['comment'] ?? '');

// Ajouter le commentaire
$result = addToolComment($tool_id, $user['id'], $rating, $comment, getIP());

if (!$result['success']) {
    http_response_code(400);
    echo json_encode($result);
    exit;
}

// Log de l'action
logAction('add_tool_comment', $user['id'], null, [
    'tool_id' => $tool_id,
    'comment_id' => $result['comment_id']
]);

echo json_encode($result);