<?php
// avatar.php

header('Content-Type: image/svg+xml');

// Récupération des paramètres
$name = isset($_GET['name']) ? trim($_GET['name']) : 'User';
$size = isset($_GET['size']) ? (int)$_GET['size'] : 32;

// Extraction des initiales (ex: "Jean Dupont" → "JD")
$words = preg_split('/\s+/', $name);
$initials = '';
foreach ($words as $w) {
    $initials .= mb_strtoupper(mb_substr($w, 0, 1));
}
$initials = mb_substr($initials, 0, 2); // Limiter à 2 lettres

// Génération d'une couleur stable à partir du nom
$hash = md5($name);
$hue = hexdec(substr($hash, 0, 6)) % 360;
$color = "hsl($hue, 60%, 55%)"; // Couleur vive mais équilibrée

// SVG minimaliste
$svg = '
<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'">
  <rect width="'.$size.'" height="'.$size.'" fill="'.$color.'" rx="{'.$size.'/2}" ry="{'.$size.'/2}" />
  <text x="50%" y="50%" dy="0.35em" text-anchor="middle"
        fill="white" font-family="Roboto, Arial, sans-serif"
        font-size="{'.$size.' * 0.5}" font-weight="500">'.$initials.'</text>
</svg>
';

echo $svg;
?>
