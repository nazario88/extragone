<?php
/**
 * Endpoint : Ajouter une note rapide à un outil
 * POST /includes/notes/add-note.php
 */

header('Content-Type: application/json');

include('../config.php');
include('functions.php');

// Lire le corps JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['rating'], $data['tool_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit;
}

$rating = (int)$data['rating'];
$tool_id = (int)$data['tool_id'];
$ip = getIP();

// Ajouter la note
$result = addToolRating($tool_id, $rating, $ip);

if (!$result['success']) {
    http_response_code($result['error'] === 'Vous avez déjà noté cet outil' ? 429 : 400);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => true]);