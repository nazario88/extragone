<?php
/**
 * Formulaire de recherche avancée (masqué par défaut)
 */
?>

<div class="mb-6">
    <!-- Bouton pour afficher/masquer -->
    <button 
        type="button"
        onclick="document.getElementById('advanced-search').classList.toggle('hidden')"
        class="w-full md:w-auto px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white rounded-lg transition-colors text-sm font-medium flex items-center justify-center gap-2">
        <i class="fa-solid fa-sliders"></i>
        <span>Afficher les filtres avancés</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    
    <!-- Formulaire avancé -->
    <div id="advanced-search" class="hidden mt-4 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
        <form method="GET" action="outils">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Nom de l'outil -->
                <div>
                    <label for="search_name" class="block text-sm font-medium mb-2">
                        <i class="fa-solid fa-tag mr-2 text-blue-500"></i>
                        Nom de l'outil
                    </label>
                    <input 
                        type="text" 
                        id="search_name" 
                        name="q" 
                        value="<?= htmlspecialchars($recherche) ?>"
                        placeholder="Ex: ChatGPT, Notion, Slack..."
                        class="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                
                <!-- Recherche dans -->
                <div>
                    <label for="search_in" class="block text-sm font-medium mb-2">
                        <i class="fa-solid fa-crosshairs mr-2 text-green-500"></i>
                        Chercher dans
                    </label>
                    <select 
                        id="search_in" 
                        name="search_in"
                        class="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="name" <?= $search_in === 'name' ? 'selected' : '' ?>>Nom uniquement (rapide)</option>
                        <option value="description" <?= $search_in === 'description' ? 'selected' : '' ?>>Description</option>
                        <option value="all" <?= $search_in === 'all' ? 'selected' : '' ?>>Partout (lent)</option>
                    </select>
                </div>
                
                <!-- URL du site -->
                <div>
                    <label for="search_url" class="block text-sm font-medium mb-2">
                        <i class="fa-solid fa-link mr-2 text-purple-500"></i>
                        URL du site
                    </label>
                    <input 
                        type="text" 
                        id="search_url" 
                        name="url" 
                        value="<?= htmlspecialchars($search_url) ?>"
                        placeholder="Ex: notion.so, slack.com..."
                        class="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                
                <!-- Catégorie -->
                <div>
                    <label for="search_categorie" class="block text-sm font-medium mb-2">
                        <i class="fa-solid fa-folder mr-2 text-orange-500"></i>
                        Catégorie
                    </label>
                    <select 
                        id="search_categorie" 
                        name="categorie"
                        class="w-full px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= $categorie_slug === $cat['slug'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?> (<?= $cat['nb_tools'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Français uniquement -->
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                        <input 
                            type="checkbox" 
                            name="is_french" 
                            value="1" 
                            <?= $is_french === 1 ? 'checked' : '' ?>
                            class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-2 focus:ring-blue-500">
                        <span class="flex items-center gap-2">
                            <?= $flag_FR ?>
                            <span class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                                Afficher uniquement les outils français
                            </span>
                        </span>
                    </label>
                </div>
            </div>
            
            <!-- Boutons d'action -->
            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button 
                    type="submit"
                    class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-search mr-2"></i>
                    Rechercher
                </button>
                
                <a 
                    href="outils"
                    class="px-6 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-900 dark:text-white font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-rotate-left mr-2"></i>
                    Réinitialiser
                </a>
                
                <span class="ml-auto text-xs text-slate-500 dark:text-slate-400 flex items-center">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    Astuce : Laissez vide pour voir tous les outils
                </span>
            </div>
        </form>
    </div>
</div>