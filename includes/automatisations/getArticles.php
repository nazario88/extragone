<?php
/* Appelé par OptiRedac pour récupérer la liste des articles au format JSON
On doit renvoyer : title, meta_description, slug
——————————————————————————————————————————————————————————————*/

// Include your site's configuration
include('../config.php');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    // Example SELECT query - MODIFY THIS for each site
    // Change TABLE_NAME to your actual articles table
    // Add/remove columns as needed
    $stmt = $pdo->prepare("
        SELECT 
            title,
            description as meta_description,
            slug,
            CONCAT('https://www.extrag.one/article/', slug) AS url
        FROM extra_articles
        ORDER BY created_at DESC
    ");
    
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'articles' => $articles
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
