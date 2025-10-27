<?php
// generate-tool-image.php
// Génération d'une image de partage pour un outil (eXtragone)

include('includes/config.php');

// Config
define('CACHE_DIR', __DIR__ . '/cache/tool-images/');
define('IMG_WIDTH', 1200);
define('IMG_HEIGHT', 675);

$toolId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$forceRegenerate = isset($_GET['refresh']) && $_GET['refresh'] === '1';

if ($toolId <= 0) {
    http_response_code(400);
    die('Invalid tool ID');
}

if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

$cacheFile = CACHE_DIR . "tool_{$toolId}.jpg";
if (!$forceRegenerate && file_exists($cacheFile)) {
    header('Content-Type: image/jpeg');
    readfile($cacheFile);
    exit;
}

// Récupère les données
$stmt = $pdo->prepare('SELECT nom, description, screenshot, logo FROM extra_tools WHERE id = ?');
$stmt->execute([$toolId]);
$tool = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tool) {
    http_response_code(404);
    die('Tool not found');
}

// Create canvas
$canvas = imagecreatetruecolor(IMG_WIDTH, IMG_HEIGHT);
imagesavealpha($canvas, true);
imagealphablending($canvas, true);

// Couleurs
$white = imagecolorallocate($canvas, 255, 255, 255);
$black = imagecolorallocate($canvas, 0, 0, 0);
$globalOverlay = imagecolorallocatealpha($canvas, 0, 0, 0, 50); // overlay général

// === Background ===
if (!empty($tool['screenshot']) && file_exists($tool['screenshot'])) {
    $bg = loadImageFromFile($tool['screenshot']);
    if ($bg) {
        $bg = resizeIfTooBig($bg, 1500); // limite la taille source
        $resized = resizeAndCropImage($bg, IMG_WIDTH, IMG_HEIGHT);
        for ($i = 0; $i < 10; $i++) imagefilter($resized, IMG_FILTER_GAUSSIAN_BLUR);
        imagecopy($canvas, $resized, 0, 0, 0, 0, IMG_WIDTH, IMG_HEIGHT);
        imagedestroy($bg);
        imagedestroy($resized);
    } else {
        createGradientBackground($canvas, IMG_WIDTH, IMG_HEIGHT);
    }
} else {
    createGradientBackground($canvas, IMG_WIDTH, IMG_HEIGHT);
}

// Superposition sombre pour augmenter contraste
imagefilledrectangle($canvas, 0, 0, IMG_WIDTH, IMG_HEIGHT, $globalOverlay);

// === Paramètres de la boîte ===
$boxWidth = (int)(IMG_WIDTH * 0.82);
$boxHeight = 240;
$boxX = (IMG_WIDTH - $boxWidth) / 2;
// *** MODIF : décale la boîte vers le bas ***
$boxY = (IMG_HEIGHT - $boxHeight) / 2 + 60;

$boxRadius = 36;
$boxAlpha = 70;

$brightness = averageBrightness($canvas);
if ($brightness < 50) {
    $boxColorRGBA = [100, 100, 100, 70];
} else {
    $boxColorRGBA = [0, 0, 0, 70];
}

// Crée une couche pour la boîte
$box = imagecreatetruecolor($boxWidth, $boxHeight);
imagesavealpha($box, true);
imagealphablending($box, false);
$transparent = imagecolorallocatealpha($box, 0, 0, 0, 127);
imagefill($box, 0, 0, $transparent);

$boxColor = imagecolorallocatealpha($box, $boxColorRGBA[0], $boxColorRGBA[1], $boxColorRGBA[2], $boxColorRGBA[3]);
imagefilledroundedrect_local($box, 0, 0, $boxWidth, $boxHeight, $boxRadius, $boxColor);
imagecopy($canvas, $box, (int)$boxX, (int)$boxY, 0, 0, $boxWidth, $boxHeight);
imagedestroy($box);

