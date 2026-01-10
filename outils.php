<?php
/**
 * Page de recherche et listing des outils
 * Optimisée pour le SEO et l'expérience utilisateur
 * Version refactorisée - Architecture modulaire
 */

include 'includes/config.php';

// ============================================
// 1. RÉCUPÉRATION DES CATÉGORIES
// ============================================
$stmt = $pdo->query('
    SELECT a.*, count(b.categorie_id) as nb_tools
    FROM extra_tools_categories a
    LEFT JOIN extra_tools b ON b.categorie_id=a.id AND b.is_valid=1 
    GROUP BY a.id ORDER BY a.nom ASC
');
$categories = $stmt->fetchAll();

// ============================================
// 2. LOGIQUE DE RECHERCHE
// ============================================
include 'includes/outils/search-logic.php';

// ============================================
// 3. PAGINATION
// ============================================
$itemsPerPage = 120;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// ============================================
// 4. CONSTRUCTION DE LA REQUÊTE SQL
// ============================================
// Requête de comptage
if (!empty($categorie_slug)) {
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM extra_tools a
        LEFT JOIN extra_tools_categories b ON a.categorie_id = b.id 
        WHERE $clause_where
    ");
} else {
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM extra_tools a
        WHERE $clause_where
    ");
}
$countStmt->execute($params);
$totalItems = $countStmt->fetch()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Requête principale
if (!empty($categorie_slug)) {
    $stmt = $pdo->prepare("
        SELECT a.*, b.nom AS categorie_nom 
        FROM extra_tools a
        LEFT JOIN extra_tools_categories b ON a.categorie_id = b.id 
        WHERE $clause_where
        ORDER BY a.date_creation DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$itemsPerPage, $offset]));
} else {
    $stmt = $pdo->prepare("
        SELECT a.* 
        FROM extra_tools a
        WHERE $clause_where
        ORDER BY a.date_creation DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$itemsPerPage, $offset]));
}

$outils = $stmt->fetchAll();
$nombre_outils = count($outils);

// ============================================
// 5. LOG SI AUCUN RÉSULTAT
// ============================================
if ($nombre_outils === 0 && !empty($recherche)) {
    $stmt_log = $pdo->prepare('INSERT INTO extra_logs (date, content) VALUES(NOW(), ?)');
    $log = 'Outil non trouvé via recherche : ' . $recherche . ' (search_in: ' . $search_in . ')';
    $stmt_log->execute([$log]);
}

// ============================================
// 6. FONCTION PAGINATION
// ============================================
function buildPaginationUrl($page, $categorie_slug = null) {
    $params = ['page' => $page];
    
    if (!empty($categorie_slug)) {
        $params['categorie'] = $categorie_slug;
    }
    
    // Conserver les autres paramètres GET existants
    $currentParams = $_GET;
    unset($currentParams['page']); // On retire page pour le remplacer
    $params = array_merge($currentParams, $params);
    
    return 'outils?' . http_build_query($params);
}

// ============================================
// 7. SEO : META TAGS DYNAMIQUES
// ============================================
$title = "Liste des outils référencés";
$description = "Accédez à la liste complète des outils référencés sur eXtragone pour trouver des alternatives fiables et simples aux services numériques populaires.";

// Meta dynamiques selon la recherche
if (!empty($recherche)) {
    $title = "Recherche : " . htmlspecialchars($recherche) . " — eXtragone";
    $description = "Résultats de recherche pour « " . htmlspecialchars($recherche) . " » : découvrez " . $totalItems . " outil" . ($totalItems > 1 ? 's' : '') . " et leurs alternatives françaises.";
}

if (!empty($categorie_slug)) {
    $stmt_cat = $pdo->prepare('SELECT nom, description FROM extra_tools_categories WHERE slug = ?');
    $stmt_cat->execute([$categorie_slug]);
    $cat_data = $stmt_cat->fetch();
    
    if ($cat_data) {
        $title = "Catégorie " . htmlspecialchars($cat_data['nom']) . " — eXtragone";
        $description = htmlspecialchars($cat_data['description']) . " | Trouvez les meilleurs outils et alternatives françaises.";
    }
}

$url_canon = 'https://www.extrag.one/outils';
if (!empty($_GET)) {
    $url_canon .= '?' . http_build_query($_GET);
}

// ============================================
// 8. HEADER
// ============================================
include 'includes/header.php';
?>

<!-- ============================================
     CONTENU PRINCIPAL
     ============================================ -->

<div class="w-full px-5 py-5">
    
    <?php if (!empty($recherche)): ?>
        <!-- Mode recherche -->
        <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">
            &rarr; Résultats de recherche
        </p>
        <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
            Recherche : "<?= htmlspecialchars($recherche) ?>"
            <span class="ml-3 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-base rounded-lg border border-blue-200 dark:border-blue-800">
                <?= $totalItems ?> résultat<?= $totalItems > 1 ? 's' : '' ?>
            </span>
        </h1>
        
    <?php else: ?>
        <!-- Mode liste -->
        <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">
            &rarr; Outils
        </p>
        <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
            Liste des outils
        </h1>
    <?php endif; ?>
</div>

<!-- Séparateur -->
<hr class="h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

