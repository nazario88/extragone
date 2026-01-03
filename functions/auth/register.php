<?php
/**
 * Traitement de l'inscription utilisateur
 * Utilise le système d'authentification centralisé
 */

// Charger la config et l'authentification centralisées
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/email.php';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: https://projets.extrag.one/connexion');
    exit;
}

// Vérifier le token CSRF
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token de sécurité invalide.';
    header('Location: https://projets.extrag.one/connexion');
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
    header('Location: https://projets.extrag.one/connexion');
    exit;
}

// Inscription via la fonction centralisée
$result = registerUser($email, $password, $username, $display_name);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    header('Location: https://projets.extrag.one/connexion');
    exit;
}

// ✅ INSCRIPTION RÉUSSIE
// Récupérer l'utilisateur créé pour l'email
$stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE id = ?');
$stmt->execute([$result['user_id']]);
$new_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Envoyer l'email de bienvenue
if ($new_user) {
    sendWelcomeEmail($new_user);
}

// Connexion automatique après inscription
// Appeler loginUser() pour créer la session ET le cookie
loginUser($result['user_id']);

$_SESSION['success'] = 'Compte créé avec succès ! Bienvenue sur Projets eXtragone.';

header('Location: https://projets.extrag.one');
exit;