<?php
/**
 * Fonctions utilitaires pour projets.extrag.one
 */

/**
 * Génère un slug à partir d'un titre
 */
function generateSlug($text, $project_id = null) {
    global $pdo;
    
    // Convertir en minuscules
    $text = mb_strtolower($text, 'UTF-8');
    
    // Remplacer les caractères accentués
    $text = str_replace(
        ['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ù', 'û', 'ü', 'î', 'ï', 'ô', 'ö', 'ç'],
        ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'u', 'u', 'u', 'i', 'i', 'o', 'o', 'c'],
        $text
    );
    
    // Remplacer tout ce qui n'est pas alphanumerique par un tiret
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Supprimer les tirets en début/fin
    $slug = trim($text, '-');
    
    // Vérifier l'unicité
    $original_slug = $slug;
    $counter = 1;
    
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM extra_proj_projects WHERE slug = ?' . ($project_id ? ' AND id != ?' : ''));
        $params = $project_id ? [$slug, $project_id] : [$slug];
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            break;
        }
        
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Récupère les derniers projets publiés
 */
function getLatestProjects($limit = 12) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT p.*, u.username, u.display_name, u.avatar,
               (SELECT filepath FROM extra_proj_images WHERE project_id = p.id AND is_cover = 1 LIMIT 1) as cover_image_path
        FROM extra_proj_projects p
        JOIN extra_proj_users u ON p.user_id = u.id
        WHERE p.status = "published"
        ORDER BY p.published_at DESC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un projet par son slug
 */
function getProjectBySlug($slug) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT p.*, u.username, u.display_name, u.avatar,
               r.username as reviewer_username, r.display_name as reviewer_name
        FROM extra_proj_projects p
        JOIN extra_proj_users u ON p.user_id = u.id
        LEFT JOIN extra_proj_users r ON p.reviewer_id = r.id
        WHERE p.slug = ? AND p.status = "published"
    ');
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère les images d'un projet
 */
function getProjectImages($project_id) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT * FROM extra_proj_images 
        WHERE project_id = ? 
        ORDER BY is_cover DESC, display_order ASC
    ');
    $stmt->execute([$project_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les commentaires d'un projet
 */
function getProjectComments($project_id) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT c.*, u.username, u.display_name, u.avatar
        FROM extra_proj_comments c
        JOIN extra_proj_users u ON c.user_id = u.id
        WHERE c.project_id = ?
        ORDER BY c.created_at DESC
    ');
    $stmt->execute([$project_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Compte les commentaires d'un projet
 */
function countProjectComments($project_id) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM extra_proj_comments WHERE project_id = ?');
    $stmt->execute([$project_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Récupère les projets d'un utilisateur
 */
function getUserProjects($user_id, $include_drafts = false) {
    global $pdo;
    
    $sql = 'SELECT p.*, 
            (SELECT filepath FROM extra_proj_images WHERE project_id = p.id AND is_cover = 1 LIMIT 1) as cover_image_path
            FROM extra_proj_projects p 
            WHERE p.user_id = ?';
    
    if (!$include_drafts) {
        $sql .= ' AND p.status = "published"';
    }
    
    $sql .= ' ORDER BY p.created_at DESC';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un utilisateur par son username
 */
function getUserByUsername($username) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère le top des reviewers
 */
function getTopReviewers($limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT u.*, COUNT(p.id) as review_count
        FROM extra_proj_users u
        LEFT JOIN extra_proj_projects p ON p.reviewer_id = u.id AND p.status = "published"
        WHERE u.role IN ("reviewer", "admin")
        GROUP BY u.id
        ORDER BY review_count DESC, u.username ASC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère les derniers commentaires (pour homepage)
 */
function getLatestComments($limit = 5) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT c.*, u.username, u.display_name, u.avatar,
               p.title as project_title, p.slug as project_slug
        FROM extra_proj_comments c
        JOIN extra_proj_users u ON c.user_id = u.id
        JOIN extra_proj_projects p ON c.project_id = p.id
        WHERE p.status = "published"
        ORDER BY c.created_at DESC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Incrémente le compteur de vues d'un projet
 */
function incrementProjectViews($project_id) {
    global $pdo;
    
    $stmt = $pdo->prepare('UPDATE extra_proj_projects SET view_count = view_count + 1 WHERE id = ?');
    $stmt->execute([$project_id]);
}

/**
 * Formate une date relative (il y a X temps)
 */
function timeAgo($datetime) {
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

/**
 * Tronque un texte
 */
function truncateText($text, $length = 150, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Upload d'une image de projet
 */
function uploadProjectImage($file, $project_id, $is_cover = false) {
    global $pdo;
    
    // Vérifications
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5 Mo
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Fichier trop volumineux (max 5 Mo)'];
    }
    
    // Vérifier le nombre d'images existantes
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM extra_proj_images WHERE project_id = ?');
    $stmt->execute([$project_id]);
    $count = (int)$stmt->fetchColumn();
    
    if ($count >= 5) {
        return ['success' => false, 'error' => 'Maximum 5 images par projet'];
    }
    
    // Générer un nom unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('proj_' . $project_id . '_') . '.' . $extension;
    $upload_dir = __DIR__ . '/../uploads/projects/';
    
    // Créer le dossier si nécessaire
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filepath = $upload_dir . $filename;
    $relative_path = '/uploads/projects/' . $filename;
    
    // Upload
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Si c'est une cover, retirer le flag des autres images
        if ($is_cover) {
            $stmt = $pdo->prepare('UPDATE extra_proj_images SET is_cover = 0 WHERE project_id = ?');
            $stmt->execute([$project_id]);
        }
        
        // Insérer en base
        $display_order = $count;
        $stmt = $pdo->prepare('
            INSERT INTO extra_proj_images (project_id, filename, filepath, display_order, is_cover) 
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$project_id, $filename, $relative_path, $display_order, $is_cover ? 1 : 0]);
        
        return ['success' => true, 'filepath' => $relative_path, 'image_id' => $pdo->lastInsertId()];
    }
    
    return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
}

/**
 * Supprime une image de projet
 */
function deleteProjectImage($image_id, $user_id) {
    global $pdo;
    
    // Vérifier que l'image appartient à un projet de l'utilisateur
    $stmt = $pdo->prepare('
        SELECT i.*, p.user_id 
        FROM extra_proj_images i
        JOIN extra_proj_projects p ON i.project_id = p.id
        WHERE i.id = ?
    ');
    $stmt->execute([$image_id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$image || $image['user_id'] != $user_id) {
        return ['success' => false, 'error' => 'Image non trouvée'];
    }
    
    // Supprimer le fichier physique
    $filepath = __DIR__ . '/..' . $image['filepath'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    
    // Supprimer en base
    $stmt = $pdo->prepare('DELETE FROM extra_proj_images WHERE id = ?');
    $stmt->execute([$image_id]);
    
    return ['success' => true];
}

/**
 * Récupère le nombre de projets en attente de review
 */
function getPendingReviewCount() {
    global $pdo;
    
    $stmt = $pdo->query('SELECT COUNT(*) FROM extra_proj_projects WHERE status = "draft"');
    return (int)$stmt->fetchColumn();
}

/**
 * Vérifie si un utilisateur peut éditer un commentaire
 */
function canEditComment($comment_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT user_id FROM extra_proj_comments WHERE id = ?');
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $comment && $comment['user_id'] == $user_id;
}
?>