<!-- Filtres actifs -->
<div class="px-5 py-5">
    <?php include 'includes/outils/filters-active.php'; ?>
</div>

<!-- Formulaire de recherche avancée -->
<div class="px-5">
    <?php include 'includes/outils/search-form.php'; ?>
</div>

<!-- Résultats -->
<?php include 'includes/outils/results-grid.php'; ?>

<!-- ============================================
     PAGINATION
     ============================================ -->
<?php if ($totalPages > 1 && $nombre_outils > 0): ?>
<nav aria-label="Navigation par pages" class="flex flex-col sm:flex-row justify-center items-center gap-3 mt-8 mb-6">
    <!-- Navigation mobile simplifiée -->
    <div class="flex items-center gap-2 sm:hidden">
        <?php if ($currentPage > 1): ?>
            <a href="<?= buildPaginationUrl($currentPage - 1, $categorie_slug) ?>" 
               aria-label="Page précédente"
               class="group px-4 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 duration-200">
                <i class="fa-solid fa-chevron-left transition-transform duration-200 group-hover:-translate-x-0.5"></i>
            </a>
        <?php endif; ?>
        
        <span class="px-4 py-3 bg-blue-600 border-blue-700 dark:bg-blue-500 dark:border-blue-600 text-white border rounded-xl font-semibold shadow-sm">
            <?= $currentPage ?> / <?= $totalPages ?>
        </span>
        
        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= buildPaginationUrl($currentPage + 1, $categorie_slug) ?>" 
               aria-label="Page suivante"
               class="group px-4 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 duration-200">
                <i class="fa-solid fa-chevron-right transition-transform duration-200 group-hover:translate-x-0.5"></i>
            </a>
        <?php endif; ?>
    </div>

    <!-- Navigation desktop complète -->
    <div class="hidden sm:flex items-center gap-2">
        <?php if ($currentPage > 1): ?>
            <a href="<?= buildPaginationUrl($currentPage - 1, $categorie_slug) ?>" 
               aria-label="Page précédente"
               class="group px-4 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 hover:shadow-md duration-200">
                <i class="fa-solid fa-chevron-left transition-transform duration-200 group-hover:-translate-x-0.5"></i>
            </a>
        <?php endif; ?>

        <?php
        // Calculer les pages à afficher
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        // Ajuster si on est près du début ou de la fin
        if ($currentPage <= 3) {
            $endPage = min($totalPages, 5);
        }
        if ($currentPage > $totalPages - 3) {
            $startPage = max(1, $totalPages - 4);
        }
        ?>

        <?php if ($startPage > 1): ?>
            <a href="<?= buildPaginationUrl(1, $categorie_slug) ?>" 
               aria-label="Aller à la page 1"
               class="group px-4 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 hover:shadow-md duration-200">
                <span class="transition-transform duration-200 group-hover:scale-110 inline-block">1</span>
            </a>
            <?php if ($startPage > 2): ?>
                <span class="px-2 py-3 text-gray-400 dark:text-gray-500 select-none">
                    <i class="fa-solid fa-ellipsis-h text-sm"></i>
                </span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <?php if ($i == $currentPage): ?>
                <span aria-current="page" 
                      class="px-4 py-3 bg-blue-600 border-blue-700 dark:bg-blue-500 dark:border-blue-600 text-white border rounded-xl font-semibold shadow-md ring-2 ring-blue-200 dark:ring-blue-800 ring-opacity-50">
                    <?= $i ?>
                </span>
            <?php else: ?>
                <a href="<?= buildPaginationUrl($i, $categorie_slug) ?>" 
                   aria-label="Aller à la page <?= $i ?>"
                   class="group px-4 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 hover:shadow-md duration-200">
                    <span class="transition-transform duration-200 group-hover:scale-110 inline-block"><?= $i ?></span>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
                <span class="px-2 py-3 text-gray-400 dark:text-gray-500 select-none">
                    <i class="fa-solid fa-ellipsis-h text-sm"></i>
                </span>
            <?php endif; ?>
            <a href="<?= buildPaginationUrl($totalPages, $categorie_slug) ?>" 
               aria-label="Aller à la page <?= $totalPages ?>"
               class="group px-4 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 hover:shadow-md duration-200">
                <span class="transition-transform duration-200 group-hover:scale-110 inline-block"><?= $totalPages ?></span>
            </a>
        <?php endif; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= buildPaginationUrl($currentPage + 1, $categorie_slug) ?>" 
               aria-label="Page suivante"
               class="group px-4 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 hover:shadow-md duration-200">
                <i class="fa-solid fa-chevron-right transition-transform duration-200 group-hover:translate-x-0.5"></i>
            </a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>

<!-- Schema.org pour SEO -->
<?php if (!empty($recherche) && $totalItems > 0): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SearchResultsPage",
  "name": "Résultats de recherche pour <?= htmlspecialchars($recherche, ENT_QUOTES) ?>",
  "description": "<?= htmlspecialchars($description, ENT_QUOTES) ?>",
  "url": "<?= htmlspecialchars($url_canon, ENT_QUOTES) ?>",
  "numberOfItems": <?= $totalItems ?>
}
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>