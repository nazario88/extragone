<?php
include '../../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /devenir-reviewer');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: /devenir-reviewer');
    exit;
}

$user = getCurrentUser();

// Vérifier si l'utilisateur est déjà reviewer
if (isReviewer()) {
    $_SESSION['info'] = 'Tu es déjà reviewer !';
    header('Location: /reviewer/dashboard');
    exit;
}

$motivation = sanitizeInput($_POST['motivation'] ?? '');

// Validation
if (empty($motivation)) {
    $_SESSION['error'] = 'La motivation est obligatoire.';
    header('Location: /devenir-reviewer');
    exit;
}

if (strlen($motivation) < 100) {
    $_SESSION['error'] = 'Ta motivation doit contenir au moins 100 caractères.';
    header('Location: /devenir-reviewer');
    exit;
}

// Vérifier si une demande existe déjà
$stmt = $pdo->prepare('SELECT id FROM extra_proj_reviewer_requests WHERE user_id = ? AND status = "pending"');
$stmt->execute([$user['id']]);
if ($stmt->fetch()) {
    $_SESSION['error'] = 'Tu as déjà une candidature en cours.';
    header('Location: /devenir-reviewer');
    exit;
}

try {
    // Insérer la demande
    $stmt = $pdo->prepare('
        INSERT INTO extra_proj_reviewer_requests (user_id, motivation) 
        VALUES (?, ?)
    ');
    $stmt->execute([$user['id'], $motivation]);
    
    // Log de l'action
    logAction('apply_reviewer', $user['id']);
    
    // Notifier l'équipe
    include_once '../../includes/email.php';
    sendReviewerApplicationEmail($user, $motivation);
    
    $_SESSION['success'] = 'Candidature envoyée ! Notre équipe va l\'examiner rapidement.';
    header('Location: /devenir-reviewer');
    exit;
    
} catch (Exception $e) {
    error_log('Apply reviewer error: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de l\'envoi de la candidature.';
    header('Location: /devenir-reviewer');
    exit;
}
?>