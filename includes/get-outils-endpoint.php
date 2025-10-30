<?php
// =======================================================
// API pour récupérer des outils eXtragone pour Twitter
// =======================================================

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include('config.php');

// --- PARAMÈTRES ---
$isRandom = isset($_GET['random']) && $_GET['random'] == '1';
$excludeIds = isset($_GET['exclude']) ? explode(',', $_GET['exclude']) : [];

try {
    if ($isRandom) {
        // --- MODE RANDOM ---
        // Outils valides, + vieux que 30 jours, non supprimés
        $sql = "SELECT id, nom, description, description_longue, screenshot 
                FROM extra_tools 
                WHERE is_valid = 1 
                AND is_deleted IS NULL 
                AND date_creation < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        if (!empty($excludeIds)) {
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $sql .= " AND id NOT IN ($placeholders)";
        }
        
        $sql .= " ORDER BY RAND() LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        
        if (!empty($excludeIds)) {
            $stmt->execute($excludeIds);
        } else {
            $stmt->execute();
        }
        
    } else {
        // --- MODE DERNIER OUTIL ---
        $sql = "SELECT id, nom, description, description_longue, screenshot 
                FROM extra_tools 
                WHERE is_valid = 1 
                AND is_deleted IS NULL 
                ORDER BY id DESC 
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    
    $tool = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tool) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Aucun outil trouvé'
        ]);
        exit;
    }
    
    // --- FORMATAGE DE LA RÉPONSE ---
    $response = [
        'success' => true,
        'tool' => [
            'id' => (int)$tool['id'],
            'name' => $tool['nom'],
            'description' => $tool['description'],
            'description_longue' => strip_tags($tool['description_longue']),
            'screenshot_url' => $tool['screenshot'] 
                ? 'https://extrag.one/' . $tool['screenshot'] 
                : null
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}