<?php

require('../includes/config.php');

header('Content-Type: text/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

function get_first_sentences(string $text, int $max_sentences = 2): string {
    // Nettoie les espaces inutiles
    $text = trim(preg_replace('/\s+/', ' ', $text));

    // Coupe le texte en phrases (basé sur ponctuation classique)
    $sentences = preg_split('/(?<=[.!?])\s+(?=[A-ZÀÂÉÈÊÎÔÙÛÇ])/u', $text, -1, PREG_SPLIT_NO_EMPTY);

    // Prend les premières phrases
    $result = array_slice($sentences, 0, $max_sentences);

    return implode(' ', $result);
}

?>
<rss version="2.0">
	<channel>
		<title>Flux RSS de eXtragone</title> 
		<link>https://www.extrag.one</link>
		<description>Liste des derniers outils référencés sur eXtragone.</description>
		<language>fr</language>
		<lastBuildDate><?php echo date(DATE_RSS); ?></lastBuildDate>

	<?php


	// Nombre d'outils à rédiger au hasard
	$nb_tools = mt_rand(5,9);
	$sql = '
		SELECT a.id, a.nom, a.description, a.description_longue, a.date_creation, a.slug, b.nom AS categorie
		FROM extra_tools a
		INNER JOIN extra_tools_categories b ON b.id = a.categorie_id
		WHERE a.article = 0
		  AND a.is_valid = 1
		  AND a.categorie_id = (
		      SELECT categorie_id
		      FROM extra_tools
		      WHERE article = 0 AND is_valid = 1
		      ORDER BY id DESC
		      LIMIT 1
		  )
		ORDER BY a.id DESC
		LIMIT ' . $nb_tools;
	
	$sql = $pdo->query($sql);
	
	$toolIds = []; // Array pour stocker les IDs des outils traités

	while($data = $sql->fetch()) {
		// On garde l'ID de l'outil pour l'UPDATE
		$toolIds[] = $data['id'];

		// Date au format RSS
		$rfc822Date = date(DATE_RSS, strtotime($data['date_creation']));

		// 2 premières phrases
		$shortDescription = get_first_sentences($data['description_longue']);
		echo '
		<item>
			<title>'.$data['nom'].'</title>
			<link>'.$base.'/outil/'.$data['slug'].'</link>
			<summary>'.$data['description'].'</summary>
			<description>'.$shortDescription.'></description>
			<guid>'.$base.'/outil/'.$data['slug'].'</guid>
			<pubDate>'.$rfc822Date.'</pubDate>
			<category>'.$data['categorie'].'</category>
		</item>
		';
	}

	// UPDATE des outils traités pour passer article=1
	if (!empty($toolIds)) {
	    $placeholders = str_repeat('?,', count($toolIds) - 1) . '?';
	    $updateSql = $pdo->prepare("UPDATE extra_tools SET article=1 WHERE id IN ($placeholders)");
	    $updateSql->execute($toolIds);
	}


	?>
	</channel>
</rss>

