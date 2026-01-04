<?php
/**
 * Sidebar pour les articles
 * Affiche : temps de lecture, table des matières, partage, articles liés, CTA
 */

// Charger les fonctions si pas déjà fait
if (!function_exists('calculateReadingTime')) {
    require_once __DIR__ . '/functions.php';
}

// Calculer le temps de lecture
$reading_time = calculateReadingTime($data_article['content_html']);

// Récupérer les articles suggérés
$suggested_articles = getSuggestedArticles($data_article['id']);
?>

<!-- Sidebar sticky -->
<aside class="lg:sticky lg:top-24 space-y-6">
    
    <!-- Temps de lecture -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold text-blue-500"><?= $reading_time ?> min</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Temps de lecture</div>
            </div>
            <i class="fa-solid fa-clock text-3xl text-blue-500 opacity-20"></i>
        </div>
    </div>

    <!-- Table des matières (si l'article a des titres) -->
    <?php if (!empty($toc)): ?>
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
        <h3 class="text-sm font-bold mb-4 uppercase tracking-wider flex items-center">
            <i class="fa-solid fa-list-ul mr-2"></i>
            Sommaire
        </h3>
        <nav class="space-y-2">
            <?php foreach ($toc as $item): ?>
                <a href="<?= $url_canon ?>#<?= htmlspecialchars($item['anchor']) ?>" 
                   class="block text-sm hover:text-blue-500 transition-colors <?= $item['level'] === 3 ? 'pl-4' : '' ?> group">
                    <span class="group-hover:underline">
                        <?= htmlspecialchars($item['title']) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    <?php endif; ?>

    <!-- Partage de l'article -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
        <h3 class="text-sm font-bold mb-4 uppercase tracking-wider flex items-center">
            <i class="fa-solid fa-share-nodes mr-2"></i>
            Partager l'article
        </h3>
        <div class="grid grid-cols-2 gap-2">
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($url_canon) ?>" 
               target="_blank"
               class="flex items-center justify-center gap-2 p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm">
                <i class="fa-brands fa-linkedin"></i>
                <span class="hidden xl:inline">LinkedIn</span>
            </a>
            
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($url_canon) ?>&text=<?= urlencode($data_article['title']) ?>" 
               target="_blank"
               class="flex items-center justify-center gap-2 p-3 bg-black hover:bg-gray-800 text-white rounded-lg transition text-sm">
                <i class="fa-brands fa-x-twitter"></i>
                <span class="hidden xl:inline">Twitter</span>
            </a>
            
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($url_canon) ?>" 
               target="_blank"
               class="flex items-center justify-center gap-2 p-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                <i class="fa-brands fa-facebook"></i>
                <span class="hidden xl:inline">Facebook</span>
            </a>
            
            <button onclick="copyArticleUrl()" 
                    id="copyUrlBtn"
                    class="flex items-center justify-center gap-2 p-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition text-sm">
                <i class="fa-solid fa-link"></i>
                <span class="hidden xl:inline">Copier</span>
            </button>
        </div>
    </div>

    <!-- Articles liés -->
    <?php if (!empty($suggested_articles)): ?>
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
        <h3 class="text-sm font-bold mb-4 uppercase tracking-wider flex items-center">
            <i class="fa-solid fa-newspaper mr-2"></i>
            Articles à lire
        </h3>
        <div class="space-y-4">
            <?php foreach ($suggested_articles as $article): ?>
            <a href="article/<?= htmlspecialchars($article['slug']) ?>" 
               class="block group hover:bg-slate-50 dark:hover:bg-slate-700 p-2 rounded-lg transition-colors">
                <div class="flex gap-3">
                    <img src="<?= htmlspecialchars($article['image']) ?>" 
                         alt="<?= htmlspecialchars($article['title']) ?>"
                         class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-sm line-clamp-2 group-hover:text-blue-500 transition-colors mb-1">
                            <?= htmlspecialchars($article['title']) ?>
                        </h4>
                        <p class="text-xs text-gray-500 line-clamp-2">
                            <?= htmlspecialchars($article['description']) ?>
                        </p>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Lien vers tous les articles -->
        <a href="articles" 
           class="block mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 text-center text-sm text-blue-500 hover:text-blue-600 font-medium transition-colors">
            Voir tous les articles
            <i class="fa-solid fa-arrow-right ml-1"></i>
        </a>
    </div>
    <?php endif; ?>

    <!-- CTA Nomi -->
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-6 border-2 border-green-200 dark:border-green-700">
        <div class="text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-wand-magic-sparkles text-white text-xl"></i>
            </div>
            <h3 class="font-bold text-lg mb-2">Besoin d'un nom ?</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                Nomi génère des noms percutants pour ton projet en 3 secondes chrono !
            </p>
            <a href="https://nomi.extrag.one" 
               target="_blank"
               class="inline-block px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl">
                <i class="fa-solid fa-rocket mr-2"></i>
                Essayer Nomi
            </a>
        </div>
    </div>
</aside>

<!-- Script pour copier l'URL -->
<script>
function copyArticleUrl() {
    const url = '<?= $url_canon ?>';
    const btn = document.getElementById('copyUrlBtn');
    
    navigator.clipboard.writeText(url).then(() => {
        // Feedback visuel
        btn.innerHTML = '<i class="fa-solid fa-check"></i> <span class="hidden xl:inline">Copié !</span>';
        btn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        btn.classList.add('bg-green-500');
        
        // Retour à l'état normal après 2 secondes
        setTimeout(() => {
            btn.innerHTML = '<i class="fa-solid fa-link"></i> <span class="hidden xl:inline">Copier</span>';
            btn.classList.remove('bg-green-500');
            btn.classList.add('bg-gray-600', 'hover:bg-gray-700');
        }, 2000);
    }).catch(err => {
        console.error('Erreur copie URL:', err);
        alert('Erreur lors de la copie');
    });
}
</script>