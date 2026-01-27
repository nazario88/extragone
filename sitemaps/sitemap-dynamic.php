<?php

require('../includes/config.php');
if(!$base) $base = "https://www.extrag.one";

header('Content-Type: text/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<?php

	/* OUTILS
	———————————————————————————————————————————————————————————————*/
	$tools = $pdo->query('SELECT slug, date_creation, is_french FROM extra_tools WHERE is_valid=1 ORDER BY id ASC');
	while($data = $tools->fetch()) {
		$date = substr($data['date_creation'], 0, 10);
		$priority = $data['is_french'] ? '0.85' : '0.80';
		echo '
			<url>
				<loc>'.$base.'/outil/'.$data['slug'].'</loc>
				<lastmod>'.$date.'</lastmod>
				<changefreq>monthly</changefreq>
				<priority>'.$priority.'</priority>
			</url>
		';
	}

	/* ARTICLES
	———————————————————————————————————————————————————————————————*/
	$tools = $pdo->query('SELECT slug, created_at FROM extra_articles ORDER BY id ASC');
	while($data = $tools->fetch()) {
		$date = substr($data['created_at'], 0, 10);
		echo '
			<url>
				<loc>'.$base.'/article/'.$data['slug'].'</loc>
				<lastmod>'.$date.'</lastmod>
				<changefreq>monthly</changefreq>
        		<priority>0.7</priority>
			</url>
		';
	}

	/* ALTERNATIVES
	———————————————————————————————————————————————————————————————*/
	$sql_alternatives = "
		SELECT 
			t1.slug,
			t1.date_creation,
			COUNT(DISTINCT a.id_alternative) as nb_alternatives_fr
		FROM extra_tools t1
		INNER JOIN extra_alternatives a ON a.id_outil = t1.id
		INNER JOIN extra_tools t2 ON a.id_alternative = t2.id
		WHERE t1.is_valid = 1
		AND t1.is_french = 0
		AND t2.is_french = 1
		GROUP BY t1.id
		HAVING nb_alternatives_fr >= 3
		ORDER BY t1.hits DESC
	";

	$stmt = $pdo->query($sql_alternatives);
	$alternatives_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach ($alternatives_pages as $alt):
		$lastmod = date('Y-m-d', strtotime($alt['date_creation']));
		?>
		<url>
			<loc>https://www.extrag.one/alternative-francaise-<?= htmlspecialchars($alt['slug'], ENT_XML1) ?></loc>
			<lastmod><?= $lastmod ?></lastmod>
			<changefreq>weekly</changefreq>
			<priority>0.95</priority>
		</url>
	<?php endforeach; ?>
	?>
</urlset>
