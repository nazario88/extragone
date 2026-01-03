<?php
// ==========================================
// Configuration des projets de l'écosystème
// ==========================================
$ecosystem_projects = [
    [
        'title' => 'Projets',
        'description' => 'Partage tes projets avec la communauté et obtiens des reviews détaillées de notre équipe.',
        'badge' => 'COMMUNAUTÉ',
        'cta_text' => 'Découvrir',
        'url' => 'https://projets.extrag.one',
        'url_logo' => 'https://projets.extrag.one/assets/images/logo_projets.png'
    ],
    [
        'title' => 'Nomi',
        'description' => 'Génère le nom parfait pour ton projet en quelques secondes avec l\'IA. Gratuit et illimité.',
        'badge' => 'IA GÉNÉRATIVE',
        'cta_text' => 'Essayer Nomi',
        'url' => 'https://nomi.extrag.one',
        'url_logo' => 'https://nomi.extrag.one/images/nomi_logo.webp'
    ],
    [
        'title' => 'Extension Chrome',
        'description' => 'Détecte automatiquement les alternatives françaises pendant ta navigation. Léger et rapide.',
        'badge' => 'GRATUIT',
        'cta_text' => 'Installer',
        'url' => 'https://chromewebstore.google.com/detail/fjcfkhkhplfpngmffdkfekcpelkaggof?utm_source=item-share-cb',
        'url_logo' => 'https://www.extrag.one/assets/img/google_chrome.webp'
    ]
];
?>

<!-- Section 3 Projets -->
<div class="w-full mb-8">
    <h2 class="font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400 text-left mb-8">
        Découvre l'écosystème eXtragone
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($ecosystem_projects as $project): ?>
        <div class="group bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 flex flex-col">
            
            <!-- Contenu principal -->
            <div class="flex flex-col items-center text-center flex-grow">
                <!-- Icône -->
                <div class="w-20 h-20 mb-4 bg-white dark:bg-slate-800/50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <img src="<?= $project['url_logo'] ?>" 
                        alt="<?= $project['title'] ?>" 
                        class="w-full h-full object-contain">
                </div>
                
                <!-- Badge -->
                <span class="inline-block px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold rounded-full mb-3">
                    <?= $project['badge'] ?>
                </span>
                
                <!-- Titre -->
                <h3 class="text-xl font-bold mb-3 group-hover:text-blue-500 transition-colors">
                    <?= $project['title'] ?>
                </h3>
                
                <!-- Description -->
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                    <?= $project['description'] ?>
                </p>
            </div>
            
            <!-- CTA aligné en bas -->
            <a href="<?= $project['url'] ?>" 
               class="w-full px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all duration-300 group-hover:shadow-lg flex items-center justify-center gap-2 mt-auto">
                <i class="fa-solid fa-arrow-right"></i> <?= $project['cta_text'] ?>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>