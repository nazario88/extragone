<?php

$allowed_origins = [
    'https://www.extrag.one',
    'https://nomi.extrag.one',
	'https://projets.extrag.one'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Gestion requête OPTIONS (préflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'config.php';

$cacheTime = '3600';
$cacheDir = '../cache/';
$nameFile = md5('nom_outils');

// Si fichier cache existe
if((file_exists($cacheDir.$nameFile)) && (time()-filemtime($cacheDir.$nameFile) < $cacheTime)) {
	$content = file_get_contents($cacheDir.$nameFile);
}
// Sinon, on le créé
else {
	ob_start();

	// Requête
	$stmt = $pdo->query("SELECT nom, description FROM extra_tools WHERE is_valid=1 ORDER BY nom ASC");

	// Récupération des résultats
	$outils = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Supprimer les espaces ou caractères invisibles autour des noms
	foreach ($outils as &$outil) {
		$outil['nom'] = trim($outil['nom']);
	}

	// Encodage JSON minifié
	echo json_encode($outils, JSON_UNESCAPED_UNICODE);
	$content = ob_get_contents();

	// On enregistre le cache
	file_put_contents($cacheDir.$nameFile, $content);

	ob_end_clean();
}

echo $content;
