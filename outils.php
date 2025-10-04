<?php
include 'includes/config.php';

// Récupérer toutes les catégories pour le filtre
$stmt = $pdo->query('
    SELECT a.*, count(b.categorie_id) as nb_tools
    FROM extra_tools_categories a
    LEFT JOIN extra_tools b ON b.categorie_id=a.id
    GROUP BY a.id ORDER BY a.nom ASC');
$categories = $stmt->fetchAll();

// Vérifier si une catégorie a été sélectionnée
$categorie_slug = isset($_GET['categorie']) ? $_GET['categorie'] : null;

// Vérifier si une recherche a été effectuée
$recherche = isset($_GET['q']) ? $_GET['q'] : null;
$recherche = trim($recherche);
$clause_where = ($recherche) ? '(a.nom LIKE "%'.$recherche.'%" OR a.description LIKE "%'.$recherche.'%" OR a.description_longue LIKE "%'.$recherche.'%")' : '1=1';
//$clause_where = ($recherche) ? '(MATCH(a.nom, a.description, a.description_longue) AGAINST ("'.$recherche.'" IN NATURAL LANGUAGE MODE))' : '1=1';

// Alimentation avec le filtre sur les outils français
$is_french = isset($_POST['is_frenchPost']) ? $_POST['is_frenchPost'] : null;
$is_french_checked = '';
if($is_french) { // voir plus tard pour activer le filtre par défaut + SESSION ?
    $clause_where .= ' AND a.is_french=1';
    $is_french_checked = 'checked';
}

/* Configuration pagination
———————————————————————————————————————————————————————————————*/
$itemsPerPage = 120;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Récupérer le nombre total d'éléments pour calculer le nombre de pages
if (!empty($categorie_slug)) {
    $countStmt = $pdo->prepare('
        SELECT COUNT(*) as total
        FROM extra_tools a
        LEFT JOIN extra_tools_categories b ON a.categorie_id = b.id 
        WHERE a.is_valid=1 AND b.slug = ? AND '.$clause_where
    );
    $countStmt->execute([$categorie_slug]);
} else {
    $countStmt = $pdo->query('
        SELECT COUNT(*) as total
        FROM extra_tools a
        WHERE a.is_valid=1 AND '.$clause_where
    );
}

$totalItems = $countStmt->fetch()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Construire la requête en fonction de la catégorie sélectionnée
if (!empty($categorie_slug)) {
    // Si une catégorie est sélectionnée, récupérer les outils associés à cette catégorie
    $stmt = $pdo->prepare('
        SELECT a.*, b.nom AS categorie_nom 
        FROM extra_tools a
        LEFT JOIN extra_tools_categories b ON a.categorie_id = b.id 
        WHERE a.is_valid=1 AND b.slug = ? AND '.$clause_where.'
        ORDER BY a.date_creation DESC
        LIMIT ? OFFSET ?
    ');
    $stmt->execute([$categorie_slug, $itemsPerPage, $offset]);
} else {
    // Sinon, récupérer tous les outils associés à la recherche
    $stmt = $pdo->prepare('
        SELECT a.* 
        FROM extra_tools a
        WHERE a.is_valid=1 AND '.$clause_where.'
        ORDER BY a.date_creation DESC
        LIMIT ? OFFSET ?
    ');
    $stmt->execute([$itemsPerPage, $offset]);
}

$outils = $stmt->fetchAll();
$nombre_outils = count($outils);

function buildPaginationUrl($page, $categorie_slug = null) {
    $params = ['page' => $page];
    
    if (!empty($categorie_slug)) {
        $params['categorie'] = $categorie_slug;
    }
    
    // Conserver les autres paramètres GET existants (recherche, etc.)
    $currentParams = $_GET;
    unset($currentParams['page']); // On retire page pour le remplacer
    $params = array_merge($currentParams, $params);
    
    return 'outils?' . http_build_query($params);
}

// On enregistre la log si pas d'outil trouvé
if(!$outils) {
    $sql = $pdo->prepare('INSERT INTO extra_logs (date, content) VALUES(now(), ?)');
    $log = 'Outil non trouvé via recherche : '.$recherche;
    $sql->execute(array($log));
}

$title = "Liste des outils référencés";

$url_canon = 'https://www.extrag.one/outils';

include 'includes/header.php';

        if($recherche) {
            echo '
        <div class="w-full px-5 py-5">
            <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; Recherche</p>
            <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
                Résultats de la recherche <span class="border-gray-300 border text-gray-500 dark:border-gray-600 text-sm rounded px-2 mt-2">'.$nombre_outils.'</span>
            </h1>
            <a href="index.php" class="border-b-2 border-blue-500 hover:border-dotted">&rarr; Réinitialiser</a>
        </div>
            ';
        }
        else {
            ?>
        <div class="w-full px-5 py-5">
            <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; Outils</p>
            <div class="grid grid-cols-2">
                <div class="col-span-2 md:col-span-1">
                    <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
                        Liste des outils
                    </h1>
                </div>
                <div class="col-span-2 py-2 md:py-1 md:col-span-1 md:text-right">
                    <!-- Filtre par catégorie -->
                    <form method="post">
                        <div class="gap-2">
                            <!-- Case -->
                            <label for="is_frenchPost" class="inline-flex items-center gap-2 p-2">
                                <input id="is_frenchPost" name="is_frenchPost" type="checkbox" onchange="this.form.submit()" class="w-4 h-4" <?=$is_french_checked?>/>
                                <span>Français uniquement</span>
                            </label>

                            <!-- Select -->
                            <select class="p-2 min-w-[100%] lg:min-w-[300px] rounded bg-slate-200 text-slate-900 dark:bg-gray-800 dark:text-white" name="categorie" id="categorie" onchange="location.href='outils/categorie/'+this.value;">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?php echo $categorie['slug']; ?>" <?php echo ($categorie_slug == $categorie['slug']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categorie['nom']).' ('.$categorie['nb_tools'].')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
            <?php
        }
        ?>

        <!-- Separateur -->
        <hr class="h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

        <!-- Affichages des cartes -->
        <?php if ($outils): ?>
            <div class="px-5 py-5 grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-6">
            <?php foreach ($outils as $outil): ?>
                <?php
                if(empty($outil['logo'])) $outil['logo'] = 'assets/link.jpg';
                $drapeau = ($outil['is_french']) ? $flag_FR : '';
                ?>
                <div class="bg-slate-100 hover:bg-white rounded-xl shadow p-4 flex flex-col items-center text-center border border-slate-200 dark:bg-slate-800 dark:border-slate-700 hover:dark:bg-slate-700 transition-colors duration-300">
                    <a href="outil/<?php echo $outil['slug']; ?>" title="En savoir +">
                        <h2 class="text-xl font-bold mb-2 flex gap-2"><?php echo htmlspecialchars($outil['nom']).$drapeau ?></h2>
                    </a>
                        <div class="h-[100px] flex items-center justify-center">
                            <a href="outil/<?php echo $outil['slug']; ?>" title="En savoir +">
                                <img class="mx-auto w-full h-auto mb-2 rounded transition-transform duration-300 ease-in-out max-h-[100px]" src="<?php echo htmlspecialchars($outil['logo']); ?>" alt="Logo de <?php echo htmlspecialchars($outil['nom']); ?>">
                            </a>
                        </div>
                        <p class="text-sm"><?php echo htmlspecialchars($outil['description']); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
        <p class="text-center m-5">
            Aucun outil trouvé ☹️.
        </p>
        <?php endif; ?>
 
        <!-- Pagination -->
<?php if ($totalPages > 1): ?>
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
    <!-- Fin de pagination -->

    <!-- Footer -->
<?php
include 'includes/footer.php';
?>