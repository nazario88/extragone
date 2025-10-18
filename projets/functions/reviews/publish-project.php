<?php
include '../../../includes/config.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

// Vérifier que l'utilisateur est reviewer
requireRole('reviewer');

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /reviewer/dashboard');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: /reviewer/dashboard');
    exit;
}

$user = getCurrentUser();
$project_id = (int)($_POST['project_id'] ?? 0);
$meta_description = sanitizeInput($_POST['meta_description'] ?? '');
$review_text = sanitizeInput($_POST['review_text'] ?? '');
$youtube_video_id = sanitizeInput($_POST['youtube_video_id'] ?? '');
$cover_image_id = (int)($_POST['cover_image_id'] ?? 0);

// Validation
if (empty($meta_description) || empty($review_text)) {
    $_SESSION['error'] = 'La meta description et le texte de review sont obligatoires.';
    header('Location: /reviewer/review/' . $project_id);
    exit;
}

if (strlen($meta_description) > 300) {
    $_SESSION['error'] = 'La meta description ne peut pas dépasser 300 caractères.';
    header('Location: /reviewer/review/' . $project_id);
    exit;
}

// Vérifier que le projet est assigné au reviewer
$stmt = $pdo->prepare('
    SELECT * FROM extra_proj_projects 
    WHERE id = ? AND reviewer_id = ? AND status = "in_review"
');
$stmt->execute([$project_id, $user['id']]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    $_SESSION['error'] = 'Projet non trouvé ou non assigné à toi.';
    header('Location: /reviewer/dashboard');
    exit;
}

try {
    // Mettre à jour l'image de couverture si changée
    if ($cover_image_id > 0) {
        // Retirer le flag cover des autres images
        $stmt = $pdo->prepare('UPDATE extra_proj_images SET is_cover = 0 WHERE project_id = ?');
        $stmt->execute([$project_id]);
        
        // Mettre le nouveau cover
        $stmt = $pdo->prepare('UPDATE extra_proj_images SET is_cover = 1 WHERE id = ? AND project_id = ?');
        $stmt->execute([$cover_image_id, $project_id]);
        
        // Récupérer le filepath de la nouvelle cover
        $stmt = $pdo->prepare('SELECT filepath FROM extra_proj_images WHERE id = ?');
        $stmt->execute([$cover_image_id]);
        $cover_image_path = $stmt->fetchColumn();
    } else {
        $cover_image_path = $project['cover_image'];
    }
    
    // Publier le projet
    $stmt = $pdo->prepare('
        UPDATE extra_proj_projects 
        SET status = "published",
            meta_description = ?,
            review_text = ?,
            review_date = CURRENT_TIMESTAMP,
            published_at = CURRENT_TIMESTAMP,
            youtube_video_id = ?,
            cover_image = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ');
    
    $stmt->execute([
        $meta_description,
        $review_text,
        $youtube_video_id ?: null,
        $cover_image_path,
        $project_id
    ]);
    
    // Log de l'action
    logAction('publish_project', $user['id'], $project_id);
    
    // Notifier l'auteur du projet
    include_once '../../includes/email.php';
    $stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE id = ?');
    $stmt->execute([$project['user_id']]);
    $project_owner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $_SESSION['success'] = 'Projet publié avec succès ! 🎉';
    header('Location: /projet/' . $project['slug']);
    exit;
    
} catch (Exception $e) {
    error_log('Publish project error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de la publication.';
    header('Location: /reviewer/review/' . $project_id);
    exit;
}
?>