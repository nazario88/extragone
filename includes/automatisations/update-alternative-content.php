<?php
// ===============================================
// API : update-alternative-content.php
// Endpoint pour actualiser le contenu des pages alternatives
// Compatible N8N / Scripts automatiques
// ===============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include('../config.php');

// ===============================================
// 1. AUTHENTIFICATION (clé API)
// ===============================================

$api_key = $_GET['api_key'] ?? $_POST['api_key'] ?? null;
$valid_key = $_ENV['API_UPDATE_KEY'];

if ($api_key !== $valid_key) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Clé API invalide'
    ]);
    exit;
}

// ===============================================
// 2. PARAMÈTRES DE LA REQUÊTE
// ===============================================

$action = $_GET['action'] ?? 'list'; // list, update, bulk_update
$slug = $_GET['slug'] ?? null;

// ===============================================
// ACTION : LISTER LES PAGES À ACTUALISER
// ===============================================

if ($action === 'list') {
    
    $days_old = (int)($_GET['days_old'] ?? 30); // Pages de + de X jours
    
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.slug,
            c.tool_id,
            c.word_count,
            c.updated_at,
            t.nom as tool_name,
            DATEDIFF(NOW(), c.updated_at) as days_since_update,
            (SELECT COUNT(*) FROM extra_alternatives a 
             INNER JOIN extra_tools alt ON alt.id = a.id_alternative 
             WHERE a.id_outil = c.tool_id AND alt.is_french = 1) as nb_alternatives
        FROM extra_alternatives_content c
        INNER JOIN extra_tools t ON t.id = c.tool_id
        WHERE c.is_active = 1
        AND DATEDIFF(NOW(), c.updated_at) >= ?
        ORDER BY c.updated_at ASC
        LIMIT 100
    ");
    
    $stmt->execute([$days_old]);
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($pages),
        'pages' => $pages
    ]);
    exit;
}

// ===============================================
// ACTION : METTRE À JOUR UNE PAGE
// ===============================================

if ($action === 'update' && $slug) {
    
    // Récupérer les données JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Données JSON invalides'
        ]);
        exit;
    }
    
    // Vérifier que la page existe
    $stmt = $pdo->prepare('SELECT id, tool_id FROM extra_alternatives_content WHERE slug = ?');
    $stmt->execute([$slug]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        // Créer la page si elle n'existe pas
        $stmt = $pdo->prepare('SELECT id FROM extra_tools WHERE slug = ?');
        $stmt->execute([$slug]);
        $tool = $stmt->fetch();
        
        if (!$tool) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Outil non trouvé'
            ]);
            exit;
        }
        
        $tool_id = $tool['id'];
        
        // Insertion
        $stmt = $pdo->prepare("
            INSERT INTO extra_alternatives_content 
            (slug, tool_id, intro_text, comparison_table_json, tools_details_json, faq_json, last_updated_by)
            VALUES (?, ?, ?, ?, ?, ?, 'api_n8n')
        ");
        
        $stmt->execute([
            $slug,
            $tool_id,
            $data['intro_text'] ?? null,
            isset($data['comparison_table']) ? json_encode($data['comparison_table']) : null,
            isset($data['tools_details']) ? json_encode($data['tools_details']) : null,
            isset($data['faq']) ? json_encode($data['faq']) : null
        ]);
        
        $page_id = $pdo->lastInsertId();
        
    } else {
        // Mise à jour
        $stmt = $pdo->prepare("
            UPDATE extra_alternatives_content
            SET 
                intro_text = COALESCE(?, intro_text),
                comparison_table_json = COALESCE(?, comparison_table_json),
                tools_details_json = COALESCE(?, tools_details_json),
                faq_json = COALESCE(?, faq_json),
                last_updated_by = 'api_n8n',
                updated_at = NOW()
            WHERE slug = ?
        ");
        
        $stmt->execute([
            $data['intro_text'] ?? null,
            isset($data['comparison_table']) ? json_encode($data['comparison_table']) : null,
            isset($data['tools_details']) ? json_encode($data['tools_details']) : null,
            isset($data['faq']) ? json_encode($data['faq']) : null,
            $slug
        ]);
        
        $page_id = $existing['id'];
    }
    
    // Calculer le nombre de mots
    $word_count = 0;
    if (!empty($data['intro_text'])) {
        $word_count += str_word_count(strip_tags($data['intro_text']));
    }
    if (!empty($data['faq'])) {
        foreach ($data['faq'] as $item) {
            $word_count += str_word_count($item['question'] . ' ' . $item['answer']);
        }
    }
    if (!empty($data['tools_details'])) {
        foreach ($data['tools_details'] as $detail) {
            $word_count += str_word_count(implode(' ', $detail));
        }
    }
    
    // Mettre à jour le compteur de mots
    $pdo->prepare('UPDATE extra_alternatives_content SET word_count = ? WHERE id = ?')
        ->execute([$word_count, $page_id]);
    
    echo json_encode([
        'success' => true,
        'page_id' => $page_id,
        'word_count' => $word_count,
        'message' => 'Contenu mis à jour avec succès'
    ]);
    exit;
}

// ===============================================
// ACTION : MISE À JOUR EN MASSE
// ===============================================

if ($action === 'bulk_update') {
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['pages'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Format invalide. Attendu: {"pages": [...]}'
        ]);
        exit;
    }
    
    $updated = 0;
    $errors = [];
    
    foreach ($data['pages'] as $page) {
        if (!isset($page['slug'])) {
            $errors[] = 'Slug manquant pour une des pages';
            continue;
        }
        
        try {
            // Simuler un appel update
            $_GET['action'] = 'update';
            $_GET['slug'] = $page['slug'];
            
            // Réutiliser la logique update (à factoriser dans une fonction)
            // Pour simplifier, on fait juste un update SQL direct ici
            
            $stmt = $pdo->prepare("
                INSERT INTO extra_alternatives_content 
                (slug, tool_id, intro_text, comparison_table_json, tools_details_json, faq_json, last_updated_by)
                SELECT ?, t.id, ?, ?, ?, ?, 'api_n8n_bulk'
                FROM extra_tools t
                WHERE t.slug = ?
                ON DUPLICATE KEY UPDATE
                    intro_text = VALUES(intro_text),
                    comparison_table_json = VALUES(comparison_table_json),
                    tools_details_json = VALUES(tools_details_json),
                    faq_json = VALUES(faq_json),
                    last_updated_by = VALUES(last_updated_by),
                    updated_at = NOW()
            ");
            
            $stmt->execute([
                $page['slug'],
                $page['intro_text'] ?? null,
                isset($page['comparison_table']) ? json_encode($page['comparison_table']) : null,
                isset($page['tools_details']) ? json_encode($page['tools_details']) : null,
                isset($page['faq']) ? json_encode($page['faq']) : null,
                $page['slug']
            ]);
            
            $updated++;
            
        } catch (Exception $e) {
            $errors[] = $page['slug'] . ': ' . $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => true,
        'updated' => $updated,
        'errors' => $errors
    ]);
    exit;
}

// ===============================================
// ACTION INVALIDE
// ===============================================

http_response_code(400);
echo json_encode([
    'success' => false,
    'error' => 'Action invalide. Actions disponibles: list, update, bulk_update'
]);
?>