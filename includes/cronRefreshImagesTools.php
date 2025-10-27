<?php
/* 
 * Utilisation One Shot ? Forcer le refresh du cache des images des sites
 * https://www.extrag.one/includes/cronRefreshImagesTools.php?token=Ninja44
*/
set_time_limit(0);
ini_set('memory_limit', '512M');

include('config.php');

$validToken = 'Ninja44';
if (!isset($_GET['token']) || $_GET['token'] !== $validToken) {
    http_response_code(403);
    die('Bah non.');
}

define('CACHE_DIR', '../cache/tool-images/');

// Récupère tous les outils
$stmt = $pdo->query('SELECT id FROM extra_tools WHERE logo IS NOT NULL AND screenshot IS NOT NULL');
$tools = $stmt->fetchAll(PDO::FETCH_COLUMN);

$generated = 0;
$errors = [];

echo "<h2>Régénération des images...</h2>";
echo "<ul>";

foreach ($tools as $toolId) {
    $cacheFile = CACHE_DIR . "tool_{$toolId}.jpg";
    
    // Supprime l'ancien cache
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
    
    // Régénère en appelant le script

    $url = "https://www.extrag.one/genImgTool.php?id={$toolId}&refresh=1";
    $result = @file_get_contents($url);
    
    if ($result !== false) {
        $generated++;
        echo "<li>✅ Tool #{$toolId} régénéré</li>";
    } else {
        $errors[] = $toolId;
        echo "<li>❌ Tool #{$toolId} erreur</li>";
    }
    
    flush();
    ob_flush();
}

echo "</ul>";
echo "<p><strong>{$generated} images régénérées</strong></p>";

if (count($errors) > 0) {
    echo "<p>Erreurs : " . implode(', ', $errors) . "</p>";
}
?>