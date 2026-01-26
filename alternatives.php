<?php
/**
 * Page Hub : Alternatives françaises
 * Point d'entrée visuel pour naviguer entre toutes les pages alternatives
 */

include 'includes/config.php';

// ===============================================
// 1. RÉCUPÉRATION DES OUTILS AVEC PAGES ALTERNATIVES
// ===============================================

$sql = "
    SELECT 
        t.id,
        t.nom,
        t.slug,
        t.logo,
        t.screenshot,
        c.nom as categorie_nom,
        c.slug as categorie_slug,
        ac.word_count,
        ac.updated_at,
        (SELECT COUNT(*) FROM extra_alternatives a 
         INNER JOIN extra_tools alt ON alt.id = a.id_alternative 
         WHERE a.id_outil = t.id AND alt.is_french = 1) as nb_alternatives
    FROM extra_tools t
    INNER JOIN extra_alternatives_content ac ON ac.tool_id = t.id
    LEFT JOIN extra_tools_categories c ON c.id = t.categorie_id
    WHERE ac.is_active = 1
    ORDER BY t.nom ASC
";

$stmt = $pdo->query($sql);
$tools_with_alternatives = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats globales
$total_pages = count($tools_with_alternatives);
$total_alternatives = array_sum(array_column($tools_with_alternatives, 'nb_alternatives'));

// Grouper par catégorie pour les filtres
$categories = [];
foreach ($tools_with_alternatives as $tool) {
    $cat = $tool['categorie_nom'] ?: 'Autres';
    if (!isset($categories[$cat])) {
        $categories[$cat] = [];
    }
    $categories[$cat][] = $tool;
}

// ===============================================
// 2. SEO
// ===============================================

$title = "Alternatives françaises aux outils web — eXtragone";
$description = "Découvrez {$total_alternatives} alternatives françaises à vos outils favoris. Trouvez rapidement des solutions souveraines, conformes RGPD et hébergées en France.";
$url_canon = 'https://www.extrag.one/alternatives';
$image_seo = 'https://www.extrag.one/assets/img/alternative-fr-og.webp';
include 'includes/header.php';
?>

<!-- ===============================================
     HERO SECTION
     =============================================== -->
<div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 py-8 px-5">
    <div class="max-w-4xl mx-auto text-center">

        <!-- Icône principale -->
        <img src="assets/img/logo.webp" class="w-24 h-24 mx-auto mb-6" alt="Logo d'Extragone">
        
        <!-- Titre -->
        <h1 class="text-xl md:text-4xl font-bold mb-4 tracking-tight">
            Trouve une alternative française
        </h1>
        
        <!-- Description -->
        <p class="text-lg text-gray-700 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
            <strong><?= $total_pages ?> guides complets</strong> pour remplacer vos outils par des 
            <strong>solutions françaises</strong>, conformes RGPD et souveraines.
        </p>
        
        <!-- Barre de recherche autocomplete -->
        <div class="relative max-w-2xl mx-auto">
            <input 
                type="text" 
                id="searchAlternatives"
                placeholder="Chercher une alternative à Google, ChatGPT..."
                class="w-full px-6 py-4 pr-12 rounded-xl text-lg bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all shadow-lg"
                autocomplete="off">
            <i class="fa-solid fa-search absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-xl"></i>
            
            <!-- Résultats autocomplete -->
            <div id="searchResults" class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-xl shadow-2xl max-h-96 overflow-y-auto z-50">
                <!-- Rempli dynamiquement par JS -->
            </div>
        </div>
        
        <!-- Stats rapides -->
        <div class="flex flex-wrap justify-center gap-6 mt-8 text-sm">
            <div class="flex items-center gap-2 px-4 py-2 bg-white/80 dark:bg-slate-800/80 rounded-lg backdrop-blur-sm">
                <i class="fa-solid fa-flag text-blue-500"></i>
                <span><strong><?= $total_alternatives ?></strong> alternatives françaises</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-white/80 dark:bg-slate-800/80 rounded-lg backdrop-blur-sm">
                <i class="fa-solid fa-shield-halved text-green-500"></i>
                <span>100% <strong>conformes RGPD</strong></span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-white/80 dark:bg-slate-800/80 rounded-lg backdrop-blur-sm">
                <i class="fa-solid fa-star text-yellow-500"></i>
                <span>Guides <strong>détaillés</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- ===============================================
     FILTRES PAR CATÉGORIE
     =============================================== -->
