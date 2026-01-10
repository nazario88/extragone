<?php
/**
 * Logique de recherche pour la page outils
 * Construit la clause WHERE selon les paramètres de recherche
 */

// Récupération des paramètres
$recherche = isset($_GET['q']) ? trim($_GET['q']) : '';
$categorie_slug = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';
$is_french = isset($_GET['is_french']) ? (int)$_GET['is_french'] : 0;
$search_in = isset($_GET['search_in']) ? $_GET['search_in'] : 'name'; // 'name', 'description', 'all'
$search_url = isset($_GET['url']) ? trim($_GET['url']) : '';

// Initialisation
$where_conditions = ['a.is_valid = 1'];
$params = [];

// =======================================
// RECHERCHE PAR NOM (prioritaire)
// =======================================
if (!empty($recherche)) {
    if ($search_in === 'name') {
        // Recherche STRICTE dans le nom uniquement
        $where_conditions[] = 'a.nom LIKE ?';
        $params[] = '%' . $recherche . '%';
        
    } elseif ($search_in === 'description') {
        // Recherche dans description ET description_longue
        $where_conditions[] = '(a.description LIKE ? OR a.description_longue LIKE ?)';
        $params[] = '%' . $recherche . '%';
        $params[] = '%' . $recherche . '%';
        
    } elseif ($search_in === 'all') {
        // Recherche dans TOUT
        $where_conditions[] = '(a.nom LIKE ? OR a.description LIKE ? OR a.description_longue LIKE ?)';
        $params[] = '%' . $recherche . '%';
        $params[] = '%' . $recherche . '%';
        $params[] = '%' . $recherche . '%';
    }
}

// =======================================
// RECHERCHE PAR URL
// =======================================
if (!empty($search_url)) {
    $where_conditions[] = 'a.url LIKE ?';
    $params[] = '%' . $search_url . '%';
}

// =======================================
// FILTRE CATÉGORIE
// =======================================
if (!empty($categorie_slug)) {
    $where_conditions[] = 'b.slug = ?';
    $params[] = $categorie_slug;
}

// =======================================
// FILTRE FRANÇAIS UNIQUEMENT
// =======================================
if ($is_french === 1) {
    $where_conditions[] = 'a.is_french = 1';
}

// Construction de la clause WHERE finale
$clause_where = implode(' AND ', $where_conditions);

// =======================================
// SUGGESTION D'ÉLARGISSEMENT
// =======================================
$suggest_expand = false;
$expand_url = '';

// Si recherche dans le nom uniquement et 0 résultat, suggérer d'élargir
if (!empty($recherche) && $search_in === 'name') {
    $suggest_expand = true;
    
    // URL pour élargir à la description
    $expand_params = $_GET;
    $expand_params['search_in'] = 'description';
    $expand_url = 'outils?' . http_build_query($expand_params);
}

// =======================================
// DÉTECTION DES FILTRES ACTIFS
// =======================================
$has_active_filters = !empty($recherche) || !empty($categorie_slug) || $is_french === 1 || !empty($search_url);