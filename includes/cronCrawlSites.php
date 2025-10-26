<?php
// =======================================================
// Script de vérification des liens eXtragone
// =======================================================

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
set_time_limit(0);
ini_set('memory_limit', '512M');

include('config.php');

$contactEmail = $_ENV['CONTACT_EMAIL'];

// --- FONCTION POUR TESTER UN LIEN --- //
function getHttpStatus($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_RANGE => '0-1024',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36'
    ]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode;
}

// --- RÉCUPÉRATION DE 50 OUTILS LES PLUS ANCIENS --- //
$stmt = $pdo->query("
    SELECT id, nom, url 
    FROM extra_tools
    WHERE is_valid=1
    ORDER BY date_last_check ASC
    LIMIT 50
");
$tools = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- TEST DES LIENS --- //
$errors = [];
foreach ($tools as $tool) {
    $status = getHttpStatus($tool['url']);
    $now = date('Y-m-d H:i:s');

    // Mise à jour de la date de dernier check
    $update = $pdo->prepare("UPDATE extra_tools SET date_last_check = ? WHERE id = ?");
    $update->execute([$now, $tool['id']]);

    // Si erreur HTTP
    if (in_array($status, [0, 404, 500, 502, 503, 504])) {
        $errors[] = [
            'id' => $tool['id'],
            'nom' => $tool['nom'],
            'url' => $tool['url'],
            'status' => $status ?: 'Inaccessible'
        ];
    }

    usleep(300000); // 0.3 sec pour éviter d'être bloqué
}

// --- CONSTRUCTION DU RAPPORT --- //
if (empty($errors)) {
    $subject = "[eXtragone] Vérification hebdo : tout est OK ✅";
    $message = "Aucun lien en erreur détecté dans ce lot.";
} else {
    $subject = "[eXtragone] Vérification hebdo : " . count($errors) . " erreurs détectées ⚠️";
    $message = "Les liens suivants sont en erreur :\n\n";
    foreach ($errors as $err) {
        $message .= "ID {$err['id']} - {$err['nom']} ({$err['url']}) → Code HTTP : {$err['status']}\n";
    }
}

$headers = "From: eXtragone <no-reply@extrag.one>\r\n";
mail($contactEmail, $subject, $message, $headers);

echo "Rapport envoyé à $contactEmail (".count($tools)." outils vérifiés)\n";
?>
