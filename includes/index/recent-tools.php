<?php
/**
 * Section "Derniers outils ajoutés" avec scroll automatique
 * Affiche les 30 derniers outils avec animation de défilement continu
 */

// Configuration du cache
$cacheTime = 3600*24; // 24 heures (les derniers outils ne changent pas toutes les 5 minutes)
$cacheDir = __DIR__ . '/../../cache/';
$cacheFile = $cacheDir . 'recent_tools.json';

// Vérifier si le cache existe et est encore valide
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    // Charger depuis le cache
    $recent_tools = json_decode(file_get_contents($cacheFile), true);
} else {
    // Récupération des 30 derniers outils depuis la BDD
    $stmt = $pdo->query('
        SELECT id, nom, slug, logo, description, is_french, date_creation
        FROM extra_tools 
        WHERE is_valid = 1 
        ORDER BY date_creation DESC 
        LIMIT 30
    ');
    $recent_tools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sauvegarder dans le cache
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    file_put_contents($cacheFile, json_encode($recent_tools, JSON_UNESCAPED_UNICODE));
}

// Fonction pour calculer "il y a X jours"
function timeAgoShort($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return "il y a {$minutes} min";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "il y a {$hours}h";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "il y a {$days}j";
    } else {
        return date('d/m/Y', $timestamp);
    }
}
?>

<!-- Section Derniers Outils -->
<div class="w-full mb-8">
    <h2 class="font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400 text-left mb-4">
        🆕 Derniers outils ajoutés
    </h2>
    
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
        <!-- Conteneur avec hauteur fixe -->
        <div class="relative h-[300px] overflow-hidden">
            <!-- Liste scrollante -->
            <div id="recentToolsList" class="space-y-0">
                <?php 
                // On duplique la liste pour un scroll infini fluide
                $tools_doubled = array_merge($recent_tools, $recent_tools);
                foreach ($tools_doubled as $tool): 
                    $description_short = mb_strimwidth($tool['description'], 0, 70, '...');
                    $logo = !empty($tool['logo']) ? $tool['logo'] : 'assets/link.jpg';
                    $flag = $tool['is_french'] ? $flag_FR : '';
                    $time_ago = timeAgoShort($tool['date_creation']);
                ?>
                <a href="outil/<?= htmlspecialchars($tool['slug']) ?>" 
                   class="flex items-center gap-4 px-6 py-2 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200 border-b border-slate-100 dark:border-slate-700 last:border-0 tool-item">
                    
                    <!-- Logo -->
                    <img src="<?= htmlspecialchars($logo) ?>" 
                         alt="<?= htmlspecialchars($tool['nom']) ?>"
                         class="w-10 h-10 object-contain rounded-lg flex-shrink-0">
                    
                    <!-- Nom + Description sur une ligne -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <?php if ($flag): ?>
                                <span><?= $flag ?></span>
                            <?php endif; ?>
                            <span class="font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($tool['nom']) ?>
                            </span>
                            <span class="text-slate-400 dark:text-slate-500">•</span>
                            <span class="text-sm text-slate-600 dark:text-slate-400 truncate">
                                <?= htmlspecialchars($description_short) ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Date -->
                    <div class="text-xs text-slate-500 dark:text-slate-400 flex-shrink-0 ml-4">
                        <?= $time_ago ?>
                    </div>
                    
                    <!-- Icône flèche -->
                    <i class="fa-solid fa-chevron-right text-slate-400 text-xs flex-shrink-0"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Footer avec bouton -->
        <div class="border-t border-slate-200 dark:border-slate-700 p-4 text-center bg-slate-50 dark:bg-slate-900">
            <a href="outils" 
               class="inline-flex items-center gap-2 text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors">
                Voir tous les outils
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</div>

<!-- Script d'animation scroll infini -->
<script>
(function() {
    const container = document.getElementById('recentToolsList');
    if (!container) return;
    
    let scrollSpeed = 0.5; // pixels par frame (ajustable)
    let scrollPosition = 0;
    let isPaused = false;
    let animationFrame;
    
    // Hauteur d'un item (calculée dynamiquement)
    const firstItem = container.querySelector('.tool-item');
    if (!firstItem) return;
    
    const itemHeight = firstItem.offsetHeight;
    const totalItems = <?= count($recent_tools) ?>; // Nombre réel d'outils (pas doublé)
    const totalHeight = itemHeight * totalItems;
    
    function scroll() {
        if (!isPaused) {
            scrollPosition += scrollSpeed;
            
            // Reset position pour scroll infini
            if (scrollPosition >= totalHeight) {
                scrollPosition = 0;
            }
            
            container.style.transform = `translateY(-${scrollPosition}px)`;
        }
        
        animationFrame = requestAnimationFrame(scroll);
    }
    
    // Pause au hover
    container.addEventListener('mouseenter', () => {
        isPaused = true;
    });
    
    container.addEventListener('mouseleave', () => {
        isPaused = false;
    });
    
    // Démarrer l'animation
    scroll();
    
    // Cleanup
    window.addEventListener('beforeunload', () => {
        if (animationFrame) {
            cancelAnimationFrame(animationFrame);
        }
    });
})();
</script>

<style>
/* Animation fluide */
#recentToolsList {
    transition: transform 0.05s linear;
}

/* Effet hover sur les lignes */
.tool-item {
    cursor: pointer;
}

.tool-item:hover .fa-chevron-right {
    transform: translateX(4px);
    transition: transform 0.2s ease;
}
</style>