<?php
/**
 * Navigation Article Précédent / Article Suivant
 */

// Charger les fonctions si pas déjà fait
if (!function_exists('getPreviousArticle')) {
    require_once __DIR__ . '/functions.php';
}

// Récupérer les articles adjacents
$previous_article = getPreviousArticle($data_article['created_at']);
$next_article = getNextArticle($data_article['created_at']);

// Ne rien afficher si pas d'articles adjacents
if (!$previous_article && !$next_article) {
    return;
}
?>

<!-- Navigation Article Précédent / Suivant -->
<nav class="mt-12 border-t border-slate-200 dark:border-slate-700 pt-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Article précédent -->
        <div class="<?= !$previous_article ? 'invisible' : '' ?>">
            <?php if ($previous_article): ?>
            <a href="article/<?= htmlspecialchars($previous_article['slug']) ?>" 
               class="block group bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-lg transition-all">
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-arrow-left"></i>
                    Article précédent
                </div>
                <div class="flex gap-4">
                    <?php if ($previous_article['image']): ?>
                    <img src="<?= htmlspecialchars($previous_article['image']) ?>" 
                         alt="<?= htmlspecialchars($previous_article['title']) ?>"
                         class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold line-clamp-2 group-hover:text-blue-500 transition-colors">
                            <?= htmlspecialchars($previous_article['title']) ?>
                        </h3>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Article suivant -->
        <div class="<?= !$next_article ? 'invisible' : '' ?>">
            <?php if ($next_article): ?>
            <a href="article/<?= htmlspecialchars($next_article['slug']) ?>" 
               class="block group bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-lg transition-all">
                <div class="flex items-center justify-end gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Article suivant
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
                <div class="flex flex-row-reverse gap-4">
                    <?php if ($next_article['image']): ?>
                    <img src="<?= htmlspecialchars($next_article['image']) ?>" 
                         alt="<?= htmlspecialchars($next_article['title']) ?>"
                         class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                    <?php endif; ?>
                    <div class="flex-1 min-w-0 text-right">
                        <h3 class="font-bold line-clamp-2 group-hover:text-blue-500 transition-colors">
                            <?= htmlspecialchars($next_article['title']) ?>
                        </h3>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>
</nav>