<div class="max-w-7xl mx-auto px-5 py-8">
    <div class="flex flex-wrap items-center gap-3 mb-8">
        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Filtrer par catégorie :</span>
        
        <button 
            onclick="filterByCategory('all')"
            class="category-filter active px-4 py-2 rounded-lg bg-blue-500 text-white text-sm font-medium transition-all hover:shadow-lg"
            data-category="all">
            Toutes (<?= $total_pages ?>)
        </button>
        
        <?php foreach ($categories as $cat_name => $cat_tools): ?>
        <button 
            onclick="filterByCategory('<?= htmlspecialchars(strtolower(str_replace(' ', '-', $cat_name))) ?>')"
            class="category-filter px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-700 text-gray-700 dark:text-gray-300 text-sm font-medium transition-all hover:bg-slate-300 dark:hover:bg-slate-600"
            data-category="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $cat_name))) ?>">
            <?= htmlspecialchars($cat_name) ?> (<?= count($cat_tools) ?>)
        </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===============================================
     GRILLE DES ALTERNATIVES (CARTES CLIQUABLES)
     =============================================== -->
<div class="max-w-7xl mx-auto px-5 pb-12">
    
    <div id="toolsGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        
        <?php foreach ($tools_with_alternatives as $tool): ?>
        <?php 
        $cat_slug = strtolower(str_replace(' ', '-', $tool['categorie_nom'] ?: 'autres'));
        ?>
        
        <a href="alternative-francaise-<?= htmlspecialchars($tool['slug']) ?>" 
           class="tool-card group bg-white dark:bg-slate-800 rounded-2xl p-6 border-2 border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-2xl hover:-translate-y-1 flex flex-col items-center text-center"
           data-category="<?= htmlspecialchars($cat_slug) ?>"
           data-name="<?= htmlspecialchars(strtolower($tool['nom'])) ?>">
            
            <!-- Logo -->
            <div class="w-20 h-20 mb-4 flex items-center justify-center">
                <img src="<?= htmlspecialchars($tool['logo'] ?: $tool['screenshot']) ?>" 
                     alt="<?= htmlspecialchars($tool['nom']) ?>"
                     class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform duration-300">
            </div>
            
            <!-- Nom outil -->
            <h3 class="font-bold text-lg mb-2 line-clamp-2 group-hover:text-blue-500 transition-colors">
                <?= htmlspecialchars($tool['nom']) ?>
            </h3>
            
            <!-- Nombre d'alternatives -->
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs font-semibold mb-3">
                <?= $flag_FR ?>
                <span><?= $tool['nb_alternatives'] ?> alternative<?= $tool['nb_alternatives'] > 1 ? 's' : '' ?></span>
            </div>
            
            <!-- Catégorie -->
            <?php if ($tool['categorie_nom']): ?>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                <?= htmlspecialchars($tool['categorie_nom']) ?>
            </span>
            <?php endif; ?>
            
            <!-- CTA au survol -->
            <div class="mt-auto pt-4 opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="text-sm text-blue-500 font-medium flex items-center gap-2">
                    Voir les alternatives
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </span>
            </div>
        </a>
        
        <?php endforeach; ?>
    </div>
    
    <!-- Message si aucun résultat -->
    <div id="noResults" class="hidden text-center py-12">
        <i class="fa-solid fa-inbox text-4xl text-gray-400 mb-4"></i>
        <p class="text-gray-600 dark:text-gray-400">
            Aucune alternative trouvée dans cette catégorie
        </p>
    </div>
</div>

<!-- ===============================================
     SECTION CTA : PROPOSER UN OUTIL
     =============================================== -->
