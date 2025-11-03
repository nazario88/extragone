<?php
include '../../../includes/config.php';
include '../../includes/auth.php';
include '../../includes/functions.php';

// Vérifier que l'utilisateur est reviewer
requireRole('reviewer');

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://projets.extrag.one/reviewer/dashboard');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: https://projets.extrag.one/reviewer/dashboard');
    exit;
}

$user = getCurrentUser();
$project_id = (int)($_POST['project_id'] ?? 0);

// Vérifier que le projet existe et est en statut "draft"
$stmt = $pdo->prepare('SELECT * FROM extra_proj_projects WHERE id = ? AND status = "draft"');
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    $_SESSION['error'] = 'Projet non trouvé ou déjà pris en charge.';
    header('Location: https://projets.extrag.one/reviewer/dashboard');
    exit;
}

try {
    // Mettre à jour le projet
    $stmt = $pdo->prepare('
        UPDATE extra_proj_projects 
        SET status = "in_review", reviewer_id = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ? AND status = "draft"
    ');
    $stmt->execute([$user['id'], $project_id]);
    
    // Vérifier que la mise à jour a bien eu lieu
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = 'Ce projet a déjà été pris en charge par un autre reviewer.';
        header('Location: https://projets.extrag.one/reviewer/dashboard');
        exit;
    }
    
    // Log de l'action
    logAction('claim_review', $user['id'], $project_id);
    
    $_SESSION['success'] = 'Projet pris en charge ! Tu peux maintenant rédiger ta review.';
    header('Location: https://projets.extrag.one/reviewer/review/' . $project_id);
    exit;
    
} catch (Exception $e) {
    error_log('Claim review error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de la prise en charge.';
    header('Location: https://projets.extrag.one/reviewer/dashboard');
    exit;
}
?>