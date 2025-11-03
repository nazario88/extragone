<?php
include '../../../includes/config.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://projets.extrag.one/reglages');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: https://projets.extrag.one/reglages');
    exit;
}

$user = getCurrentUser();

// Récupération des données
$display_name = sanitizeInput($_POST['display_name'] ?? '');
$bio = sanitizeInput($_POST['bio'] ?? '');
$external_link = sanitizeInput($_POST['external_link'] ?? '');

// Validation
if (empty($display_name)) {
    $_SESSION['error'] = 'Le nom d\'affichage ne peut pas être vide.';
    header('Location: https://projets.extrag.one/reglages');
    exit;
}

if (strlen($display_name) > 100) {
    $_SESSION['error'] = 'Le nom d\'affichage ne peut pas dépasser 100 caractères.';
    header('Location: https://projets.extrag.one/reglages');
    exit;
}

if (strlen($bio) > 500) {
    $_SESSION['error'] = 'La bio ne peut pas dépasser 500 caractères.';
    header('Location: https://projets.extrag.one/reglages');
    exit;
}

if (!empty($external_link) && !filter_var($external_link, FILTER_VALIDATE_URL)) {
    $_SESSION['error'] = 'Le lien externe n\'est pas valide.';
    header('Location: https://projets.extrag.one/reglages');
    exit;
}

try {
    // Gestion de l'avatar
    $avatar_path = $user['avatar'];
    
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2 Mo
        
        if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
            $_SESSION['error'] = 'Type de fichier non autorisé pour l\'avatar.';
            header('Location: https://projets.extrag.one/reglages');
            exit;
        }
        
        if ($_FILES['avatar']['size'] > $max_size) {
            $_SESSION['error'] = 'L\'avatar ne peut pas dépasser 2 Mo.';
            header('Location: https://projets.extrag.one/reglages');
            exit;
        }
        
        // Supprimer l'ancien avatar si existe
        if ($user['avatar'] && file_exists(__DIR__ . '/../..' . $user['avatar'])) {
            unlink(__DIR__ . '/../..' . $user['avatar']);
        }
        
        // Générer un nom unique
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $user['id'] . '_' . uniqid() . '.' . $extension;
        $upload_dir = __DIR__ . '/../../uploads/avatars/';
        
        // Créer le dossier si nécessaire
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filepath = $upload_dir . $filename;
        $relative_path = '/uploads/avatars/' . $filename;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filepath)) {
            $avatar_path = $relative_path;
        }
    }
    
    // Mettre à jour le profil
    $stmt = $pdo->prepare('
        UPDATE extra_proj_users 
        SET display_name = ?, bio = ?, external_link = ?, avatar = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ');
    
    $stmt->execute([
        $display_name,
        $bio ?: null,
        $external_link ?: null,
        $avatar_path,
        $user['id']
    ]);
    
    // Log de l'action
    logAction('update_profile', $user['id']);
    
    $_SESSION['success'] = 'Profil mis à jour avec succès !';
    header('Location: https://projets.extrag.one/reglages');
    exit;
    
} catch (Exception $e) {
    error_log('Update profile error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de la mise à jour du profil.';
    header('Location: https://projets.extrag.one/reglages');
    exit;
}
?>