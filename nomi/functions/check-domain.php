<?php
header('Content-Type: application/json');

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['domain'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Domain parameter required']);
    exit;
}

$domain = strtolower(trim($input['domain']));

// Validation du domaine
if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
    echo json_encode(['error' => 'Invalid domain format', 'available' => false]);
    exit;
}

try {
    $available = checkDomainAvailability($domain);
    echo json_encode(['available' => $available, 'domain' => $domain]);
} catch (Exception $e) {
    error_log('Domain check error: ' . $e->getMessage());
    echo json_encode(['error' => 'Check failed', 'available' => false]);
}

/**
 * Vérifie la disponibilité d'un domaine
 * Utilise plusieurs méthodes pour une meilleure fiabilité
 */
function checkDomainAvailability($domain) {
    // Méthode 1: DNS lookup (plus rapide)
    $dnsResult = checkDomainDNS($domain);
    
    // Méthode 2: WHOIS lookup (plus fiable mais plus lent)
    $whoisResult = checkDomainWhois($domain);
    
    // Si DNS dit que le domaine n'existe pas ET whois confirme, alors disponible
    return !$dnsResult && !$whoisResult;
}

/**
 * Vérification DNS - rapide mais pas toujours fiable
 */
function checkDomainDNS($domain) {
    // Vérifier les enregistrements A et AAAA
    $ipv4 = dns_get_record($domain, DNS_A);
    $ipv6 = dns_get_record($domain, DNS_AAAA);
    
    // Si on trouve des enregistrements IP, le domaine existe
    return !empty($ipv4) || !empty($ipv6);
}

/**
 * Vérification WHOIS - plus fiable mais plus lente
 */
function checkDomainWhois($domain) {
    // Déterminer le serveur WHOIS selon l'extension
    $tld = substr(strrchr($domain, '.'), 1);
    $whoisServer = getWhoisServer($tld);
    
    if (!$whoisServer) {
        return false; // Impossible de vérifier
    }
    
    try {
        $whoisData = queryWhoisServer($whoisServer, $domain);
        
        // Analyser la réponse WHOIS
        return analyzeWhoisResponse($whoisData);
        
    } catch (Exception $e) {
        // En cas d'erreur WHOIS, fallback sur DNS
        return checkDomainDNS($domain);
    }
}

/**
 * Obtient le serveur WHOIS selon l'extension
 */
function getWhoisServer($tld) {
    $servers = [
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'fr' => 'whois.afnic.fr',
        'io' => 'whois.nic.io',
        'co' => 'whois.nic.co',
        'me' => 'whois.nic.me',
        'tv' => 'whois.nic.tv',
        'cc' => 'whois.nic.cc'
    ];
    
    return $servers[$tld] ?? null;
}

/**
 * Requête au serveur WHOIS
 */
function queryWhoisServer($server, $domain) {
    $connection = fsockopen($server, 43, $errno, $errstr, 10);
    
    if (!$connection) {
        throw new Exception("Cannot connect to WHOIS server: $errstr");
    }
    
    fwrite($connection, $domain . "\r\n");
    
    $response = '';
    while (!feof($connection)) {
        $response .= fgets($connection, 128);
    }
    
    fclose($connection);
    
    return $response;
}

/**
 * Analyse la réponse WHOIS pour déterminer si le domaine est pris
 */
function analyzeWhoisResponse($whoisData) {
    $whoisData = strtolower($whoisData);
    
    // Indicateurs que le domaine est DISPONIBLE
    $availableIndicators = [
        'no match',
        'not found',
        'no data found',
        'no entries found',
        'domain available',
        'no matching record',
        'not registered',
        'available for registration'
    ];
    
    // Indicateurs que le domaine est PRIS
    $takenIndicators = [
        'creation date',
        'created:',
        'registered:',
        'registration date',
        'domain status: ok',
        'registrar:',
        'name server'
    ];
    
    // Vérifier les indicateurs de disponibilité
    foreach ($availableIndicators as $indicator) {
        if (strpos($whoisData, $indicator) !== false) {
            return false; // Disponible
        }
    }
    
    // Vérifier les indicateurs de prise
    foreach ($takenIndicators as $indicator) {
        if (strpos($whoisData, $indicator) !== false) {
            return true; // Pris
        }
    }
    
    // Si aucun indicateur clair, considérer comme pris par sécurité
    return true;
}
?>