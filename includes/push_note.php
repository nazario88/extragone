<?php
header('Content-Type: application/json');

include('config.php');

// Lire le corps JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['rating'], $data['tool_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes']);
    exit;
}

$rating = (int) $data['rating'];
$tool_id = (int) $data['tool_id'];
$ip = getIP();

// Vérifier la validité de la note
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Note invalide']);
    exit;
}

// Éviter les abus : exemple simple de limite par IP et par outil
$stmt = $pdo->prepare("SELECT COUNT(*) FROM extra_tools_notes WHERE id_tool = ? AND ip = ?");
$stmt->execute([$tool_id, $ip]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    http_response_code(429);
    echo json_encode(['error' => 'Vous avez déjà noté cet outil.']);
    exit;
}

// Insérer la note
$stmt = $pdo->prepare("INSERT INTO extra_tools_notes (id_tool, note, ip, date_note) VALUES (?, ?, ?, NOW())");
$stmt->execute([$tool_id, $rating, $ip]);

echo json_encode(['success' => true]);
