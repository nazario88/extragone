<?php
/**
 * Système d'authentification pour projets.extrag.one
 */

// Démarrer la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Récupère les informations de l'utilisateur connecté
 */
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE id = ? AND is_active = 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 */
function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    return $user['role'] === $role || $user['role'] === 'admin';
}

/**
 * Vérifie si l'utilisateur est reviewer
 */
function isReviewer() {
    return hasRole('reviewer');
}

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Redirige vers la page de connexion si non connecté
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: connexion');
        exit;
    }
}

/**
 * Redirige si l'utilisateur n'a pas le rôle requis
 */
function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        $_SESSION['error'] = 'Accès non autorisé.';
        header('Location: https://projets.extrag.one');
        exit;
    }
}

/**
 * Connecte un utilisateur
 */
function loginUser($user_id) {
    global $pdo;
    
    $_SESSION['user_id'] = $user_id;
    $_SESSION['login_time'] = time();
    
    // Créer une session en base
    $session_id = session_id();
    $ip = getIP();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $pdo->prepare('
        INSERT INTO extra_proj_sessions (id, user_id, ip_address, user_agent) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE last_activity = CURRENT_TIMESTAMP
    ');
    $stmt->execute([$session_id, $user_id, $ip, $user_agent]);
    
    // Log de connexion
    logAction('login', $user_id);
}

/**
 * Déconnecte l'utilisateur
 */
function logoutUser() {
    global $pdo;
    
    if (isLoggedIn()) {
        // Supprimer la session en base
        $session_id = session_id();
        $stmt = $pdo->prepare('DELETE FROM extra_proj_sessions WHERE id = ?');
        $stmt->execute([$session_id]);
        
        // Log de déconnexion
        logAction('logout', $_SESSION['user_id']);
    }
    
    // Détruire la session PHP
    session_unset();
    session_destroy();
}

/**
 * Inscription d'un nouvel utilisateur
 */
function registerUser($email, $password, $username, $display_name = null) {
    global $pdo;
    
    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Email invalide'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caractères'];
    }
    
    if (!preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $username)) {
        return ['success' => false, 'error' => 'Nom d\'utilisateur invalide (3-50 caractères, lettres, chiffres, _ et - uniquement)'];
    }
    
    // Vérifier si email existe déjà
    $stmt = $pdo->prepare('SELECT id FROM extra_proj_users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Cet email est déjà utilisé'];
    }
    
    // Vérifier si username existe déjà
    $stmt = $pdo->prepare('SELECT id FROM extra_proj_users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Ce nom d\'utilisateur est déjà pris'];
    }
    
    // Créer le compte
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $display_name = $display_name ?: $username;
    
    $stmt = $pdo->prepare('
        INSERT INTO extra_proj_users (email, password_hash, username, display_name) 
        VALUES (?, ?, ?, ?)
    ');
    
    try {
        $stmt->execute([$email, $password_hash, $username, $display_name]);
        $user_id = $pdo->lastInsertId();
        
        // Log de création de compte
        logAction('register', $user_id);
        
        return ['success' => true, 'user_id' => $user_id];
    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de la création du compte'];
    }
}

/**
 * Authentification d'un utilisateur
 */
function authenticateUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return ['success' => false, 'error' => 'Email ou mot de passe incorrect'];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Email ou mot de passe incorrect'];
    }
    
    return ['success' => true, 'user' => $user];
}

/**
 * Génère un token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log une action utilisateur
 */
function logAction($action, $user_id = null, $project_id = null, $details = null) {
    global $pdo;
    
    $ip = getIP();
    $details_json = $details ? json_encode($details) : null;
    
    $stmt = $pdo->prepare('
        INSERT INTO extra_proj_logs (action, user_id, project_id, user_ip, details) 
        VALUES (?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([$action, $user_id, $project_id, $ip, $details_json]);
}

/**
 * Nettoie les anciennes sessions (à exécuter périodiquement)
 */
function cleanOldSessions() {
    global $pdo;
    
    // Supprimer les sessions inactives depuis plus de 30 jours
    $stmt = $pdo->prepare('
        DELETE FROM extra_proj_sessions 
        WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ');
    $stmt->execute();
}
?>