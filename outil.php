<?php

include 'includes/config.php';

/* Récupération des informations de l'outil
——————————————————————————————————————————————————*/
$nom = isset($_GET['nom']) ? $_GET['nom'] : null;

$sql = $pdo->prepare('SELECT * FROM extra_tools WHERE is_valid=1 AND slug=?');
$sql->execute(array($nom));
$data_outil = $sql->fetch();
if(!$data_outil) {
    http_response_code(410);
    errorPage("Les informations de l'outil n'ont pas pu être récupérées ☹️");
    exit;
}

/* Récupération d'autres outils 
——————————————————————————————————————————————————*/
if($data_outil['id']) {

    /* Récupération des alternatives 
    ——————————————————————————————————————————————————*/
    $sql = $pdo->prepare('SELECT b.* FROM extra_alternatives a INNER JOIN extra_tools b ON b.id=a.id_alternative WHERE a.id_outil=?');
    $sql->execute(array($data_outil['id']));
    $alternatives = $sql->fetchAll();
    $alternativesNb = count($alternatives);

    /* Récupérer le libellé de la catégorie
    ——————————————————————————————————————————————————*/
    $sql = $pdo->prepare('SELECT c.slug FROM extra_tools_categories c WHERE c.id=?');
    $sql->execute(array($data_outil['categorie_id']));
    $data_category = $sql->fetch();
    $slug_category = $data_category['slug'];

    /* Même catégorie
    ——————————————————————————————————————————————————*/
    $sql = $pdo->prepare('SELECT * FROM extra_tools WHERE is_valid=1 AND categorie_id=? AND id<>? order by rand() limit 3;');
    $sql->execute(array($data_outil['categorie_id'],$data_outil['id']));
    $sameCategory = $sql->fetchAll();
    $sameCategoryNb = count($sameCategory);

    /* Note
    ——————————————————————————————————————————————————*/
    $sql = $pdo->prepare('SELECT  ROUND(AVG(note),2) as average, count(note) as nb FROM extra_tools_notes WHERE id_tool=?');
    $sql->execute(array($data_outil['id']));
    $stats_note = $sql->fetch();

    $myip = getIP();
    $sql = $pdo->prepare('SELECT id FROM extra_tools_notes WHERE id_tool=? and ip=?');
    $sql->execute(array($data_outil['id'], $myip));
    $check_note = $sql->fetch();

    /* +1 pour les stats
    ——————————————————————————————————————————————————*/
    $sql = $pdo->prepare('UPDATE extra_tools SET hits=hits+1 WHERE id=?');
    $sql->execute(array($data_outil['id']));

}



/* SEO
——————————————————————————————————————————————————*/
$title = $data_outil['nom'].' — eXtragone';
$description = mb_strimwidth(strip_tags($data_outil['description']) . ' ' . strip_tags($data_outil['description_longue']), 0, 150) . '...'; 
$image_seo = 'cache/tool-images/tool_'.$data_outil['id'].'.jpg';

$url_canon = 'https://www.extrag.one/outil/'.$data_outil['slug'];

include 'includes/header.php';
?>

<div class="w-full px-5 py-5">
    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; Fiche</p>
    <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
        En savoir + sur <?=$data_outil['nom']?>
    </h1>
</div>

<?php
 // Si pas d'image de logo, on en met une par défaut
if(empty($data_outil['logo'])) $data_outil['logo'] = 'assets/link.jpg';

// Consolidation du lien de l'article
$link_article = (!empty($data_outil['url_article'])) ? '<a class="w-full sm:w-auto px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded transition-all duration-300 transform" href="'.$data_outil['url_article'].'" target="_blank" title="Cliquer pour lire l\'article sur InnoSpira.fr"><i class="fa-solid fa-book-open mr-2"></i> Lire le guide complet</a>':'';

// Label
$is_french = (isset($data_outil['is_french']) && $data_outil['is_french']) 
    ? '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
        <i class="fa-solid fa-flag text-[10px]"></i>
        Français
       </span>' 
    : '';

