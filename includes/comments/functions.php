<?php
/**
 * Fonctions pour le système de commentaires détaillés des outils
 */

/**
 * Récupère les commentaires d'un outil
 * @param int $tool_id ID de l'outil
 * @return array Liste des commentaires avec infos utilisateur
 */
function getToolComments($tool_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT c.*, u.username, u.display_name, u.avatar
            FROM extra_tools_comments c
            JOIN extra_proj_users u ON c.user_id = u.id
            WHERE c.tool_id = ?
            ORDER BY c.created_at DESC
        ');
        $stmt->execute([$tool_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Get tool comments error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Compte les commentaires d'un outil
 * @param int $tool_id ID de l'outil
 * @return int Nombre de commentaires
 */
function countToolComments($tool_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM extra_tools_comments WHERE tool_id = ?');
        $stmt->execute([$tool_id]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        error_log('Count tool comments error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Vérifie si un utilisateur a déjà commenté un outil
 * @param int $tool_id ID de l'outil
 * @param int $user_id ID de l'utilisateur
 * @return bool True si déjà commenté
 */
function hasUserCommentedTool($tool_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT id 
            FROM extra_tools_comments 
            WHERE tool_id = ? AND user_id = ?
        ');
        $stmt->execute([$tool_id, $user_id]);
        return (bool)$stmt->fetch();
    } catch (Exception $e) {
        error_log('Check user commented tool error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Récupère le commentaire d'un utilisateur sur un outil
 * @param int $tool_id ID de l'outil
 * @param int $user_id ID de l'utilisateur
 * @return array|null Le commentaire ou null
 */
function getUserToolComment($tool_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            SELECT * 
            FROM extra_tools_comments 
            WHERE tool_id = ? AND user_id = ?
        ');
        $stmt->execute([$tool_id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        error_log('Get user tool comment error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Ajoute un commentaire à un outil
 * @param int $tool_id ID de l'outil
 * @param int $user_id ID de l'utilisateur
 * @param int $rating Note (1-5)
 * @param string $comment Texte du commentaire
 * @param string $ip Adresse IP
 * @return array ['success' => bool, 'error' => string|null, 'comment_id' => int|null]
 */
function addToolComment($tool_id, $user_id, $rating, $comment, $ip) {
    global $pdo;
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'error' => 'Note invalide (1-5)'];
    }
    
    if (empty($comment) || strlen($comment) > 2000) {
        return ['success' => false, 'error' => 'Le commentaire doit contenir entre 1 et 2000 caractères'];
    }
    
    // Vérifier si déjà commenté
    if (hasUserCommentedTool($tool_id, $user_id)) {
        return ['success' => false, 'error' => 'Vous avez déjà laissé un avis sur cet outil'];
    }
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO extra_tools_comments (tool_id, user_id, rating, comment, ip) 
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$tool_id, $user_id, $rating, $comment, $ip]);
        
        $comment_id = $pdo->lastInsertId();
        
        // Log l'action
        logAction('add_tool_comment', $user_id, null, [
            'tool_id' => $tool_id,
            'comment_id' => $comment_id
        ]);
        
        return ['success' => true, 'comment_id' => $comment_id];
    } catch (Exception $e) {
        error_log('Add tool comment error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement'];
    }
}

/**
 * Vérifie si un utilisateur peut éditer un commentaire
 * @param int $comment_id ID du commentaire
 * @param int $user_id ID de l'utilisateur
 * @return bool True si peut éditer
 */
function canEditToolComment($comment_id, $user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('SELECT user_id FROM extra_tools_comments WHERE id = ?');
        $stmt->execute([$comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $comment && $comment['user_id'] == $user_id;
    } catch (Exception $e) {
        error_log('Can edit tool comment error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Met à jour un commentaire
 * @param int $comment_id ID du commentaire
 * @param int $user_id ID de l'utilisateur
 * @param int $rating Nouvelle note (1-5)
 * @param string $comment Nouveau texte
 * @return array ['success' => bool, 'error' => string|null]
 */
function updateToolComment($comment_id, $user_id, $rating, $comment) {
    global $pdo;
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'error' => 'Note invalide (1-5)'];
    }
    
    if (empty($comment) || strlen($comment) > 2000) {
        return ['success' => false, 'error' => 'Le commentaire doit contenir entre 1 et 2000 caractères'];
    }
    
    // Vérifier les permissions
    if (!canEditToolComment($comment_id, $user_id)) {
        return ['success' => false, 'error' => 'Vous ne pouvez pas modifier ce commentaire'];
    }
    
    try {
        $stmt = $pdo->prepare('
            UPDATE extra_tools_comments 
            SET rating = ?, comment = ?, is_edited = 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ? AND user_id = ?
        ');
        $stmt->execute([$rating, $comment, $comment_id, $user_id]);
        
        // Log l'action
        logAction('edit_tool_comment', $user_id, null, [
            'comment_id' => $comment_id
        ]);
        
        return ['success' => true];
    } catch (Exception $e) {
        error_log('Update tool comment error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la modification'];
    }
}

/**
 * Supprime un commentaire
 * @param int $comment_id ID du commentaire
 * @param int $user_id ID de l'utilisateur
 * @return array ['success' => bool, 'error' => string|null]
 */
function deleteToolComment($comment_id, $user_id) {
    global $pdo;
    
    // Vérifier les permissions
    if (!canEditToolComment($comment_id, $user_id)) {
        return ['success' => false, 'error' => 'Vous ne pouvez pas supprimer ce commentaire'];
    }
    
    try {
        $stmt = $pdo->prepare('DELETE FROM extra_tools_comments WHERE id = ? AND user_id = ?');
        $stmt->execute([$comment_id, $user_id]);
        
        // Log l'action
        logAction('delete_tool_comment', $user_id, null, [
            'comment_id' => $comment_id
        ]);
        
        return ['success' => true];
    } catch (Exception $e) {
        error_log('Delete tool comment error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la suppression'];
    }
}

/**
 * Formate une date en temps relatif (pour les commentaires outils)
 * @param string $datetime Date au format MySQL
 * @return string Texte formaté
 */
function timeAgoTool($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'à l\'instant';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return 'il y a ' . $minutes . ' min';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return 'il y a ' . $hours . 'h';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return 'il y a ' . $days . 'j';
    } else {
        return date('d/m/Y', $timestamp);
    }
}