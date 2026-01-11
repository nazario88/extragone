<?php
/*
Endpoint pour récupérer les alternatives d'un outil donné (plugin Chrome)
Utilisé aussi pour l'API n8n.

On doit passer en GET le paramètre "site", qui est l'URL de l'outil à vérifier
Exemple : https://www.extrag.one/includes/get-alternatives.php?site=https://www.example.com
——————————————————————————————————————————————————*/


header('Content-Type: application/json');

// Si on affiche la page dans le site
if (!isset($_GET['site'])) {
    echo json_encode(['error' => 'Paramètre "site" manquant']);
    http_response_code(400);
    exit;
}

include('config.php');

$site = strtolower(trim($_GET['site']));
$urlParts = parse_url($site);

$host = str_replace("www.", "", $urlParts['host'] ?? '');
$path = $urlParts['path'] ?? '';

// Exclusions pour les sous domaines différents du domaine majeur
$exclusions = [
    'mail.google.com',
    'analytics.google.com',
    'docs.google.com',
    'photos.google.com',
    'keep.google.com',
    'drive.google.com',
    'maps.google.com',
];

// 1. Cas particulier : Google Maps (google.com/maps/...)
if ($host === 'google.com' && str_starts_with(ltrim($path, '/'), 'maps')) {
    $domain = 'maps.google.com';
}
// 2. Autres exclusions classiques
elseif (in_array($host, $exclusions)) {
    $domain = $host;
}
// 3. Sinon on prend juste le domaine principal (ex: sub.site.com → site.com)
else {
    $parts = explode('.', $host);
    $count = count($parts);
    if ($count >= 2) {
        $domain = $parts[$count - 2] . '.' . $parts[$count - 1];
    } else {
        $domain = $host;
    }
}

/* Conversions pour bien matcher les résultats
——————————————————————————————————————————————————*/
if($domain == "google.com") $domain = "google.fr";
if($domain == "notion.com") $domain = "notion.so";
if($domain == "bubbleapps.io") $domain = "bubble.io";
if($domain == "openai.com") $domain = "chatgpt.com";


// Si on a un domaine, c'est OK
if($domain) {
    $search = '%' . $domain . '%';
}
// Sinon, c'est qu'on est dans les nuages (nouvel onglet, etc)
else {
    exit;
}

/* Vérifier si l'outil est référencé
——————————————————————————————————————————————————*/
$sql = $pdo->prepare("SELECT id, url, is_french FROM extra_tools WHERE is_valid=1 AND url LIKE :site");
$sql->execute([':site' => $search]);
$ifTool = $sql->fetch();
// Si pas d'outil
if(!$ifTool['id']) {
    echo json_encode(['no_tool' => 'Pas d\'outil trouvé']);
    
    // On enregistre la log
    $sql = $pdo->prepare('INSERT INTO extra_logs (date, content) VALUES(now(), ?)');
    $log = 'Outil non trouvé via API : '.$search;
    $sql->execute(array($log));

    exit;
}

if($ifTool['is_french']) {
    echo json_encode(['is_french' => 'Outil français !']);
    exit;
}

// Si outil, on récupère les alternatives
// 2026/01/11 : Ajout de la descrption, + is_free + is_paid
$sql = $pdo->prepare("
    SELECT C.nom, C.logo, C.slug, C.description, C.is_free, C.is_paid FROM extra_alternatives A
    INNER JOIN extra_tools B ON B.id=A.id_outil
    INNER JOIN extra_tools C ON C.id=A.id_alternative
    WHERE A.id_outil = :id_tool
    ORDER BY B.nom ASC
    ");
$sql->execute([':id_tool' => $ifTool['id']]);
$alternativesTools = $sql->fetchAll();

$alternatives = [
    $site => $alternativesTools
];

// On renvoi en JSON
if (count($alternativesTools) > 0) {
    echo json_encode([
        'site' => $site,
        'alternatives' => $alternativesTools
    ]);
} else {
    echo json_encode([
        'site' => $site,
        'alternatives' => []
    ]);
}