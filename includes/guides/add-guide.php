<?php
/**
 * Endpoint : Ajouter un guide à un outil
 * POST /includes/guides/add-guide.php
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
$title = sanitizeInput($_POST['title'] ?? '');
$url = sanitizeInput($_POST['url'] ?? '');

// Validation basique
if ($tool_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Outil non spécifié']);
    exit;
}

// Ajouter le guide
$result = addToolGuide($tool_id, $user['id'], $title, $url);

if (!$result['success']) {
    http_response_code(400);
    echo json_encode($result);
    exit;
}

// Succès
echo json_encode([
    'success' => true,
    'message' => 'Guide soumis avec succès ! Il sera publié après modération.',
    'guide_id' => $result['guide_id']
]);