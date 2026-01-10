<?php
/**
 * Affichage des filtres actifs avec possibilité de les retirer
 */

if ($has_active_filters): ?>
<div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
    <div class="flex flex-wrap items-center gap-3">
        <span class="text-sm font-semibold text-blue-900 dark:text-blue-100">
            <i class="fa-solid fa-filter mr-2"></i>Filtres actifs :
        </span>
        
        <!-- Recherche dans le nom -->
        <?php if (!empty($recherche)): ?>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-800 rounded-lg border border-blue-300 dark:border-blue-700 text-sm">
            <i class="fa-solid fa-search text-blue-600 dark:text-blue-400 text-xs"></i>
            <span class="font-medium">
                <?php 
                if ($search_in === 'name') echo 'Nom :';
                elseif ($search_in === 'description') echo 'Description :';
                else echo 'Partout :';
                ?>
            </span>
            <span class="text-gray-700 dark:text-gray-300">"<?= htmlspecialchars($recherche) ?>"</span>
            <a href="outils?<?= http_build_query(array_diff_key($_GET, ['q' => '', 'search_in' => ''])) ?>" 
               class="text-red-500 hover:text-red-700 transition-colors" 
               title="Retirer ce filtre">
                <i class="fa-solid fa-times text-xs"></i>
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Recherche dans l'URL -->
        <?php if (!empty($search_url)): ?>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-800 rounded-lg border border-blue-300 dark:border-blue-700 text-sm">
            <i class="fa-solid fa-link text-blue-600 dark:text-blue-400 text-xs"></i>
            <span class="font-medium">URL :</span>
            <span class="text-gray-700 dark:text-gray-300">"<?= htmlspecialchars($search_url) ?>"</span>
            <a href="outils?<?= http_build_query(array_diff_key($_GET, ['url' => ''])) ?>" 
               class="text-red-500 hover:text-red-700 transition-colors" 
               title="Retirer ce filtre">
                <i class="fa-solid fa-times text-xs"></i>
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Catégorie -->
        <?php if (!empty($categorie_slug)): 
            $stmt_cat = $pdo->prepare('SELECT nom FROM extra_tools_categories WHERE slug = ?');
            $stmt_cat->execute([$categorie_slug]);
            $cat_name = $stmt_cat->fetchColumn();
        ?>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-800 rounded-lg border border-blue-300 dark:border-blue-700 text-sm">
            <i class="fa-solid fa-folder text-blue-600 dark:text-blue-400 text-xs"></i>
            <span class="font-medium">Catégorie :</span>
            <span class="text-gray-700 dark:text-gray-300"><?= htmlspecialchars($cat_name ?: $categorie_slug) ?></span>
            <a href="outils?<?= http_build_query(array_diff_key($_GET, ['categorie' => ''])) ?>" 
               class="text-red-500 hover:text-red-700 transition-colors" 
               title="Retirer ce filtre">
                <i class="fa-solid fa-times text-xs"></i>
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Français uniquement -->
        <?php if ($is_french === 1): ?>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-800 rounded-lg border border-blue-300 dark:border-blue-700 text-sm">
            <i class="fa-solid fa-flag text-blue-600 dark:text-blue-400 text-xs"></i>
            <span class="font-medium">Français uniquement</span>
            <a href="outils?<?= http_build_query(array_diff_key($_GET, ['is_french' => ''])) ?>" 
               class="text-red-500 hover:text-red-700 transition-colors" 
               title="Retirer ce filtre">
                <i class="fa-solid fa-times text-xs"></i>
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Bouton réinitialiser tout -->
        <a href="outils" 
           class="ml-auto px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-rotate-left mr-2"></i>Réinitialiser tous les filtres
        </a>
    </div>
</div>
<?php endif; ?>