$is_free = (isset($data_outil['is_free']) && $data_outil['is_free']) 
    ? '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 border border-green-200 dark:border-green-800">
        <i class="fa-solid fa-gift text-[10px]"></i>
        Gratuit
       </span>' 
    : '';

$is_paid = (isset($data_outil['is_paid']) && $data_outil['is_paid']) 
    ? '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
        <i class="fa-solid fa-crown text-[10px]"></i>
        Payant
       </span>' 
    : '';

// Tags
if($data_outil['tags']) {
    $tags = array_filter(explode(',', $data_outil['tags']));
    $tags = array_map('trim', $tags);
}

?>

<!-- Grille 3 colonnes -->
<div class="grid grid-cols-4 gap-4 mx-4">

    <!-- Détail de l'outil -->
    <div class="col-span-4 md:col-span-2 text-sm md:text-base bg-white rounded-xl shadow p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <!-- Titre -->
        <h2 class="text-xl font-bold mb-2">Description de <?php echo htmlspecialchars($data_outil['nom']); ?></h2>

        <!-- Courte description -->
        <p class="text-sm">
            <?php echo htmlspecialchars($data_outil['description']); ?>
            <br>
            <div class="flex flex-wrap gap-2 mt-3">
                <?= $is_french ?>
                <?= $is_free ?>
                <?= $is_paid ?>
            </div>
        </p>

        <!-- Image -->
        <img class="mx-auto h-auto my-3 rounded-md transition-transform duration-300 ease-in-out hover:scale-105" style="max-height: 100px" src="<?php echo htmlspecialchars($data_outil['logo']); ?>" alt="Logo de <?php echo htmlspecialchars($data_outil['nom']); ?>">

        <!-- Capture d'écran -->
        <?php
        if($data_outil['screenshot']) {
            echo '
                <h3 class="text-sm font-bold mt-2 mb-3 uppercase tracking-wider">
                    <i class="fa-solid fa-image"></i> Capture d\'écran
                </h3>
                <div class="screenshot-container">
                    <img class="mx-auto h-auto my-3 rounded-md transition-transform duration-300 ease-in-out hover:scale-105 cursor-pointer rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm" 
                        style="max-height: 200px" 
                        src="'.$data_outil['screenshot'].'" 
                        alt="Screenshot de '.$data_outil['nom'].'"
                        onclick="openImageModal(this)">
                </div>
            ';
        }
        ?>

        <!-- Boutons -->
        <div class="w-full mt-5 flex flex-col sm:flex-row items-center justify-center gap-3">
            <?php
            echo $link_article;
            $url = buildUtmUrl($data_outil['url']);
            ?>
            <a class="w-full md:w-auto px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition" href="<?=$url?>" target="_blank" title="Voir l'outil">
                <i class="fa-solid fa-up-right-from-square mr-2"> </i> Voir le site
            </a>
            <?php if(isAdmin()) echo '<a class="w-full md:w-auto px-3 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition" href="admin/edit-tool.php?id='.$data_outil['id'].'" title="Modifier"><i class="fa-solid fa-pen-to-square mr-2"></i> Modifier</a>'; ?>
        </div>

        <!-- Description longue -->
        <?php
        if($data_outil['description_longue']) {
            echo '
                <h3 class="text-sm font-bold mt-5 mb-3 uppercase tracking-wider">
                    <i class="fa-regular fa-file-lines"></i> Résumé
                </h3>
                <p>'.nl2br(addCssClasses($data_outil['description_longue'])).'</p>
            ';
        }
        ?>

        <!-- Tags -->
        <?php if($data_outil['tags']): ?>
        <h3 class="text-sm font-bold mt-5 mb-3 uppercase tracking-wider">
            <i class="fa-solid fa-tags"></i> Tags
        </h3>
        <div class="flex flex-wrap gap-2 mt-3">
            <?php foreach($tags as $tag): ?>
            <span class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">
                <?= htmlspecialchars($tag) ?>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- 2ème rangée -->
    <div class="col-span-4 md:col-span-1">
        
        <!-- Stats & Infos -->
        <div class="bg-white rounded-xl shadow mb-4 p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            <h3 class="text-sm font-bold mb-3 uppercase tracking-wider">
                <i class="fa-solid fa-chart-simple mr-2"></i>Statistiques
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-eye w-4"></i>Vues
                    </span>
                    <span class="font-semibold text-blue-600 dark:text-blue-400">
                        <?=number_format($data_outil['hits'])?>
                    </span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-star w-4"></i>Notes
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">
                            <?=$stats_note['nb']?> avis
                        </span>
                        <div class="flex items-center">
                            <?php
                            for($i = 1; $i <= $stats_note['average']; $i++) {
                                echo '<i class="fa-solid fa-star text-yellow-400 text-xs"></i>';
                            }
                            $i--;
                            if($stats_note['average'] >= ($i + 0.50)) {
                                echo '<i class="fa-solid fa-star-half text-yellow-400 text-xs"></i>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-calendar w-4"></i>Ajouté
                    </span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">
                        <?=date('d/m/Y', strtotime($data_outil['date_creation']))?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Notation -->
        <div class="bg-white rounded-xl shadow mb-4 p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            <h3 class="text-sm font-bold mb-3 uppercase tracking-wider">
                <i class="fa-regular fa-star mr-2"></i> Attribuez une note
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Votre avis compte ! Aidez les utilisateurs en notant cet outil.
            </p>
            <div class="flex flex-col items-center">
                <?php
                if(!$check_note) {
                    ?>
                <div class="flex items-center space-x-1" id="ratingForm">
                <input type="hidden" name="rating" id="ratingInput" value="0">
                <input type="hidden" name="tool_id" id="toolIdInput" value="<?=$data_outil['id']?>"> <!-- ID de la fiche outil -->

                <div class="flex" id="starContainer">
                    <button type="button" data-value="1" class="star text-2xl text-gray-300 hover:text-yellow-400 transition-colors duration-200">
                    <i class="fa-solid fa-star"></i>
                    </button>
                    <button type="button" data-value="2" class="star text-2xl text-gray-300 hover:text-yellow-400 transition-colors duration-200">
                    <i class="fa-solid fa-star"></i>
                    </button>
                    <button type="button" data-value="3" class="star text-2xl text-gray-300 hover:text-yellow-400 transition-colors duration-200">
                    <i class="fa-solid fa-star"></i>
                    </button>
                    <button type="button" data-value="4" class="star text-2xl text-gray-300 hover:text-yellow-400 transition-colors duration-200">
                    <i class="fa-solid fa-star"></i>
                    </button>
                    <button type="button" data-value="5" class="star text-2xl text-gray-300 hover:text-yellow-400 transition-colors duration-200">
                    <i class="fa-solid fa-star"></i>
                    </button>
                </div>
                <span class="ml-2 text-sm text-gray-600" id="ratingLabel">0 / 5</span>
                </div>
                <!-- Message de confirmation -->
                <p id="ratingMessage" class="mx-auto text-center p-2 bg-green-300 text-green-800 rounded text-sm mt-2 hidden">Merci pour votre note !</p>
                <script>
                const stars = document.querySelectorAll('.star');
                const ratingInput = document.getElementById('ratingInput');
                const toolIdInput = document.getElementById('toolIdInput');
                const ratingLabel = document.getElementById('ratingLabel');
                const ratingMessage = document.getElementById('ratingMessage');
                let currentRating = 4;

                stars.forEach((star, index) => {
                    const value = index + 1;

                    star.addEventListener('mouseover', () => {
                    highlightStars(value);
                    });

                    star.addEventListener('mouseout', () => {
                    highlightStars(currentRating);
                    });

                    star.addEventListener('click', async () => {
                    currentRating = value;
                    ratingInput.value = currentRating;
                    ratingLabel.textContent = `${currentRating} / 5`;
                    highlightStars(currentRating);

                    // Données à envoyer
                    const payload = {
                        rating: currentRating,
                        tool_id: toolIdInput.value
                    };

                    try {
                        const response = await fetch('includes/push_note.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                        });

                        if (response.ok) {
                        ratingMessage.classList.remove('hidden');
                        ratingMessage.textContent = 'Merci pour votre note !';
                        } else {
                        throw new Error('Erreur serveur');
                        }
                    } catch (error) {
                        ratingMessage.classList.remove('hidden');
                        ratingMessage.classList.replace('bg-green-300', 'bg-red-300');
                        ratingMessage.classList.replace('text-green-800', 'text-red-800');
                        ratingMessage.textContent = 'Erreur lors de l’envoi. Veuillez réessayer.';
                    }
                    });
                });

                function highlightStars(value) {
                    stars.forEach((star, index) => {
                    if (index < value) {
                        star.classList.add('text-yellow-400');
                        star.classList.remove('text-gray-300');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300');
                    }
                    });
                }
                </script>

                    <?php
                }
                else {
                    echo '<p class="mx-auto text-center p-2 bg-green-300 text-green-800 rounded text-sm">Merci d\'avoir voté !</p>';
                }
                ?>
            </div>
        </div>
        <!-- Partage Social -->
        <div class="bg-white rounded-xl shadow mb-4 p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            <h3 class="text-sm font-bold mb-3 uppercase tracking-wider">
                <i class="fa-solid fa-share-nodes mr-2"></i>Partager
            </h3>
            <div class="grid grid-cols-2 gap-2">
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?=urlencode($url_canon)?>" 
                target="_blank"
                class="flex items-center justify-center gap-2 p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm">
                    <i class="fa-brands fa-linkedin"></i>
                    LinkedIn
                </a>
                
                <a href="https://twitter.com/intent/tweet?url=<?=urlencode($url_canon)?>&text=<?=urlencode('Découvrez '.$data_outil['nom'].' sur eXtragone')?>" 
                target="_blank"
                class="flex items-center justify-center gap-2 p-2 bg-black hover:bg-gray-800 text-white rounded-lg transition text-sm">
                    <i class="fa-brands fa-x-twitter"></i>
                    Twitter
                </a>
                
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?=urlencode($url_canon)?>" 
                target="_blank"
                class="flex items-center justify-center gap-2 p-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                    <i class="fa-brands fa-facebook"></i>
                    Facebook
                </a>
                
                <button onclick="navigator.clipboard.writeText('<?=$url_canon?>'); this.innerHTML='<i class=\'fa-solid fa-check\'></i> Copié !'" 
                        class="flex items-center justify-center gap-2 p-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition text-sm">
                    <i class="fa-solid fa-copy"></i>
                    Copier
                </button>
            </div>
        </div>

        <!-- Actions diverses -->
        <div class="bg-white rounded-xl shadow mb-4 p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            <h3 class="text-sm font-bold mb-3 uppercase tracking-wider">
                <i class="fa-solid fa-bolt mr-2"></i>Actions
            </h3>
            <div class="space-y-2">
                <a href="outils/categorie/<?=$slug_category?>" 
                class="flex items-center gap-3 p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700 transition text-sm group">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-folder text-blue-600 dark:text-blue-400 text-xs"></i>
                    </div>
                    <span class="flex-1">Voir la catégorie</span>
                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                </a>
                
                <a href="ajouter?site=<?=urlencode($data_outil['url'])?>" 
                class="flex items-center gap-3 p-2 rounded-lg hover:bg-green-50 dark:hover:bg-slate-700 transition text-sm group">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-plus text-green-600 dark:text-green-400 text-xs"></i>
                    </div>
                    <span class="flex-1">Proposer une alternative</span>
                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                </a>
                
                <a href="contact" 
                class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 dark:hover:bg-slate-700 transition text-sm group">
                    <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-flag text-orange-600 dark:text-orange-400 text-xs"></i>
                    </div>
                    <span class="flex-1">Signaler une erreur</span>
                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- 3ème rangée -->
    <div class="col-span-4 md:col-span-1">
        <?php if(!isset($data_outil['is_french']) || !$data_outil['is_french']): ?>
        <div class="col-span-4 md:col-span-2 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl shadow-lg mb-4 p-4 border-2 border-blue-200 dark:border-blue-700">
            <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                <span>Alternatives françaises</span>
                <?php echo $flag_FR; ?>
            </h2>
            
            <?php if(!$alternativesNb): ?>
                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-search text-3xl mb-2 opacity-50"></i>
                    <p class="text-sm mb-3">Aucune alternative trouvée</p>
                    <a class="inline-block px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition" 
                    href="ajouter?site=<?=urlencode($data_outil['url'])?>" 
                    title="Proposer une alternative">
                        <i class="fa-solid fa-plus mr-2"></i>Proposer une alternative
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($alternatives as $alternative): ?>
                    <a href="outil/<?=$alternative['slug']?>" 
                    class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg border border-blue-200 dark:border-blue-700 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md transition-all group">
                        <img src="<?=$alternative['logo']?>" 
                            alt="<?=htmlspecialchars($alternative['nom'])?>" 
                            class="w-12 h-12 rounded object-contain flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-sm group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                <?=htmlspecialchars($alternative['nom'])?>
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                <?=htmlspecialchars($alternative['description'])?>
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Dans la même catégorie -->
        <div class="col-span-4 md:col-span-<?=(!isset($data_outil['is_french']) || !$data_outil['is_french']) ? '2' : '3'?> bg-white rounded-xl shadow p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
            <h2 class="text-lg font-bold mb-3">Dans la même catégorie</h2>
            
            <?php if(!$sameCategoryNb): ?>
                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-inbox text-3xl mb-2 opacity-50"></i>
                    <p class="text-sm">Aucun autre outil</p>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($sameCategory as $similar): ?>
                    <a href="outil/<?=$similar['slug']?>" 
                    class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-slate-700 transition-all group">
                        <img src="<?=htmlspecialchars($similar['logo'])?>" 
                            alt="<?=htmlspecialchars($similar['nom'])?>" 
                            class="w-12 h-12 rounded object-contain flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-sm group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                <?=htmlspecialchars($similar['nom'])?>
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                <?=htmlspecialchars($similar['description'])?>
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="col-span-4">
    <?php
    // CTA pour Nomi
    renderCTA(
        'fa-solid fa-wand-magic-sparkles',
        'Bloqué sur le nom de ton projet ? 🚀',
        'Nomi génère des noms percutants et disponibles en 3 secondes chrono. Testez, c\'est gratuit !',
        'Essayer Nomi',
        'https://nomi.extrag.one',
        'bg-white',
        'bg-primary'
    );
    
    // CTA pour la communauté (à décommenter plus tard)
    /*
    renderCTA(
        'fa-solid fa-users',
        'Rejoins la communauté !',
        'Découvre les projets en cours de développement et partage ton avis avec la communauté.',
        'Voir les projets',
        '/communaute',
        'bg-green-50',
        'bg-green-500'
    );
    */
    ?>
    </div>
</div>

<?php

// Schema - toujours affiché
echo '
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "' . htmlspecialchars($data_outil['nom'], ENT_QUOTES) . '",
    "url": "https://www.extrag.one/outil/' . $data_outil['slug'] . '",
    "image": "https://www.extrag.one/' . htmlspecialchars($data_outil['screenshot'], ENT_QUOTES) . '",
    "description": "' . htmlspecialchars(strip_tags($data_outil['description']), ENT_QUOTES) . '",
    "applicationCategory": "BusinessApplication",
    "operatingSystem": "Web"';

// Ajouter les notes seulement si elles existent
if (isset($stats_note) && $stats_note['average'] > 0) {
    echo ',
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "' . $stats_note['average'] . '",
        "reviewCount": "' . $stats_note['nb'] . '"
    }';
}

echo '
}
</script>
';

include 'includes/footer.php';
?>