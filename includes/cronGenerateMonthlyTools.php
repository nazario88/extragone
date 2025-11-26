<?php
// generate-monthly-tools.php
// Objectif unique : sortir un JSON + une image mosaïque pour n8n

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

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

$cover_url = generateMosaicAndReturnPath($tools);

// =======================================================
// 3. Génération du titre + contenu HTML de l'article WordPress
// =======================================================
$mois_fr = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$month_fr = $mois_fr[date('n')-1] . ' ' . date('Y');

$total = count($tools);
$total_tools = $total;
$mois_actuel = $mois_fr[date('n')-1] . ' ' . date('Y');

$titre_article = "$total_tools nouveau" . ($total_tools > 1 ? 'x' : '') . " outil" . ($total_tools > 1 ? 's' : '') . " – $month_fr";

// Regroupement par catégorie (au cas où ce ne serait pas déjà fait)
$tools_by_cat = [];
foreach ($tools as $t) {
    $cat = $t['category'] ?: 'Autres';
    $tools_by_cat[$cat][] = $t;
}

// === Génération du HTML ===
// === 2. Génération du HTML propre ===
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Récap outils <?= htmlspecialchars($mois_actuel) ?> – Copier ce HTML</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
        pre { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 8px; height: 200px; overflow: auto; }
    </style>
</head>
<body>

<h2>Récapitulatif du mois de <?= htmlspecialchars($mois_actuel) ?> (<?= $total ?> nouvel<?= $total > 1 ? 's' : '' ?> outil<?= $total > 1 ? 's' : '' ?>)</h2>
<p>
    <img src="<?= htmlspecialchars($cover_url) ?>" alt="Mosaïque des nouveaux outils de <?= htmlspecialchars($mois_actuel) ?>" style="max-width:50%; height:auto; border:1px solid #ddd; border-radius:8px;">
</p>
<p><strong>Copiez tout le code HTML ci-dessous</strong> et collez-le dans un bloc "HTML personnalisé" dans WordPress :</p>

<pre id="code-to-copy" style="background:#fff; padding:20px; border:1px solid #ddd; border-radius:8px; white-space: pre; overflow-x:auto; font-family:Consolas,Monaco,monospace; font-size:14px;">
&lt;p style="font-size:18px;"&gt;&lt;strong&gt;<?= $total ?> nouvel<?= $total > 1 ? 's' : '' ?> outil<?= $total > 1 ? 's' : '' ?>&lt;/strong&gt; découvert<?= $total > 1 ? 's' : '' ?> en &lt;strong&gt;<?= $mois_actuel ?>&lt;/strong&gt; !&lt;/p&gt;

&lt;p&gt;Voici le récapitulatif mensuel des nouveaux outils ajoutés sur eXtrag.one ce mois-ci :&lt;/p&gt;

&lt;hr&gt;

<?php foreach ($tools_by_cat as $categorie => $outils): ?>
&lt;h2&gt;<?= htmlspecialchars($categorie) ?> &lt;small style="color:#666;"&gt;(<?= count($outils) ?>)&lt;/small&gt;&lt;/h2&gt;

&lt;table width="100%" cellpadding="12" cellspacing="0" style="border-collapse:collapse; border:1px solid #ddd; background:#fff; margin-bottom:30px;"&gt;
&lt;thead&gt;
&lt;tr style="background:#0066ff; color:white; text-align:left;"&gt;
    &lt;th width="80"&gt;Logo&lt;/th&gt;
    &lt;th&gt;Outil&lt;/th&gt;
    &lt;th&gt;Description&lt;/th&gt;
    &lt;th width="100"&gt;Prix&lt;/th&gt;
    &lt;th width="60"&gt;FR&lt;/th&gt;
    &lt;th width="120"&gt;&lt;/th&gt;
&lt;/tr&gt;
&lt;/thead&gt;
&lt;tbody&gt;
<?php foreach ($outils as $o):
    $link = 'https://extrag.one/outil/' . $o['slug'];
    $logo = !empty($o['logo']) ? 'https://extrag.one/' . $o['logo'] : '';
    $logoImg = $logo ? '&lt;img src="'.$logo.'" width="48" height="48" alt="'.htmlspecialchars($o['nom']).'" style="border-radius:6px;"&gt;' : '–';

    $prix = [];
    if ($o['is_free']) $prix[] = 'Gratuit';
    if ($o['is_paid']) $prix[] = 'Payant';
    $prixStr = $prix ? implode(' / ', $prix) : '–';

    $fr = $o['is_french'] ? 'Oui' : 'Non';
?>
&lt;tr style="border-bottom:1px solid #eee;"&gt;
    &lt;td&gt;<?= $logoImg ?>&lt;/td&gt;
    &lt;td&gt;&lt;strong&gt;&lt;a href="<?= $link ?>"&gt;<?= htmlspecialchars($o['nom']) ?>&lt;/a&gt;&lt;/strong&gt;&lt;/td&gt;
    &lt;td&gt;<?= htmlspecialchars($o['description_courte'] ?: '–') ?>&lt;/td&gt;
    &lt;td style="text-align:center;"&gt;<?= $prixStr ?>&lt;/td&gt;
    &lt;td style="text-align:center;"&gt;<?= $fr ?>&lt;/td&gt;
    &lt;td style="text-align:center;"&gt;&lt;a href="<?= $link ?>" style="background:#3a3a3a; color:#ffab3f; padding:8px 16px; border-radius:6px; text-decoration:none; font-weight:bold;"&gt;Voir l’outil&lt;/a&gt;&lt;/td&gt;
&lt;/tr&gt;
<?php endforeach; ?>
&lt;/tbody&gt;
&lt;/table&gt;
<?php endforeach; ?>

&lt;p style="color:#555; font-size:15px; margin-top:40px;"&gt;
    Prochain récap le 25 du mois prochain !&lt;br&gt;&lt;br&gt;
    &lt;a href="https://twitter.com/extragone"&gt;Twitter&lt;/a&gt; • 
    &lt;a href="https://www.linkedin.com/company/extragone"&gt;LinkedIn&lt;/a&gt; • 
    &lt;a href="https://extrag.one/newsletter"&gt;Newsletter&lt;/a&gt;
&lt;/p&gt;
</pre>

<p style="margin-top:20px;">
    <button onclick="selectCode()" style="padding:10px 20px; background:#0066ff; color:white; border:none; border-radius:6px; cursor:pointer;">Sélectionner tout le code</button>
    <small style="margin-left:20px; color:#555;">(puis Ctrl+C pour copier)</small>
</p>

<script>
function selectCode() {
    var range = document.createRange();
    range.selectNode(document.getElementById('code-to-copy'));
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);
}
</script>

</body>
</html>
<?php