// === Logo centré en haut (ajouté avant les textes) ===
$mainLogoPath = $tool['logo'];
if (file_exists($mainLogoPath)) {
    $mainLogoImg = loadImageFromFile($mainLogoPath);
    if ($mainLogoImg) {
        $lw = imagesx($mainLogoImg);
        $lh = imagesy($mainLogoImg);
        $targetW = 200;
        $targetH = (int)($targetW * $lh / $lw);
        $resMainLogo = imagecreatetruecolor($targetW, $targetH);
        imagesavealpha($resMainLogo, true);
        imagealphablending($resMainLogo, false);
        $trans = imagecolorallocatealpha($resMainLogo, 0, 0, 0, 127);
        imagefill($resMainLogo, 0, 0, $trans);
        imagecopyresampled($resMainLogo, $mainLogoImg, 0, 0, 0, 0, $targetW, $targetH, $lw, $lh);

        $posX = (IMG_WIDTH - $targetW) / 2;
        $posY = $boxY - $targetH - 65; // juste au-dessus de la boîte
        imagecopy($canvas, $resMainLogo, (int)$posX, (int)$posY, 0, 0, $targetW, $targetH);

        imagedestroy($mainLogoImg);
        imagedestroy($resMainLogo);
    }
}

// === Textes ===
$fontBold = 'assets/font/montserrat/Montserrat-Bold.ttf';
$fontRegular = 'assets/font/montserrat/Montserrat-SemiBold.ttf';

// Paramètres
$toolName = mb_strtoupper($tool['nom'], 'UTF-8');
$fontSizeTitle = 58;
$fontSizeDesc = 30;

// Tronque la description si trop longue
$desc = trim($tool['description'] ?? '');
$maxLen = 75;
if (mb_strlen($desc) > $maxLen) $desc = mb_substr($desc, 0, $maxLen - 3) . '...';

// Wrap texte
$descMaxWidth = $boxWidth - 120;
$descLines = wrapTextToLines($desc, $fontRegular, $fontSizeDesc, $descMaxWidth);

// Centrage vertical du texte dans la boîte
$bboxTitle = imagettfbbox($fontSizeTitle, 0, $fontBold, $toolName);
$titleWidth = $bboxTitle[2] - $bboxTitle[0];
$titleHeight = abs($bboxTitle[7] - $bboxTitle[1]);
$lineHeight = (int)($fontSizeDesc * 1.25);
$descHeightTotal = count($descLines) * $lineHeight;
$contentHeight = $titleHeight + 20 + $descHeightTotal;
$startY = $boxY + ($boxHeight - $contentHeight) / 2 + $titleHeight;

// Centrage horizontal
$titleX = (IMG_WIDTH - $titleWidth) / 2;
$titleY = (int)$startY;

// Contour noir + texte blanc
$contourColor = imagecolorallocate($canvas, 0, 0, 0);
for ($ox = -2; $ox <= 2; $ox++) {
    for ($oy = -2; $oy <= 2; $oy++) {
        imagettftext($canvas, $fontSizeTitle, 0, (int)($titleX + $ox), (int)($titleY + $oy), $contourColor, $fontBold, $toolName);
    }
}
imagettftext($canvas, $fontSizeTitle, 0, (int)$titleX, (int)$titleY, $white, $fontBold, $toolName);

// Description
$gray = imagecolorallocate($canvas, 230, 230, 230);
$descStartY = $titleY + 20;
foreach ($descLines as $i => $line) {
    $bbox = imagettfbbox($fontSizeDesc, 0, $fontRegular, $line);
    $w = $bbox[2] - $bbox[0];
    $x = (IMG_WIDTH - $w) / 2;
    $y = $descStartY + $i * $lineHeight + ($fontSizeDesc);
    imagettftext($canvas, $fontSizeDesc, 0, (int)$x, (int)$y, $gray, $fontRegular, $line);
}

// === Petit logo en bas à droite ===
$logoPath = 'assets/img/logo.webp';
if (file_exists($logoPath)) {
    $logoImg = imagecreatefromwebp($logoPath);
    if ($logoImg) {
        $lw = imagesx($logoImg);
        $lh = imagesy($logoImg);
        $targetW = 100;
        $targetH = (int)($targetW * $lh / $lw);
        $resLogo = imagecreatetruecolor($targetW, $targetH);
        imagesavealpha($resLogo, true);
        imagealphablending($resLogo, false);
        $trans = imagecolorallocatealpha($resLogo, 0, 0, 0, 127);
        imagefill($resLogo, 0, 0, $trans);
        imagecopyresampled($resLogo, $logoImg, 0, 0, 0, 0, $targetW, $targetH, $lw, $lh);

        $posX = IMG_WIDTH - $targetW - 36;
        $posY = IMG_HEIGHT - $targetH - 36;
        imagecopy($canvas, $resLogo, $posX, $posY, 0, 0, $targetW, $targetH);

        imagedestroy($logoImg);
        imagedestroy($resLogo);
    }
}

