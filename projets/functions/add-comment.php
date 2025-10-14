<?php
include '../includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

$user = getCurrentUser();
$project_id = (int)($_POST['project_id'] ?? 0);
$content = sanitizeInput($_POST['content'] ?? '');

// Validation
if (empty($content)) {
    $_SESSION['error'] = 'Le commentaire ne peut pas être vide.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

if (strlen($content) > 2000) {
    $_SESSION['error'] = 'Le commentaire ne peut pas dépasser 2000 caractères.';
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

// Vérifier que le projet existe
$stmt = $pdo->prepare('SELECT slug FROM extra_proj_projects WHERE id = ? AND status = "published"');
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    $_SESSION['error'] = 'Projet non trouvé.';
    header('Location: /');
    exit;
}

try {
    // Insérer le commentaire
    $stmt = $pdo->prepare('
        INSERT INTO extra_proj_comments (project_id, user_id, content) 
        VALUES (?, ?, ?)
    ');
    $stmt->execute([$project_id, $user['id'], $content]);
    
    // Log de l'action
    logAction('add_comment', $user['id'], $project_id);
    
    $_SESSION['success'] = 'Commentaire publié !';
    header('Location: /projet/' . $project['slug'] . '#comment-' . $pdo->lastInsertId());
    exit;
    
} catch (Exception $e) {
    error_log('Add comment error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de la publication du commentaire.';
    header('Location: /projet/' . $project['slug']);
    exit;
}
?>