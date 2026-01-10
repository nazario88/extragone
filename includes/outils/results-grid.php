<?php
/**
 * Affichage de la grille des résultats avec gestion du "0 résultat"
 */

// Message si aucun résultat
if ($nombre_outils === 0): ?>
    <div class="text-center py-12 px-6">
        <!-- Icône -->
        <div class="inline-block p-6 bg-slate-100 dark:bg-slate-800 rounded-full mb-6">
            <i class="fa-solid fa-inbox text-6xl text-slate-400"></i>
        </div>
        
        <!-- Message principal -->
        <h3 class="text-2xl font-bold mb-3">Aucun outil trouvé</h3>
        
        <?php if (!empty($recherche)): ?>
            <p class="text-slate-600 dark:text-slate-400 mb-6 max-w-md mx-auto">
                Aucun résultat pour 
                <span class="font-semibold text-slate-900 dark:text-white">"<?= htmlspecialchars($recherche) ?>"</span>
                <?php if ($search_in === 'name'): ?>
                    dans le nom des outils.
                <?php elseif ($search_in === 'description'): ?>
                    dans les descriptions.
                <?php else: ?>
                    dans notre base de données.
                <?php endif; ?>
            </p>
        <?php endif; ?>
        
        <!-- Suggestions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-6">
            
            <!-- Élargir la recherche -->
            <?php if ($suggest_expand && !empty($expand_url)): ?>
            <a href="<?= htmlspecialchars($expand_url) ?>" 
               class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                <i class="fa-solid fa-magnifying-glass-plus mr-2"></i>
                Élargir à la description
            </a>
            <?php endif; ?>
            
            <!-- Ajouter un outil -->
            <a href="ajouter<?= !empty($recherche) ? '?nom=' . urlencode($recherche) : '' ?>" 
               class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                <i class="fa-solid fa-plus-circle mr-2"></i>
                Proposer cet outil
            </a>
        </div>
        
        <!-- Conseils -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 max-w-2xl mx-auto text-left">
            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-lightbulb"></i>
                Suggestions :
            </h4>
            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-green-600 mt-0.5"></i>
                    <span>Vérifiez l'orthographe de votre recherche</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-green-600 mt-0.5"></i>
                    <span>Essayez des termes plus génériques</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-green-600 mt-0.5"></i>
                    <span>Utilisez les <button onclick="document.getElementById('advanced-search').classList.remove('hidden')" class="underline font-medium">filtres avancés</button></span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-green-600 mt-0.5"></i>
                    <span>Parcourez les <a href="categories" class="underline font-medium">catégories</a></span>
                </li>
            </ul>
        </div>
    </div>

<?php else: ?>
    <!-- Affichage des cartes d'outils -->
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
<?php endif; ?>