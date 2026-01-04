<?php
/**
 * Fonctions utilitaires pour les articles
 */

/**
 * Calcule le temps de lecture estimé d'un article
 * @param string $content Contenu HTML de l'article
 * @return int Temps de lecture en minutes
 */
function calculateReadingTime($content) {
    // Retirer les balises HTML
    $text = strip_tags($content);
    
    // Compter les mots
    $word_count = str_word_count($text);
    
    // Vitesse moyenne de lecture : 200 mots/minute
    $reading_time = ceil($word_count / 200);
    
    return max(1, $reading_time); // Minimum 1 minute
}

/**
 * Extrait les titres H2 et H3 du contenu HTML pour la table des matières
 * @param string $content Contenu HTML de l'article
 * @return array Tableau des titres avec leurs ancres
 */
function extractTableOfContents($content) {
    $toc = [];
    
    // Chercher tous les H2 et H3
    preg_match_all('/<h([23])>(.*?)<\/h\1>/i', $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $level = $match[1]; // 2 ou 3
        $title = strip_tags($match[2]);
        
        // Générer une ancre unique (slug)
        $anchor = generateAnchor($title);
        
        $toc[] = [
            'level' => (int)$level,
            'title' => $title,
            'anchor' => $anchor
        ];
    }
    
    return $toc;
}

/**
 * Génère une ancre HTML à partir d'un titre
 * @param string $text Texte du titre
 * @return string Ancre slugifiée
 */
function generateAnchor($text) {
    // Convertir en minuscules
    $anchor = mb_strtolower($text, 'UTF-8');
    
    // Remplacer les caractères accentués
    $anchor = str_replace(
        ['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ù', 'û', 'ü', 'î', 'ï', 'ô', 'ö', 'ç', '\''],
        ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'u', 'u', 'u', 'i', 'i', 'o', 'o', 'c', '', ''],
        $anchor
    );
    
    // Remplacer les espaces et caractères spéciaux par des tirets
    $anchor = preg_replace('/[^a-z0-9]+/', '-', $anchor);
    
    // Supprimer les tirets en début/fin
    $anchor = trim($anchor, '-');
    
    return $anchor;
}

/**
 * Injecte les ancres dans le contenu HTML
 * @param string $content Contenu HTML original
 * @param array $toc Table des matières (résultat de extractTableOfContents)
 * @return string Contenu HTML avec les ancres injectées
 */
function injectAnchorsInContent($content, $toc) {
    foreach ($toc as $item) {
        $level = $item['level'];
        $title = $item['title'];
        $anchor = $item['anchor'];
        
        // Chercher le titre exact et ajouter l'ID
        $pattern = '/<h' . $level . '>' . preg_quote($title, '/') . '<\/h' . $level . '>/i';
        $replacement = '<h' . $level . ' id="' . $anchor . '">' . $title . '</h' . $level . '>';
        
        $content = preg_replace($pattern, $replacement, $content, 1);
    }
    
    return $content;
}

/**
 * Récupère les articles les plus lus (par hits)
 * @param int $limit Nombre d'articles à récupérer
 * @param int $exclude_id ID de l'article à exclure (l'article actuel)
 * @return array Articles les plus lus
 */
function getMostReadArticles($limit = 2, $exclude_id = null) {
    global $pdo;
    
    $sql = 'SELECT id, title, slug, image, description, hits 
            FROM extra_articles 
            WHERE image IS NOT NULL';
    
    if ($exclude_id) {
        $sql .= ' AND id != :exclude_id';
    }
    
    $sql .= ' ORDER BY hits DESC LIMIT :limit';
    
    $stmt = $pdo->prepare($sql);
    
    if ($exclude_id) {
        $stmt->bindValue(':exclude_id', $exclude_id, PDO::PARAM_INT);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un article aléatoire
 * @param int $exclude_id ID de l'article à exclure
 * @return array|false Article aléatoire ou false
 */
function getRandomArticle($exclude_id = null) {
    global $pdo;
    
    $sql = 'SELECT id, title, slug, image, description 
            FROM extra_articles 
            WHERE image IS NOT NULL';
    
    if ($exclude_id) {
        $sql .= ' AND id != :exclude_id';
    }
    
    $sql .= ' ORDER BY RAND() LIMIT 1';
    
    $stmt = $pdo->prepare($sql);
    
    if ($exclude_id) {
        $stmt->bindValue(':exclude_id', $exclude_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère les articles suggérés (mix intelligent)
 * @param int $exclude_id ID de l'article actuel à exclure
 * @return array 3 articles suggérés
 */
function getSuggestedArticles($exclude_id) {
    $suggested = [];
    
    // 1. Les 2 plus lus
    $most_read = getMostReadArticles(2, $exclude_id);
    $suggested = array_merge($suggested, $most_read);
    
    // 2. 1 aléatoire (si on n'a pas déjà 3)
    if (count($suggested) < 3) {
        // Exclure aussi les articles déjà dans $suggested
        $exclude_ids = array_merge([$exclude_id], array_column($suggested, 'id'));
        
        global $pdo;
        $placeholders = implode(',', array_fill(0, count($exclude_ids), '?'));
        $sql = "SELECT id, title, slug, image, description 
                FROM extra_articles 
                WHERE image IS NOT NULL 
                AND id NOT IN ($placeholders)
                ORDER BY RAND() 
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($exclude_ids);
        $random = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($random) {
            $suggested[] = $random;
        }
    }
    
    return $suggested;
}

/**
 * Récupère l'article précédent (plus ancien)
 * @param string $current_date Date de création de l'article actuel
 * @return array|false Article précédent ou false
 */
function getPreviousArticle($current_date) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT id, title, slug, image 
        FROM extra_articles 
        WHERE created_at < ? AND image IS NOT NULL 
        ORDER BY created_at DESC 
        LIMIT 1
    ');
    $stmt->execute([$current_date]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère l'article suivant (plus récent)
 * @param string $current_date Date de création de l'article actuel
 * @return array|false Article suivant ou false
 */
function getNextArticle($current_date) {
    global $pdo;
    
    $stmt = $pdo->prepare('
        SELECT id, title, slug, image 
        FROM extra_articles 
        WHERE created_at > ? AND image IS NOT NULL 
        ORDER BY created_at ASC 
        LIMIT 1
    ');
    $stmt->execute([$current_date]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}