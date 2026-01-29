<?php
/**
 * Fonctions pour la gestion des guides utilisateurs sur les outils
 */

/**
 * Récupère les guides approuvés d'un outil
 * @param int $tool_id ID de l'outil
 * @return array Liste des guides avec infos utilisateur
 */
function getApprovedToolGuides($tool_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT g.*, u.username, u.display_name, u.avatar
            FROM extra_tools_guides g
            JOIN extra_proj_users u ON g.user_id = u.id
            WHERE g.tool_id = ? AND g.status = "approved"
            ORDER BY g.created_at DESC
        ');
        $stmt->execute([$tool_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Get approved guides error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Compte les guides approuvés d'un outil
 * @param int $tool_id ID de l'outil
 * @return int Nombre de guides
 */
function countApprovedToolGuides($tool_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT COUNT(*) 
            FROM extra_tools_guides 
            WHERE tool_id = ? AND status = "approved"
        ');
        $stmt->execute([$tool_id]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log('Count guides error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Récupère les guides d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param string $status Filtre par statut (optionnel)
 * @return array Liste des guides
 */
function getUserGuides($user_id, $status = null) {
    global $pdo;
    
    try {
        $sql = '
            SELECT g.*, t.nom as tool_name, t.slug as tool_slug, t.logo as tool_logo
            FROM extra_tools_guides g
            JOIN extra_tools t ON g.tool_id = t.id
            WHERE g.user_id = ?
        ';
        
        $params = [$user_id];
        
        if ($status) {
            $sql .= ' AND g.status = ?';
            $params[] = $status;
        }
        
        $sql .= ' ORDER BY g.created_at DESC';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Get user guides error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Vérifie si une URL est valide et accessible
 * @param string $url URL à vérifier
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateGuideUrl($url) {
    // Validation format
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['valid' => false, 'error' => 'URL invalide'];
    }
    
    // Vérifier que c'est bien HTTP(S)
    $parsed = parse_url($url);
    if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
        return ['valid' => false, 'error' => 'Seuls les liens HTTP/HTTPS sont acceptés'];
    }
    
    // Vérification accessibilité (optionnelle, peut être lente)
    // Décommenter si besoin de vérifier que l'URL existe vraiment
    /*
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_NOBODY => true, // HEAD request
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode < 200 || $httpCode >= 400) {
        return ['valid' => false, 'error' => 'URL inaccessible (code ' . $httpCode . ')'];
    }
    */
    
    return ['valid' => true, 'error' => null];
}

/**
 * Ajoute un guide pour un outil
 * @param int $tool_id ID de l'outil
 * @param int $user_id ID de l'utilisateur
 * @param string $title Titre du guide
 * @param string $url URL du guide
 * @return array ['success' => bool, 'error' => string|null, 'guide_id' => int|null]
 */
function addToolGuide($tool_id, $user_id, $title, $url) {
    global $pdo;
    
    // Validation titre
    if (empty($title) || strlen($title) < 10 || strlen($title) > 200) {
        return ['success' => false, 'error' => 'Le titre doit contenir entre 10 et 200 caractères'];
    }
    
    // Validation URL
    $urlValidation = validateGuideUrl($url);
    if (!$urlValidation['valid']) {
        return ['success' => false, 'error' => $urlValidation['error']];
    }
    
    // Vérifier que l'outil existe
    $stmt = $pdo->prepare('SELECT id FROM extra_tools WHERE id = ? AND is_valid = 1');
    $stmt->execute([$tool_id]);
    if (!$stmt->fetch()) {
        return ['success' => false, 'error' => 'Outil non trouvé'];
    }
    
    // Vérifier que l'utilisateur n'a pas déjà soumis ce guide (même URL)
    $stmt = $pdo->prepare('
        SELECT id FROM extra_tools_guides 
        WHERE tool_id = ? AND user_id = ? AND url = ?
    ');
    $stmt->execute([$tool_id, $user_id, $url]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Vous avez déjà soumis ce guide'];
    }
    
    // Rate limiting : max 5 guides en attente par utilisateur
    $stmt = $pdo->prepare('
        SELECT COUNT(*) FROM extra_tools_guides 
        WHERE user_id = ? AND status = "pending"
    ');
    $stmt->execute([$user_id]);
    if ($stmt->fetchColumn() >= 5) {
        return ['success' => false, 'error' => 'Vous avez déjà 5 guides en attente de modération'];
    }
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO extra_tools_guides (tool_id, user_id, title, url, status) 
            VALUES (?, ?, ?, ?, "pending")
        ');
        $stmt->execute([$tool_id, $user_id, $title, $url]);
        
        $guide_id = $pdo->lastInsertId();
        
        // Log l'action
        logAction('add_tool_guide', $user_id, null, [
            'tool_id' => $tool_id,
            'guide_id' => $guide_id
        ]);
        
        return ['success' => true, 'guide_id' => $guide_id];
        
    } catch (Exception $e) {
        error_log('Add tool guide error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement'];
    }
}

/**
 * Approuve un guide (admin uniquement)
 * @param int $guide_id ID du guide
 * @return array ['success' => bool, 'error' => string|null]
 */
function approveGuide($guide_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            UPDATE extra_tools_guides 
            SET status = "approved", updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ');
        $stmt->execute([$guide_id]);
        
        // TODO: Envoyer notification email à l'utilisateur
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log('Approve guide error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'approbation'];
    }
}

/**
 * Rejette un guide (admin uniquement)
 * @param int $guide_id ID du guide
 * @param string $reason Raison du rejet
 * @return array ['success' => bool, 'error' => string|null]
 */
function rejectGuide($guide_id, $reason) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            UPDATE extra_tools_guides 
            SET status = "rejected", rejection_reason = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ');
        $stmt->execute([$reason, $guide_id]);
        
        // TODO: Envoyer notification email à l'utilisateur
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log('Reject guide error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors du rejet'];
    }
}

/**
 * Supprime un guide
 * @param int $guide_id ID du guide
 * @param int $user_id ID de l'utilisateur (pour vérifier permissions)
 * @return array ['success' => bool, 'error' => string|null]
 */
function deleteGuide($guide_id, $user_id) {
    global $pdo;
    
    try {
        // Vérifier que c'est bien le guide de l'utilisateur
        $stmt = $pdo->prepare('SELECT user_id FROM extra_tools_guides WHERE id = ?');
        $stmt->execute([$guide_id]);
        $guide = $stmt->fetch();
        
        if (!$guide) {
            return ['success' => false, 'error' => 'Guide non trouvé'];
        }
        
        // Seul l'auteur ou un admin peut supprimer
        if ($guide['user_id'] != $user_id && !isAdmin()) {
            return ['success' => false, 'error' => 'Vous ne pouvez pas supprimer ce guide'];
        }
        
        $stmt = $pdo->prepare('DELETE FROM extra_tools_guides WHERE id = ?');
        $stmt->execute([$guide_id]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log('Delete guide error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
    }
}

/**
 * Récupère tous les guides en attente (admin)
 * @return array Liste des guides avec infos outil et utilisateur
 */
function getPendingGuides() {
    global $pdo;
    
    try {
        $stmt = $pdo->query('
            SELECT g.*, 
                   t.nom as tool_name, t.slug as tool_slug, t.logo as tool_logo,
                   u.username, u.display_name, u.avatar
            FROM extra_tools_guides g
            JOIN extra_tools t ON g.tool_id = t.id
            JOIN extra_proj_users u ON g.user_id = u.id
            WHERE g.status = "pending"
            ORDER BY g.created_at ASC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Get pending guides error: ' . $e->getMessage());
        return [];
    }
}