<div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 py-12 px-5">
    <div class="max-w-3xl mx-auto text-center">
        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fa-solid fa-lightbulb text-white text-2xl"></i>
        </div>
        
        <h2 class="text-3xl font-bold mb-4">
            Vous connaissez une alternative française ?
        </h2>
        
        <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">
            Aidez-nous à enrichir notre catalogue en proposant de nouvelles alternatives françaises
        </p>
        
        <a href="ajouter" 
           class="inline-block px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
            <i class="fa-solid fa-plus-circle mr-2"></i>
            Proposer une alternative
        </a>
    </div>
</div>

<!-- ===============================================
     SCRIPTS JAVASCRIPT
     =============================================== -->
<script>
// ===============================================
// 1. DONNÉES DES OUTILS (pour autocomplete)
// ===============================================
const toolsData = <?= json_encode(array_map(function($tool) {
    return [
        'nom' => $tool['nom'],
        'slug' => $tool['slug'],
        'logo' => $tool['logo'],
        'nb_alternatives' => $tool['nb_alternatives']
    ];
}, $tools_with_alternatives), JSON_UNESCAPED_UNICODE) ?>;

// ===============================================
// 2. AUTOCOMPLETE RECHERCHE
// ===============================================
const searchInput = document.getElementById('searchAlternatives');
const searchResults = document.getElementById('searchResults');

searchInput.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    
    if (query.length < 2) {
        searchResults.classList.add('hidden');
        return;
    }
    
    // Filtrer les outils
    const matches = toolsData.filter(tool => 
        tool.nom.toLowerCase().includes(query)
    ).slice(0, 8); // Max 8 résultats
    
    if (matches.length === 0) {
        searchResults.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                Aucune alternative trouvée
            </div>
        `;
        searchResults.classList.remove('hidden');
        return;
    }
    
    // Afficher les résultats
    searchResults.innerHTML = matches.map(tool => `
        <a href="alternative-francaise-${tool.slug}" 
           class="flex items-center gap-4 p-4 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors border-b border-slate-200 dark:border-slate-600 last:border-0">
            <img src="${tool.logo}" 
                 alt="${tool.nom}" 
                 class="w-10 h-10 object-contain flex-shrink-0">
            <div class="flex-1">
                <div class="font-semibold">${tool.nom}</div>
                <div class="text-xs text-gray-500">${tool.nb_alternatives} alternative${tool.nb_alternatives > 1 ? 's' : ''} française${tool.nb_alternatives > 1 ? 's' : ''}</div>
            </div>
            <i class="fa-solid fa-arrow-right text-gray-400"></i>
        </a>
    `).join('');
    
    searchResults.classList.remove('hidden');
});

// Fermer les résultats en cliquant ailleurs
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add('hidden');
    }
});

// ===============================================
// 3. FILTRAGE PAR CATÉGORIE
// ===============================================
function filterByCategory(category) {
    const cards = document.querySelectorAll('.tool-card');
    const noResults = document.getElementById('noResults');
    let visibleCount = 0;
    
    cards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });
    
    // Afficher/masquer message "aucun résultat"
    if (visibleCount === 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
    
    // Mettre à jour les boutons filtres
    document.querySelectorAll('.category-filter').forEach(btn => {
        if (btn.dataset.category === category) {
            btn.classList.remove('bg-slate-200', 'dark:bg-slate-700', 'text-gray-700', 'dark:text-gray-300');
            btn.classList.add('bg-blue-500', 'text-white', 'active');
        } else {
            btn.classList.remove('bg-blue-500', 'text-white', 'active');
            btn.classList.add('bg-slate-200', 'dark:bg-slate-700', 'text-gray-700', 'dark:text-gray-300');
        }
    });
}
</script>

<!-- Schema.org -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "<?= htmlspecialchars($title) ?>",
  "description": "<?= htmlspecialchars($description) ?>",
  "url": "<?= $url_canon ?>",
  "numberOfItems": <?= $total_pages ?>
}
</script>

<?php include 'includes/footer.php'; ?>