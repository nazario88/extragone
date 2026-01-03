<?php
/**
 * Système d'authentification centralisé pour l'écosystème eXtragone
 * Utilise la table extra_proj_users et extra_proj_sessions
 * Compatible avec www.extrag.one et projets.extrag.one
 */

// Démarrer la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// RESTAURATION AUTOMATIQUE DE LA SESSION
// ========================================
// Si l'utilisateur n'est pas en session mais a un cookie valide,
// restaurer automatiquement la session
if (!isset($_SESSION['user_id']) && isset($_COOKIE['session_token'])) {
    $session_token = $_COOKIE['session_token'];
    
    try {
        // Vérifier si le token est valide
        $stmt = $pdo->prepare('
            SELECT s.user_id, u.* 
            FROM extra_proj_sessions s
            JOIN extra_proj_users u ON s.user_id = u.id
            WHERE s.session_token = ? 
            AND s.expires_at > NOW()
            AND u.is_active = 1
        ');
        $stmt->execute([$session_token]);
        $session_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($session_data) {
            // Restaurer la session
            $_SESSION['user_id'] = $session_data['user_id'];
            
            // Mettre à jour last_activity
            $stmt = $pdo->prepare('UPDATE extra_proj_sessions SET last_activity = NOW() WHERE session_token = ?');
            $stmt->execute([$session_token]);
        } else {
            // Token invalide ou expiré, supprimer le cookie
            setcookie('session_token', '', time() - 3600, '/', '.extrag.one', true, true);
        }
    } catch (Exception $e) {
        error_log('Session restoration error: ' . $e->getMessage());
    }
}

/**
 * Vérifie si un utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Récupère l'utilisateur connecté
 */
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE id = ? AND is_active = 1');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Get current user error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Authentifie un utilisateur
 */
function authenticateUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'error' => 'Email ou mot de passe incorrect.'];
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Email ou mot de passe incorrect.'];
        }
        
        return ['success' => true, 'user' => $user];
        
    } catch (Exception $e) {
        error_log('Authentication error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'authentification.'];
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
    try {
        $session_token = bin2hex(random_bytes(32));
        $ip = getIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $pdo->prepare('
            INSERT INTO extra_proj_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
        ');
        $stmt->execute([$user_id, $session_token, $ip, $user_agent]);
        
        // Stocker le token dans un cookie partagé
        $cookie_created = setcookie(
            'session_token',                    // nom
            $session_token,                      // valeur
            time() + (30 * 24 * 60 * 60),      // expire dans 30 jours
            '/',                                 // path
            '.extrag.one',                       // domain (avec le point)
            true,                                // secure (HTTPS uniquement)
            true                                 // httponly (pas accessible en JS)
        );
        
        // Log pour debug
        if ($cookie_created) {
            error_log("✅ LOGIN: Cookie session_token créé pour user_id=$user_id, domain=.extrag.one");
        } else {
            error_log("❌ LOGIN: Échec création cookie pour user_id=$user_id (headers déjà envoyés ?)");
        }
        
    } catch (Exception $e) {
        error_log('Create session error: ' . $e->getMessage());
    }
}

/**
 * Déconnecte un utilisateur
 */
function logoutUser() {
    global $pdo;
    
    // Supprimer la session en base
    if (isset($_COOKIE['session_token'])) {
        try {
            $stmt = $pdo->prepare('DELETE FROM extra_proj_sessions WHERE session_token = ?');
            $stmt->execute([$_COOKIE['session_token']]);
        } catch (Exception $e) {
            error_log('Delete session error: ' . $e->getMessage());
        }
        
        // Supprimer le cookie
        setcookie('session_token', '', time() - 3600, '/', '.extrag.one', true, true);
    }
    
    // Détruire la session PHP
    session_destroy();
    $_SESSION = [];
}

/**
 * Inscrit un nouvel utilisateur
 */
function registerUser($email, $password, $username, $display_name = '') {
    global $pdo;
    
    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Email invalide.'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caractères.'];
    }
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        return ['success' => false, 'error' => 'Le nom d\'utilisateur doit contenir entre 3 et 50 caractères.'];
    }
    
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        return ['success' => false, 'error' => 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, _ et -.'];
    }
    
    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare('SELECT id FROM extra_proj_users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Cet email est déjà utilisé.'];
        }
        
        // Vérifier si le username existe déjà
        $stmt = $pdo->prepare('SELECT id FROM extra_proj_users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Ce nom d\'utilisateur est déjà pris.'];
        }
        
        // Créer l'utilisateur
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $display_name = $display_name ?: $username;
        
        $stmt = $pdo->prepare('
            INSERT INTO extra_proj_users (email, username, display_name, password_hash) 
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$email, $username, $display_name, $password_hash]);
        
        return ['success' => true, 'user_id' => $pdo->lastInsertId()];
        
    } catch (Exception $e) {
        error_log('Registration error: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Erreur lors de l\'inscription.'];
    }
}

/**
 * Vérifie si l'utilisateur est un reviewer
 */
function isReviewer() {
    $user = getCurrentUser();
    return $user && in_array($user['role'], ['reviewer', 'admin']);
}

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

/**
 * Redirige vers la page de connexion si non connecté
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: https://www.extrag.one/connexion');
        exit;
    }
}

/**
 * Redirige vers la page de connexion si pas le bon rôle
 */
function requireRole($role) {
    requireLogin();
    $user = getCurrentUser();
    
    if ($role === 'reviewer' && !isReviewer()) {
        $_SESSION['error'] = 'Accès réservé aux reviewers.';
        header('Location: https://projets.extrag.one');
        exit;
    }
    
    if ($role === 'admin' && !isAdmin()) {
        $_SESSION['error'] = 'Accès réservé aux administrateurs.';
        header('Location: https://projets.extrag.one');
        exit;
    }
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
function logAction($action, $user_id = null, $project_id = null, $details = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO extra_proj_logs (action, user_id, project_id, user_ip, details) 
            VALUES (?, ?, ?, ?, ?)
        ');
        
        $user_id = $user_id ?: ($_SESSION['user_id'] ?? null);
        $ip = getIP();
        $details_json = !empty($details) ? json_encode($details) : null;
        
        $stmt->execute([$action, $user_id, $project_id, $ip, $details_json]);
    } catch (Exception $e) {
        error_log('Log action error: ' . $e->getMessage());
    }
}