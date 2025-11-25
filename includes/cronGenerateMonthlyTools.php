<?php
// generate-monthly-tools.php
// Objectif unique : sortir un JSON + une image mosaïque pour n8n

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

date_default_timezone_set('Europe/Paris');
header('Content-Type: application/json; charset=utf-8');

include __DIR__ . '/config.php'; // $pdo obligatoirement défini

// ================================================================
// 1. Récupération des outils du mois en cours
// ================================================================
$premierJourMoisEnCours = date('Y-m-01');

$sql = "
    SELECT 
        a.nom,
        a.slug,
        LEFT(a.description, 80) AS description_courte,
        a.logo,
        a.screenshot,
        a.is_french,
        a.is_free,
        a.is_paid,
        b.nom AS category
    FROM extra_tools a
    INNER JOIN extra_tools_categories b ON a.categorie_id = b.id
    WHERE a.date_creation >= ?
      AND a.is_valid = 1
    ORDER BY b.nom ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$premierJourMoisEnCours]);
$tools = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tools)) {
    echo json_encode([
        'success' => false,
        'message' => 'Aucun nouvel outil ce mois-ci',
        'count'   => 0
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Regroupement par catégorie pour n8n
$by_category = [];
foreach ($tools as $t) {
    $cat = $t['category'] ?: 'Autres';
    $by_category[$cat][] = $t;
}

// ================================================================
// 2. Génération de la mosaïque
// ================================================================
function generateMosaicAndReturnPath(array $tools): string {
    $width  = 1200;
    $height = 630;
    $cols   = 4;
    $rows   = 3;

    // Sélection de 12 outils avec screenshot (ou logo en secours)
    $candidates = array_filter($tools, fn($t) => !empty($t['screenshot']));
    if (count($candidates) < 12) {
        $withLogo = array_filter($tools, fn($t) => !empty($t['logo']));
        $candidates = array_merge($candidates, $withLogo);
    }
    shuffle($candidates);
    $selected = array_slice($candidates, 0, 12);

    $canvas = imagecreatetruecolor($width, $height);
    $white  = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white);

    // === Grille 4×3 de screenshots floutés ===
    $cellW = $width / $cols;
    $cellH = $height / $rows;

    foreach ($selected as $i => $tool) {
        $col = $i % $cols;
        $row = intdiv($i, $cols);
        $file = $tool['screenshot'] ?? $tool['logo'] ?? null;
        if (!$file) continue;

        $path = '/home/innospy/eXtragone/' . $file;
        if (!is_file($path)) continue;

        $src = match(strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png'  => imagecreatefrompng($path),
            'jpg','jpeg' => imagecreatefromjpeg($path),
            'webp' => imagecreatefromwebp($path),
            default => null
        };
        if (!$src) continue;

        // Cover + blur
        $tmp = imagecreatetruecolor((int)$cellW, (int)$cellH);
        $srcW = imagesx($src); $srcH = imagesy($src);
        $ratio = max($cellW / $srcW, $cellH / $srcH);
        $newW = (int)($srcW * $ratio);
        $newH = (int)($srcH * $ratio);
        imagecopyresampled($tmp, $src, ($cellW-$newW)/2, ($cellH-$newH)/2, 0, 0, $newW, $newH, $srcW, $srcH);

        for ($b = 0; $b < 15; $b++) imagefilter($tmp, IMG_FILTER_GAUSSIAN_BLUR);

        imagecopy($canvas, $tmp, (int)($col * $cellW), (int)($row * $cellH), 0, 0, (int)$cellW, (int)$cellH);
        imagedestroy($tmp);
        imagedestroy($src);
    }

    // Overlay sombre
    $overlay = imagecolorallocatealpha($canvas, 0, 0, 0, 58);
    imagefilledrectangle($canvas, 0, 0, $width, $height, $overlay);

    // === Boîte centrale noire arrondie ===
    $boxW = 1000;
    $boxH = 250;
    $boxX = (int)(($width  - $boxW) / 2);
    $boxY = (int)(($height - $boxH) / 2);

    $boxColor = imagecolorallocatealpha($canvas, 0, 0, 0, 53); // noir à ~60% d’opacité
    imagefilledrectangle($canvas, $boxX, $boxY, $boxX + $boxW, $boxY + $boxH, $boxColor);

        // === Textes parfaitement centrés (version corrigée, zéro chevauchement) ===
    $fontBold = __DIR__ . '/../assets/font/montserrat/Montserrat-Bold.ttf';
    $fontSemi = __DIR__ . '/../assets/font/montserrat/Montserrat-SemiBold.ttf';

    $mois   = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    $titre  = strtoupper("Outils de " . $mois[date('n')-1] . " " . date('Y'));
    $count  = count($tools);
    $sous   = $count . " " . ($count > 1 ? "nouveaux outils" : "nouvel outil") . " découverts ce mois-ci";

    $white = imagecolorallocate($canvas, 255, 255, 255);
    $black = imagecolorallocate($canvas, 0, 0, 0);

    // Titre : contour noir + texte blanc
    $sizeTitre = 48;
    $bbox = imagettfbbox($sizeTitre, 0, $fontBold, $titre);
    $textW = $bbox[2] - $bbox[0];
    $titreX = (int)(($width - $textW) / 2);
    $titreY = $boxY + 105;                     // position verticale parfaite

    // Contour épais
    for ($ox = -4; $ox <= 4; $ox++) {
        for ($oy = -4; $oy <= 4; $oy++) {
            if ($ox != 0 || $oy != 0) {
                imagettftext($canvas, $sizeTitre, 0, $titreX + $ox, $titreY + $oy, $black, $fontBold, $titre);
            }
        }
    }
    // Texte principal
    imagettftext($canvas, $sizeTitre, 0, $titreX, $titreY, $white, $fontBold, $titre);

    // Sous-titre
    $sizeSous = 28;
    $bbox2 = imagettfbbox($sizeSous, 0, $fontSemi, $sous);
    $textW2 = $bbox2[2] - $bbox2[0];
    $sousX = (int)(($width - $textW2) / 2);
    $sousY = $titreY + 74;                     // +74 px → espacement parfait

    imagettftext($canvas, $sizeSous, 0, $sousX, $sousY, $white, $fontSemi, $sous);

    // Logo eXtrag.one bas droite
    $logoPath = __DIR__ . '/../assets/img/logo.webp';
    if (is_file($logoPath)) {
        $logo = imagecreatefromwebp($logoPath);
        if ($logo) {
            $lw = imagesx($logo); $lh = imagesy($logo);
            $size = 100;
            $newW = $size; $newH = (int)($size * $lh / $lw);
            imagecopyresampled($canvas, $logo,
                $width - $newW - 50, $height - $newH - 40,
                0, 0, $newW, $newH, $lw, $lh);
            imagedestroy($logo);
        }
    }

    // Sauvegarde
    $dir = __DIR__ . '/../cache/month-tools/';
    if (!is_dir($dir)) mkdir($dir, 0755,true);
    $file = 'cover-outils-' . date('Y-m') . '.webp';
    $path = $dir . $file;
    if (is_file($path)) unlink($path);

    imagewebp($canvas, $path, 88);
    imagedestroy($canvas);

    return 'https://extrag.one/cache/month-tools/' . $file;
}

// Fonction arrondi (à garder une seule fois dans le fichier)
function imagefilledroundedrect($img, $x1, $y1, $x2, $y2, $r, $c) {
    imagefilledrectangle($img, $x1+$r, $y1,    $x2-$r, $y2, $c);
    imagefilledrectangle($img, $x1,    $y1+$r, $x2,    $y2-$r, $c);
    imagefilledellipse($img, $x1+$r, $y1+$r, $r*2, $r*2, $c);
    imagefilledellipse($img, $x2-$r, $y1+$r, $r*2, $r*2, $c);
    imagefilledellipse($img, $x1+$r, $y2-$r, $r*2, $r*2, $c);
    imagefilledellipse($img, $x2-$r, $y2-$r, $r*2, $r*2, $c);
}

$cover_url = generateMosaicAndReturnPath($tools);

// ================================================================
// 3. Réponse JSON pour n8n
// ================================================================

$mois_fr = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$month_fr = $mois_fr[date('n')-1] . ' ' . date('Y');

echo json_encode([
    'success'          => true,
    'month'           => date('Y-m'),
    'month_fr'        => $mois_fr[date('n')-1] . ' ' . date('Y'),
    'total_tools'      => count($tools),
    'cover_image_url' => $cover_url,
    'tools_by_category' => $by_category,
    'raw_tools'       => $tools, // si jamais tu veux tout
    'generated_at'    => date('c')
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);