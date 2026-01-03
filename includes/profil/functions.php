<?php
/**
 * Fonctions pour la gestion des profils utilisateurs (centralisé sur extrag.one)
 */

/**
 * Récupère les projets d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param bool $include_drafts Inclure les brouillons/en review (si c'est son propre profil)
 * @return array Liste des projets
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
 * Récupère les statistiques d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @return array Stats (published_count, review_count, comment_count)
 */
function getUserStats($user_id) {
    global $pdo;
    
    $stats = [];
    
    // Nombre de projets publiés
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM extra_proj_projects WHERE user_id = ? AND status = "published"');
    $stmt->execute([$user_id]);
    $stats['published_count'] = (int)$stmt->fetchColumn();
    
    // Nombre de reviews rédigées (si reviewer/admin)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM extra_proj_projects WHERE reviewer_id = ? AND status = "published"');
    $stmt->execute([$user_id]);
    $stats['review_count'] = (int)$stmt->fetchColumn();
    
    // Nombre de commentaires
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM extra_proj_comments WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $stats['comment_count'] = (int)$stmt->fetchColumn();
    
    return $stats;
}

/**
 * Upload et optimisation d'un avatar
 * @param array $file Le fichier $_FILES['avatar']
 * @param int $user_id ID de l'utilisateur
 * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function uploadAvatar($file, $user_id) {
    global $pdo;
    
    // Validations
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2 Mo
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé (JPG, PNG, GIF, WebP uniquement)'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Fichier trop volumineux (max 2 Mo)'];
    }
    
    // Récupérer l'ancien avatar pour le supprimer
    $stmt = $pdo->prepare('SELECT avatar FROM extra_proj_users WHERE id = ?');
    $stmt->execute([$user_id]);
    $old_avatar = $stmt->fetchColumn();
    
    // Générer un nom unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $user_id . '_' . uniqid() . '.' . $extension;
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/';
    
    // Créer le dossier si nécessaire
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filepath = $upload_dir . $filename;
    $relative_path = '/uploads/avatars/' . $filename;
    
    // Upload du fichier temporaire
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
    }
    
    // Optimiser en WebP
    $optimized_filename = 'avatar_' . $user_id . '_' . uniqid() . '.webp';
    $optimized_filepath = $upload_dir . $optimized_filename;
    
    if (optimizeAvatar($filepath, $optimized_filepath, 256, 85)) {
        // Supprimer l'original non optimisé
        @unlink($filepath);
        $final_path = '/uploads/avatars/' . $optimized_filename;
    } else {
        // Garder l'original si l'optimisation échoue
        $final_path = $relative_path;
    }
    
    // Supprimer l'ancien avatar si existe
    if ($old_avatar && file_exists($_SERVER['DOCUMENT_ROOT'] . $old_avatar)) {
        @unlink($_SERVER['DOCUMENT_ROOT'] . $old_avatar);
    }
    
    return ['success' => true, 'path' => $final_path];
}

/**
 * Optimise un avatar (redimensionnement carré + conversion WebP)
 * @param string $source_path Chemin source
 * @param string $destination_path Chemin destination
 * @param int $max_size Taille max (carré)
 * @param int $quality Qualité WebP (0-100)
 * @return bool Succès ou échec
 */
