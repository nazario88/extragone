<?php
/**
 * Fonctions pour le système de notation rapide des outils
 */

/**
 * Récupère les statistiques de notation d'un outil
 * @param int $tool_id ID de l'outil
 * @return array ['average' => float, 'nb' => int]
 */
function getToolRatingStats($tool_id) {
    global $pdo;
    
    try {
        // Notes rapides (anonymes)
        $stmt = $pdo->prepare('
            SELECT SUM(note) as sum_notes, COUNT(note) as count_notes 
            FROM extra_tools_notes 
            WHERE id_tool = ?
        ');
        $stmt->execute([$tool_id]);
        $quick_ratings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Notes détaillées (commentaires)
        $stmt = $pdo->prepare('
            SELECT SUM(rating) as sum_ratings, COUNT(rating) as count_ratings 
            FROM extra_tools_comments 
            WHERE tool_id = ?
        ');
        $stmt->execute([$tool_id]);
        $comment_ratings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Combiner les deux
        $total_sum = ($quick_ratings['sum_notes'] ?? 0) + ($comment_ratings['sum_ratings'] ?? 0);
        $total_count = ($quick_ratings['count_notes'] ?? 0) + ($comment_ratings['count_ratings'] ?? 0);
        
        $average = $total_count > 0 ? round($total_sum / $total_count, 2) : 0;
        
        return [
            'average' => (float)$average,
            'nb' => (int)$total_count
        ];
    } catch (Exception $e) {
        error_log('Get tool rating stats error: ' . $e->getMessage());
        return ['average' => 0, 'nb' => 0];
    }
}

/**
 * Vérifie si une IP a déjà noté un outil
 * @param int $tool_id ID de l'outil
 * @param string $ip Adresse IP
 * @return bool True si déjà noté
 */
function hasUserRatedTool($tool_id, $ip) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT id 
            FROM extra_tools_notes 
            WHERE id_tool = ? AND ip = ?
        ');
        $stmt->execute([$tool_id, $ip]);
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        error_log('Check user rated tool error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Ajoute une note rapide à un outil
 * @param int $tool_id ID de l'outil
 * @param int $rating Note (1-5)
 * @param string $ip Adresse IP
 * @return array ['success' => bool, 'error' => string|null]
 */
function addToolRating($tool_id, $rating, $ip) {
    global $pdo;
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'error' => 'Note invalide (1-5)'];
    }
    
    // Vérifier si déjà noté
    if (hasUserRatedTool($tool_id, $ip)) {
        return ['success' => false, 'error' => 'Vous avez déjà noté cet outil'];
    }
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO extra_tools_notes (id_tool, note, ip, date_note) 
            VALUES (?, ?, ?, NOW())
        ');
        $stmt->execute([$tool_id, $rating, $ip]);
        
        return ['success' => true];
    } catch (Exception $e) {
        error_log('Add tool rating error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement'];
    }
}

/**
 * Génère le HTML des étoiles pour l'affichage de la moyenne
 * @param float $average Moyenne (0-5)
 * @return string HTML des étoiles
 */
function renderRatingStars($average) {
    $html = '';
    $full_stars = floor($average);
    $has_half = ($average - $full_stars) >= 0.5;
    
    // Étoiles pleines
    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fa-solid fa-star text-yellow-400 text-xs"></i>';
    }
    
    // Demi-étoile
    if ($has_half) {
        $html .= '<i class="fa-solid fa-star-half text-yellow-400 text-xs"></i>';
        $full_stars++;
    }
    
    // Étoiles vides
    for ($i = $full_stars; $i < 5; $i++) {
        $html .= '<i class="fa-regular fa-star text-gray-300 text-xs"></i>';
    }
    
    return $html;
}