// Sauvegarde + sortie
imagejpeg($canvas, $cacheFile, 85); // 85% qualité
header('Content-Type: image/jpeg');
imagejpeg($canvas, null, 85);

imagedestroy($canvas);

// =================== Fonctions utilitaires ===================

function loadImageFromFile($path) {
    $info = @getimagesize($path);
    if (!$info) return false;
    switch ($info['mime']) {
        case 'image/jpeg': return imagecreatefromjpeg($path);
        case 'image/png': return imagecreatefrompng($path);
        case 'image/webp': return imagecreatefromwebp($path);
        default: return false;
    }
}

/**
 * Resize and crop to exact target (cover)
 */
function resizeAndCropImage($src, $targetW, $targetH) {
    $srcW = imagesx($src);
    $srcH = imagesy($src);
    $srcRatio = $srcW / $srcH;
    $targetRatio = $targetW / $targetH;

    if ($srcRatio > $targetRatio) {
        $newH = $targetH;
        $newW = (int)round($srcW * ($targetH / $srcH));
    } else {
        $newW = $targetW;
        $newH = (int)round($srcH * ($targetW / $srcW));
    }

    $tmp = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

    $final = imagecreatetruecolor($targetW, $targetH);
    $cropX = (int)(($newW - $targetW) / 2);
    $cropY = (int)(($newH - $targetH) / 2);
    imagecopy($final, $tmp, 0, 0, $cropX, $cropY, $targetW, $targetH);
    imagedestroy($tmp);
    return $final;
}

function createGradientBackground($canvas, $w, $h) {
    for ($y = 0; $y < $h; $y++) {
        $ratio = $y / $h;
        $r = (int)(30 + ($ratio * 20));
        $g = (int)(40 + ($ratio * 30));
        $b = (int)(80 + ($ratio * 40));
        $color = imagecolorallocate($canvas, $r, $g, $b);
        imageline($canvas, 0, $y, $w, $y, $color);
    }
}

/**
 * Dessine un rectangle arrondi rempli sur une image (local)
 * Utilisé pour dessiner sur une couche transparente avant copie
 */
function imagefilledroundedrect_local($im, $x1, $y1, $x2, $y2, $radius, $color) {
    // rectangles centraux
    imagefilledrectangle($im, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
    imagefilledrectangle($im, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
    // coins
    imagefilledellipse($im, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    imagefilledellipse($im, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
}

/**
 * Wrap text into lines respecting pixel width.
 * Retourne un tableau de lignes.
 */
function wrapTextToLines($text, $font, $size, $maxWidth) {
    $words = preg_split('/\s+/', $text);
    $lines = [];
    $current = '';
    foreach ($words as $w) {
        $test = $current === '' ? $w : $current . ' ' . $w;
        $box = imagettfbbox($size, 0, $font, $test);
        $width = $box[2] - $box[0];
        if ($width > $maxWidth && $current !== '') {
            $lines[] = $current;
            $current = $w;
        } else {
            $current = $test;
        }
    }
    if ($current !== '') $lines[] = $current;
    return $lines;
}
/**
 * Définir la luminosité pour choisir la couleur de l'encadré
 */
function averageBrightness($img) {
    $sample = 30; // échantillonnage
    $w = imagesx($img);
    $h = imagesy($img);
    $stepX = max(1, (int)($w / $sample));
    $stepY = max(1, (int)($h / $sample));
    $total = 0; $count = 0;
    for ($x = 0; $x < $w; $x += $stepX) {
        for ($y = 0; $y < $h; $y += $stepY) {
            $rgb = imagecolorat($img, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $total += ($r + $g + $b) / 3;
            $count++;
        }
    }
    return $count ? $total / $count : 128;
}
/*
 * Si image trop lourdes
*/
function resizeIfTooBig($img, $maxWidth = 1500) {
    $w = imagesx($img);
    $h = imagesy($img);
    if ($w <= $maxWidth) return $img;
    
    $ratio = $maxWidth / $w;
    $newW = $maxWidth;
    $newH = (int)($h * $ratio);
    
    $resized = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagedestroy($img);
    return $resized;
}

?>