function optimizeAvatar($source_path, $destination_path, $max_size = 256, $quality = 85) {
    // Déterminer le type d'image
    $image_info = @getimagesize($source_path);
    if ($image_info === false) {
        return false;
    }
    
    $mime_type = $image_info['mime'];
    
    // Créer l'image source selon le type
    switch ($mime_type) {
        case 'image/jpeg':
            $source = @imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source = @imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $source = @imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            $source = @imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    // Dimensions originales
    $width = imagesx($source);
    $height = imagesy($source);
    
    // Calculer les nouvelles dimensions (carré, crop centré)
    $new_size = min($width, $height, $max_size);
    
    // Créer la nouvelle image
    $destination = imagecreatetruecolor($new_size, $new_size);
    
    // Préserver la transparence
    imagealphablending($destination, false);
    imagesavealpha($destination, true);
    $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
    imagefilledrectangle($destination, 0, 0, $new_size, $new_size, $transparent);
    
    // Calculer le crop centré
    $crop_size = min($width, $height);
    $crop_x = ($width - $crop_size) / 2;
    $crop_y = ($height - $crop_size) / 2;
    
    // Redimensionner avec crop centré
    imagecopyresampled(
        $destination, $source,
        0, 0, $crop_x, $crop_y,
        $new_size, $new_size,
        $crop_size, $crop_size
    );
    
    // Sauvegarder en WebP
    $result = imagewebp($destination, $destination_path, $quality);
    
    // Libérer la mémoire
    imagedestroy($source);
    imagedestroy($destination);
    
    return $result;
}

/**
 * Met à jour les informations du profil utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param array $data Données à mettre à jour ['display_name', 'bio', 'external_link', 'avatar']
 * @return array ['success' => bool, 'error' => string|null]
 */
function updateUserProfile($user_id, $data) {
    global $pdo;
    
    // Validation
    if (empty($data['display_name'])) {
        return ['success' => false, 'error' => 'Le nom d\'affichage ne peut pas être vide'];
    }
    
    if (strlen($data['display_name']) > 100) {
        return ['success' => false, 'error' => 'Le nom d\'affichage ne peut pas dépasser 100 caractères'];
    }
    
    if (isset($data['bio']) && strlen($data['bio']) > 500) {
        return ['success' => false, 'error' => 'La bio ne peut pas dépasser 500 caractères'];
    }
    
    if (!empty($data['external_link']) && !filter_var($data['external_link'], FILTER_VALIDATE_URL)) {
        return ['success' => false, 'error' => 'Le lien externe n\'est pas valide'];
    }
    
    try {
        $stmt = $pdo->prepare('
            UPDATE extra_proj_users 
            SET display_name = ?, 
                bio = ?, 
                external_link = ?, 
                avatar = ?, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ');
        
        $stmt->execute([
            $data['display_name'],
            $data['bio'] ?? null,
            $data['external_link'] ?? null,
            $data['avatar'] ?? null,
            $user_id
        ]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log('Update profile error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Met à jour les préférences de notifications
 * @param int $user_id ID de l'utilisateur
 * @param array $prefs Préférences ['email_notif_project_published', 'email_notif_new_comment', 'email_notif_new_review_available']
 * @return array ['success' => bool, 'error' => string|null]
 */
function updateNotificationPreferences($user_id, $prefs) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            UPDATE extra_proj_users 
            SET email_notif_project_published = ?,
                email_notif_new_comment = ?,
                email_notif_new_review_available = ?,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ');
        
        $stmt->execute([
            isset($prefs['email_notif_project_published']) ? 1 : 0,
            isset($prefs['email_notif_new_comment']) ? 1 : 0,
            isset($prefs['email_notif_new_review_available']) ? 1 : 0,
            $user_id
        ]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log('Update notifications error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Change le mot de passe d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param string $current_password Mot de passe actuel
 * @param string $new_password Nouveau mot de passe
 * @return array ['success' => bool, 'error' => string|null]
 */
function changeUserPassword($user_id, $current_password, $new_password) {
    global $pdo;
    
    // Validation
    if (strlen($new_password) < 8) {
        return ['success' => false, 'error' => 'Le nouveau mot de passe doit contenir au moins 8 caractères'];
    }
    
    // Récupérer le hash actuel
    $stmt = $pdo->prepare('SELECT password_hash FROM extra_proj_users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return ['success' => false, 'error' => 'Utilisateur non trouvé'];
    }
    
    // Vérifier le mot de passe actuel
    if (!password_verify($current_password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Le mot de passe actuel est incorrect'];
    }
    
    try {
        // Hasher le nouveau mot de passe
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Mettre à jour
        $stmt = $pdo->prepare('
            UPDATE extra_proj_users 
            SET password_hash = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ');
        
        $stmt->execute([$new_hash, $user_id]);
        
        return ['success' => true];
        
    } catch (Exception $e) {
        error_log('Change password error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors du changement de mot de passe'];
    }
}