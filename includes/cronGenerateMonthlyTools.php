<?php
// generate-monthly-tools.php
// Objectif unique : sortir un JSON + une image mosaïque pour n8n


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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

$cover_url = generateMosaicAndReturnPath($tools);

// =======================================================
// 3. Génération du titre + contenu HTML de l'article WordPress
// =======================================================
$mois_fr = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$month_fr = $mois_fr[date('n')-1] . ' ' . date('Y');

$total_tools = count($tools);

$titre_article = "$total_tools nouveau" . ($total_tools > 1 ? 'x' : '') . " outil" . ($total_tools > 1 ? 's' : '') . " – $month_fr";

// Regroupement par catégorie (au cas où ce ne serait pas déjà fait)
$tools_by_cat = [];
foreach ($tools as $t) {
    $cat = $t['category'] ?: 'Autres';
    $tools_by_cat[$cat][] = $t;
}

// === Génération du HTML ===
$html = '<p style="font-size:18px;"><strong>' . $total_tools . ' nouveau' . ($total_tools > 1 ? 'x' : '') . ' outil' . ($total_tools > 1 ? 's' : '') . '</strong> découverts en <strong>' . $month_fr . '</strong> !</p>';
$html .= '<p>Ci-dessous le récap mensuel, mis à jour automatiquement chaque 25 du mois.</p><hr><br>';

foreach ($tools_by_cat as $cat => $outils) {
    $html .= '<h2 style="color:#0066ff; border-bottom:2px solid #0066ff; padding-bottom:8px;">' . htmlspecialchars($cat) . ' <span style="font-size:0.6em; color:#555;">(' . count($outils) . ')</span></h2>';

    $html .= '<div style="overflow-x:auto; margin:20px 0;">
<table width="100%" cellpadding="12" cellspacing="0" style="border-collapse:collapse; background:#f8f9fa; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
<thead>
<tr style="background:#0066ff; color:white; text-align:left;">
    <th style="border-radius:12px 0 0 0;">Logo</th>
    <th>Outil</th>
    <th>Description</th>
    <th>Prix</th>
    <th>FR</th>
    <th style="border-radius:0 12px 0 0;"></th>
</tr>
</thead>
<tbody>';

    foreach ($outils as $o) {
        $link   = 'https://extrag.one/outil/' . $o['slug'];
        $logo   = !empty($o['logo']) ? 'https://extrag.one/' . $o['logo'] : '';
        $logoImg = $logo ? '<img src="'.$logo.'" width="48" height="48" style="border-radius:8px; vertical-align:middle;" alt="'.htmlspecialchars($o['nom']).'">' : '–';

        $prix = [];
        if ($o['is_free']) $prix[] = 'Gratuit';
        if ($o['is_paid']) $prix[] = 'Payant';
        $prixStr = $prix ? implode(' / ', $prix) : '–';

        $fr = $o['is_french'] ? '<strong style="color:#0066ff;">FR</strong>' : 'Non';

        $desc = htmlspecialchars($o['description_courte'] ?? '');

        $html .= '<tr style="border-bottom:1px solid #eee;">
    <td>'.$logoImg.'</td>
    <td><strong><a href="'.$link.'" target="_blank" style="color:#0066ff; text-decoration:none;">'.htmlspecialchars($o['nom']).'</a></strong></td>
    <td>'.$desc.'</td>
    <td style="text-align:center;">'.$prixStr.'</td>
    <td style="text-align:center;">'.$fr.'</td>
    <td><a href="'.$link.'" target="_blank" style="background:#0066ff; color:white; padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:bold; display:inline-block;">+ d’info</a></td>
</tr>';
    }

    $html .= '</tbody></table></div><br><br>';
}

// Outro
$html .= '<p style="font-size:16px; color:#555;">
    Prochain récap le 25 du mois prochain !<br>
    <a href="https://twitter.com/extragone">Suis-nous sur Twitter</a> • 
    <a href="https://www.linkedin.com/company/extragone">LinkedIn</a> • 
    <a href="https://extrag.one/newsletter">Newsletter</a>
</p>';

/*
MARCHE PAS ERREUR 500 et pas d'affichages
déplacer ce fichier sur le serveur WordPress et utiliser wp-load.php ?
Ou aller le chercher ici directement ?
*/

// =======================================================
// 4. Publication directe sur WordPress via REST API (même serveur)
// =======================================================
function publishToMyWordPress(string $title, string $content, string $imageUrl, string $month_fr): ?string
{
    $wp_url      = 'https://innospira.fr';                    // Change si différent
    $username    = 'CronPHPMonthWP';                            // Change
    $appPassword = 'Z8KN edyI aeTo MYGH t2TO Chzg';           // Change (avec espaces)

    echo "Début publication WordPress...\n";

    // 1. Upload de l'image
    echo "Upload de l'image : $imageUrl\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $wp_url . '/wp-json/wp/v2/media',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => file_get_contents($imageUrl),
        CURLOPT_HTTPHEADER     => [
            'Content-Disposition: attachment; filename="cover-' . date('Y-m') . '.webp"',
            'Content-Type: image/webp',
            'Authorization: Basic ' . base64_encode($username . ':' . $appPassword)
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HEADER         => true,           // pour voir les headers
    headers
    ]);

    $rawResponse = curl_exec($ch);
    $headerSize  = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header      = substr($rawResponse, 0, $headerSize);
    $body        = substr($rawResponse, $headerSize);

    if ($httpCode !== 201) {
        echo "Échec upload image – Code HTTP : $httpCode\n";
        echo "Réponse : " . substr($body, 0, 500) . "\n";
        $featuredId = 0;
    } else {
        $media = json_decode($body, true);
        $featuredId = $media['id'] ?? 0;
        echo "Image uploadée avec succès – ID média : $featuredId\n";
    }

    // 2. Création du post
    $data = [
        'title'          => $title,
        'content'        => $content,
        'status'         => 'draft',
        'slug'           => 'outils-' . strtolower(str_replace(' ', '-', $month_fr)),
        'featured_media' => $featuredId,
        'categories'     => [12], // Change l'ID si besoin
    ];

    curl_setopt_array($ch, [
        CURLOPT_URL            => $wp_url . '/wp-json/wp/v2/posts',
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => [
            [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($username . ':' . $appPassword)
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Code HTTP création post : $httpCode\n";
    echo "Réponse brute : " . substr($response, 0, 800) . "\n";

    if ($httpCode === 201) {
        $post = json_decode($response, true);
        $link = $post['link'] ?? 'inconnu';
        echo "ARTICLE PUBLIÉ AVEC SUCCÈS : $link\n";
        return $link;
    } else {
        echo "ÉCHEC CRÉATION POST – Code : $httpCode\n";
        return null;
    }
}

function uploadMedia(string $wp_url, string $user, string $pass, string $imageUrl): ?array
{
    $ch = curl_init($wp_url . '/wp-json/wp/v2/media');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Disposition: attachment; filename="cover-' . date('Y-m') . '.webp"',
            'Authorization: Basic ' . base64_encode($user . ':' . $pass)
        ],
        CURLOPT_POSTFIELDS     => file_get_contents($imageUrl),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true) ?: null;
}

// Utilisation à la fin de ton script
$articleUrl = publishToMyWordPress($titre_article, $html, $cover_url, $month_fr);

if ($articleUrl) {
    echo "Article publié : $articleUrl\n";
    // Ici tu peux faire tes posts LinkedIn / X avec ce lien
} else {
    echo "Échec publication WordPress\n";
}

/*
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
*/