<?php
include '../../../includes/config.php';
include '../../includes/auth.php';
include '../../includes/functions.php';
include '../../includes/email.php';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: '.$base.'connexion');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: '.$base.'connexion');
    exit;
}

// Récupération des données
$email = sanitizeInput($_POST['email'] ?? '');
$username = sanitizeInput($_POST['username'] ?? '');
$display_name = sanitizeInput($_POST['display_name'] ?? '');
$password = $_POST['password'] ?? '';

// Validation basique
if (empty($email) || empty($username) || empty($password)) {
    $_SESSION['error'] = 'Tous les champs obligatoires doivent être remplis.';
    header('Location: '.$base.'connexion');
    exit;
}

// Inscription
$result = registerUser($email, $password, $username, $display_name);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    header('Location: '.$base.'connexion');
    exit;
}

// Récupérer l'utilisateur créé pour l'email
$stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE id = ?');
$stmt->execute([$result['user_id']]);
$new_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Envoyer l'email de bienvenue
if ($new_user) {
    sendWelcomeEmail($new_user);
}

// Connexion automatique après inscription
loginUser($result['user_id']);

$_SESSION['success'] = 'Compte créé avec succès ! Bienvenue sur Projets eXtragone.';

header('Location: '.$base);
exit;
?>