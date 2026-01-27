<?php

require('../includes/config.php');
if(!$base) $base = "https://www.extrag.one";

// Date de dernière modification du site
$lastmod_site = date('Y-m-d');

header('Content-Type: text/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

	<!-- Page d'accueil -->	
	<url>
		<loc><?= $base ?></loc>
		<lastmod><?= $lastmod_site ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
	</url>

	<!-- Page Outils -->
	<url>
		<loc><?= $base ?>/outils</loc>
		<lastmod><?= $lastmod_site ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
	</url>
	<!-- Page Alternatives -->
	<url>
		<loc><?= $base ?>/alternatives</loc>
		<lastmod><?= $lastmod_site ?></lastmod>
		<changefreq>weekly</changefreq>
		<priority>0.9</priority>
	</url>
	
	<!-- Page Catégories -->
	<url>
        <loc>https://www.extrag.one/categories</loc>
        <lastmod><?= $lastmod_site ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

	<!-- Pages ajouter un outil -->
	<url>
		<loc><?= $base ?>/ajouter</loc>
		<lastmod><?= $lastmod_site ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
	</url>

	<!-- Page Contact -->
    <url>
        <loc><?= $base ?>/contact</loc>
        <lastmod><?= $lastmod_site ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

	<!-- Page Articles -->
	<url>
		<loc><?= $base ?>/articles</loc>
		<changefreq>weekly</changefreq>
        <priority>0.8</priority>
	</url>

	<!-- Page À propos -->
	<url>
		<loc><?= $base ?>/a-propos</loc>
		<changefreq>monthly</changefreq>
		<priority>0.7</priority>
	</url>
</urlset>
