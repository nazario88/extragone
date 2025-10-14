<?php
include '../../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /soumettre');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: /soumettre');
    exit;
}

$user = getCurrentUser();

// Récupération des données
$title = sanitizeInput($_POST['title'] ?? '');
$short_description = sanitizeInput($_POST['short_description'] ?? '');
$long_description = sanitizeInput($_POST['long_description'] ?? '');
$demo_link = sanitizeInput($_POST['demo_link'] ?? '');
$tools_used = sanitizeInput($_POST['tools_used'] ?? '');

// Validation
if (empty($title) || empty($short_description)) {
    $_SESSION['error'] = 'Le titre et la description courte sont obligatoires.';
    header('Location: /soumettre');
    exit;
}

if (strlen($title) > 200) {
    $_SESSION['error'] = 'Le titre ne peut pas dépasser 200 caractères.';
    header('Location: /soumettre');
    exit;
}

if (strlen($short_description) > 500) {
    $_SESSION['error'] = 'La description courte ne peut pas dépasser 500 caractères.';
    header('Location: /soumettre');
    exit;
}

// Validation du lien démo
if (!empty($demo_link) && !filter_var($demo_link, FILTER_VALIDATE_URL)) {
    $_SESSION['error'] = 'Le lien vers la démo n\'est pas valide.';
    header('Location: /soumettre');
    exit;
}

try {
    // Générer un slug unique
    $slug = generateSlug($title);
    
    // Transformer tools_used en JSON
    $tools_array = array_map('trim', explode(',', $tools_used));
    $tools_array = array_filter($tools_array); // Retirer les valeurs vides
    $tools_json = !empty($tools_array) ? json_encode($tools_array) : null;
    
    // Insérer le projet en base
    $stmt = $pdo->prepare('
        INSERT INTO extra_proj_projects 
        (user_id, slug, title, short_description, long_description, demo_link, tools_used, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, "draft")
    ');
    
    $stmt->execute([
        $user['id'],
        $slug,
        $title,
        $short_description,
        $long_description ?: null,
        $demo_link ?: null,
        $tools_json
    ]);
    
    $project_id = $pdo->lastInsertId();
    
    // Upload des images
    $uploaded_images = 0;
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < min($file_count, 5); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                // La première image est la cover
                $is_cover = ($i === 0);
                
                $result = uploadProjectImage($file, $project_id, $is_cover);
                
                if ($result['success']) {
                    $uploaded_images++;
                    
                    // Mettre à jour le cover_image du projet si c'est la première
                    if ($is_cover) {
                        $stmt = $pdo->prepare('UPDATE extra_proj_projects SET cover_image = ? WHERE id = ?');
                        $stmt->execute([$result['filepath'], $project_id]);
                    }
                }
            }
        }
    }
    
    // Log de l'action
    logAction('submit_project', $user['id'], $project_id, [
        'title' => $title,
        'images_count' => $uploaded_images
    ]);
    
    // TODO: Envoyer notification email aux reviewers (quand système mail configuré)
    
    $_SESSION['success'] = 'Projet soumis avec succès ! Il sera bientôt reviewé par notre équipe.';
    header('Location: /membre/' . $user['username']);
    exit;
    
} catch (Exception $e) {
    error_log('Project submission error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de la soumission. Veuillez réessayer.';
    header('Location: /soumettre');
    exit;
}
?>