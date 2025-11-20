<?php
/**
 * Analytics Endpoint - Collecte des données de tracking
 * Conforme RGPD : IP anonymisée, pas de cookies, pas de données personnelles
 */

// Headers CORS pour autoriser tous les sous-domaines
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Inclure la config DB
require_once __DIR__ . '/config.php';

// Récupérer les données JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['site_url'], $data['page_url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

try {
    // === ANONYMISATION IP (RGPD) ===
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $ip_parts = explode('.', $ip);
    if (count($ip_parts) === 4) {
        // IPv4 : supprimer le dernier octet
        $ip_parts[3] = '0';
        $ip_anonymized = implode('.', $ip_parts);
    } else {
        // IPv6 : supprimer les 3 derniers blocs
        $ip_anonymized = substr($ip, 0, strrpos($ip, ':')) . ':0000';
    }
    
    // === HASH VISITEUR (sans cookie) ===
    $visitor_hash = hash('sha256', 
        $ip_anonymized . 
        ($data['user_agent'] ?? '') . 
        date('Y-m-d') // Change chaque jour
    );
    
    // === EXTRACTION DU REFERRER ===
    $referrer_url = $data['referrer_url'] ?? null;
    $referrer_domain = null;
    if ($referrer_url) {
        $parsed = parse_url($referrer_url);
        $referrer_domain = $parsed['host'] ?? null;
    }
    
    // === DÉTECTION DEVICE ===
    $user_agent = $data['user_agent'] ?? '';
    $device_type = detectDeviceType($user_agent);
    $browser = detectBrowser($user_agent);
    $os = detectOS($user_agent);
    
    // === GÉOLOCALISATION (ip-api.com - gratuit) ===
    $geo_data = getGeoData($ip);
    
    // === INSERTION EN BASE ===
    $stmt = $pdo->prepare('
        INSERT INTO analytics (
            visitor_hash, 
            site_url, 
            page_url, 
            page_path,
            referrer_url,
            referrer_domain,
            country_code,
            country_name,
            city,
            user_agent,
            device_type,
            browser,
            os,
            ip_anonymized,
            visited_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ');
    
    $stmt->execute([
        $visitor_hash,
        $data['site_url'],
        $data['page_url'],
        $data['page_path'],
        $referrer_url,
        $referrer_domain,
        $geo_data['country_code'],
        $geo_data['country_name'],
        $geo_data['city'],
        $user_agent,
        $device_type,
        $browser,
        $os,
        $ip_anonymized
    ]);
    
    // Réponse succès
    http_response_code(204); // No Content
    
} catch (Exception $e) {
    error_log('Analytics error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}

// =================== FONCTIONS UTILITAIRES ===================

/**
 * Détecte le type d'appareil
 */
function detectDeviceType($user_agent) {
    if (preg_match('/bot|crawl|spider|slurp/i', $user_agent)) {
        return 'bot';
    }
    if (preg_match('/mobile|android|iphone/i', $user_agent)) {
        return 'mobile';
    }
    if (preg_match('/tablet|ipad/i', $user_agent)) {
        return 'tablet';
    }
    return 'desktop';
}

/**
 * Détecte le navigateur
 */
function detectBrowser($user_agent) {
    if (strpos($user_agent, 'Firefox') !== false) return 'Firefox';
    if (strpos($user_agent, 'Edg') !== false) return 'Edge';
    if (strpos($user_agent, 'Chrome') !== false) return 'Chrome';
    if (strpos($user_agent, 'Safari') !== false) return 'Safari';
    if (strpos($user_agent, 'Opera') !== false) return 'Opera';
    return 'Other';
}

/**
 * Détecte le système d'exploitation
 */
function detectOS($user_agent) {
    if (strpos($user_agent, 'Windows') !== false) return 'Windows';
    if (strpos($user_agent, 'Mac') !== false) return 'macOS';
    if (strpos($user_agent, 'Linux') !== false) return 'Linux';
    if (strpos($user_agent, 'Android') !== false) return 'Android';
    if (strpos($user_agent, 'iOS') !== false || strpos($user_agent, 'iPhone') !== false) return 'iOS';
    return 'Other';
}

/**
 * Récupère les données de géolocalisation via ip-api.com
 * Gratuit : 45 requêtes/minute
 */
function getGeoData($ip) {
    // Valeurs par défaut
    $default = [
        'country_code' => null,
        'country_name' => null,
        'city' => null
    ];
    
    // Ne pas géolocaliser les IPs locales
    if (empty($ip) || $ip === '127.0.0.1' || strpos($ip, '192.168.') === 0) {
        return $default;
    }
    
    try {
        // Appel API avec timeout court
        $context = stream_context_create([
            'http' => [
                'timeout' => 2, // 2 secondes max
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents(
            "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,city",
            false,
            $context
        );
        
        if ($response) {
            $geo = json_decode($response, true);
            
            if ($geo && $geo['status'] === 'success') {
                return [
                    'country_code' => $geo['countryCode'] ?? null,
                    'country_name' => $geo['country'] ?? null,
                    'city' => $geo['city'] ?? null
                ];
            }
        }
    } catch (Exception $e) {
        // Échec silencieux
        error_log('Geo API error: ' . $e->getMessage());
    }
    
    return $default;
}