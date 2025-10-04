<?php
include 'includes/config.php';

/* Récupération des informations de l'outil
——————————————————————————————————————————————————*/
$nom = isset($_GET['nom']) ? $_GET['nom'] : null;

$sql = $pdo->prepare('SELECT * FROM extra_tools WHERE is_valid=1 AND slug=?');
$sql->execute(array($nom));
$data_outil = $sql->fetch();
if(!$data_outil) errorPage("Les informations de l'outil n'ont pas pu être récupérées ☹️");

/* Récupération d'autres outils 
——————————————————————————————————————————————————*/
if($data_outil['id']) {

    /* Récupération des alternatives 
    ——————————————————————————————————————————————————*/
    $sql = $pdo->prepare('SELECT b.* FROM extra_alternatives a INNER JOIN extra_tools b ON b.id=a.id_alternative WHERE a.id_outil=?');
    $sql->execute(array($data_outil['id']));
    $alternatives = $sql->fetchAll();
    $alternativesNb = count($alternatives);

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
$description = mb_strimwidth($data_outil['description'] . ' ' . $data_outil['description_longue'], 0, 150) . '...'; 
$image_seo = $data_outil['screenshot'];


require_once 'admin/includes/auth.php';

$url_canon = 'https://www.extrag.one/outil/'.$data_outil['slug'];

include 'includes/header.php';
?>

<div class="w-full px-5 py-5">
    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; Fiche</p>
    <div class="grid grid-cols-2">
        <div class="col-span-2 md:col-span-1">
            <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
                En savoir + sur <?=$data_outil['nom']?>
            </h1>
        </div>
        <div class="col-span-2 mt-2 py-2 md:py-1 md:col-span-1 md:text-right">
            <!-- AddToAny BEGIN -->
            <span class="pr-3 text-sm">Partager la page</span>
            <div class="a2a_kit a2a_kit_size_24 a2a_default_style float-right">
                <a class="a2a_dd" href="https://www.addtoany.com/share"></a>
                <a class="a2a_button_linkedin"></a>
                <a class="a2a_button_whatsapp"></a>
                <a class="a2a_button_facebook"></a>
                <a class="a2a_button_facebook_messenger"></a>
                <a class="a2a_button_copy_link"></a>
                <a class="a2a_button_email"></a>
            </div>
            <script defer src="https://static.addtoany.com/menu/page.js"></script>
            <!-- AddToAny END -->
        </div>
    </div>
</div>

<?php
 // Si pas d'image de logo, on en met une par défaut
if(empty($data_outil['logo'])) $data_outil['logo'] = 'assets/link.jpg';

// Consolidation du lien de l'article
$link_article = (!empty($data_outil['url_article'])) ? '<a class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition" href="'.$data_outil['url_article'].'" target="_blank" title="Cliquer pour lire l\'article sur InnoSpira.fr"><i class="fa-regular fa-newspaper"></i> Lire l\'article</a>':'';

// Label
$is_french = (isset($data_outil['is_french']) && $data_outil['is_french'])  ? '<span class="mx-1 bg-yellow-300 text-yellow-900 inline-block text-center px-2 py-1 rounded text-md font-semibold"><i class="fa-solid fa-check"></i> Outil français</span>':'';
$is_free = (isset($data_outil['is_free']) && $data_outil['is_free'])        ? '<span class="mx-1 bg-yellow-300 text-yellow-900 inline-block text-center px-2 py-1 rounded text-md font-semibold"><i class="fa-solid fa-check"></i> Gratuit</span>':'';
$is_paid = (isset($data_outil['is_paid']) && $data_outil['is_paid'])        ? '<span class="mx-1 bg-yellow-300 text-yellow-900 inline-block text-center px-2 py-1 rounded text-md font-semibold"><i class="fa-solid fa-check"></i> Payant</span>':'';

// si outil FR, le premier div prends 3/4 au lieu de 2/2
$largeur_premier_div = (isset($data_outil['is_french']) && $data_outil['is_french']) ? 'col-span-3' : 'col-span-2';

?>

<!-- Grille 3 colonnes -->
<div class="grid grid-cols-4 gap-4 mx-4">

    <!-- Détail de l'outil -->
    <div class="col-span-4 md:<?=$largeur_premier_div?> bg-white rounded-xl shadow p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <!-- Titre -->
        <div class="grid grid-cols-2">
            <div class="col-span-2 md:col-span-1">
                <h2 class="text-xl font-bold mb-2">Description de <?php echo htmlspecialchars($data_outil['nom']); ?></h2>
            </div>
            <div class="col-span-2 md:col-span-1 text-right" title="Note actuelle : <?=$stats_note['average']?>/5">
                <?php
                for($i = 1; $i <= $stats_note['average']; $i++) {
                    echo '<i class="fa-solid fa-star text-yellow-400"></i>';
                }
                $i--;
                if($stats_note['average'] >= ($i + 0.50)) echo '<i class="fa-solid fa-star-half text-yellow-900"></i>';
                ?>
            </div>
        </div>

        <!-- Courte description -->
        <p class="text-sm">
            <?php echo htmlspecialchars($data_outil['description']); ?>
            <br>
            <?php echo $is_french . $is_free . $is_paid ?>
        </p>

        <!-- Image -->
        <img class="mx-auto h-auto my-3 rounded-md transition-transform duration-300 ease-in-out hover:scale-105" style="max-height: 100px" src="<?php echo htmlspecialchars($data_outil['logo']); ?>" alt="Logo de <?php echo htmlspecialchars($data_outil['nom']); ?>">

        <!-- Notation -->
        <?php
        if(!$check_note) {
            ?>
        <p class="text-sm">
            Attribuez une note à cet outil !
        </p>
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

        <!-- Capture d'écran -->
        <?php
        if($data_outil['screenshot']) {
            echo '
                <h3 class="font-bold mt-2">Capture d\'écran</h3>
                <div class="screenshot-container">
                    <img class="mx-auto h-auto my-3 rounded-md transition-transform duration-300 ease-in-out hover:scale-105 cursor-pointer" 
                        style="max-height: 200px" 
                        src="'.$data_outil['screenshot'].'" 
                        alt="Screenshot de '.$data_outil['nom'].'"
                        onclick="openImageModal(this)">
                </div>
            ';
        }
        ?>

        <!-- Boutons -->
        <div class="w-full mt-5 flex items-center justify-center gap-3">
            <?php echo $link_article; ?>
            <a class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition" href="<?=$data_outil['url']?>" target="_blank" title="Voir l'outil"><i class="fa-solid fa-up-right-from-square"> </i> Voir le site</a>
            <?php if(is_admin_logged_in()) echo '<a class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition" href="admin/edit-tool.php?id='.$data_outil['id'].'" title="Modifier"><i class="fa-solid fa-pen-to-square"></i> Modifier</a>'; ?>
        </div>

        <!-- Description longue -->
        <?php
        if($data_outil['description_longue']) {
            echo '
                <h3 class="font-bold mt-2">Résumé</h3>
                <p class="text-sm">
                    '.nl2br(addCssClasses($data_outil['description_longue'])).'
                </p>
            ';
        }
        ?>
    </div>

    

    <!-- Equivalents -->
    <?php
    if(isset($data_outil['is_french']) && $data_outil['is_french']) {
        // Si je décide d'ajouter un encart pour les outils déjà FR ..
    }
    else {
        ?>
    <div class="col-span-4 md:col-span-1 bg-white rounded-xl shadow p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <!-- Titre -->
        <h2 class="text-xl font-bold mb-2 flex gap-2">
            Alternatives françaises
            <?php echo $flag_FR; ?>
        </h2>
        <!-- Outils -->
        <?php
        if(!$alternativesNb) {
            echo '
                <p class="text-center mt-5">Aucune alternative n\'a été trouvée ☹️.</p>
                <p class="text-center mt-5"><a class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition" href="ajouter" title="Ajouter un outil"><i class="fa-solid fa-plus"></i> Proposer un outil</a></p>
            ';
        }
        else {
            foreach ($alternatives as $alternatives): ?>
                <div class="my-2 bg-white rounded-xl shadow p-4 flex flex-col items-center text-center border border-slate-200 dark:bg-slate-700 dark:border-slate-600">
                    <a href="outil/<?php echo $alternatives['slug']; ?>">
                        <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($alternatives['nom']); ?></h2>
                    </a>
                    <div class="h-[100px]">
                        <a href="outil/<?php echo $alternatives['slug']; ?>" title="En savoir +">
                            <img class="w-full h-auto mb-2 rounded-md transition-transform duration-300 ease-in-out hover:scale-105 max-h-[100px]" src="<?php echo $alternatives['logo']; ?>" alt="Logo de <?php echo htmlspecialchars($alternatives['nom']); ?>">
                        </a>
                    </div>
                    <p class="text-sm"><?php echo htmlspecialchars($alternatives['description']); ?></p>
                </div>            
            <?php
            endforeach;
        }
        echo '
    </div>';
    }
    ?>
    <!-- Même catégorie -->
    <div class="col-span-4 md:col-span-1 bg-white rounded-xl shadow p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <h2 class="text-xl font-bold mb-2">Dans la même catégorie</h2>
        <!-- Outils -->
        <?php
        if(!$sameCategoryNb) {
            echo '
                <p class="text-center mt-5">Aucun autre outil n\'a été trouvé ☹️.</p>
                <p class="text-center mt-5"><a class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition" href="ajouter" title="Ajouter un outil"><i class="fa-solid fa-plus"></i> Proposer un outil</a></p>
            ';
        }
        else {
            foreach ($sameCategory as $sameCategory): ?>
                <div class="my-2 bg-white rounded-xl shadow p-4 flex flex-col items-center text-center border border-slate-200 dark:bg-slate-700 dark:border-slate-600">
                    <a href="outil/<?php echo $sameCategory['slug']; ?>">
                        <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($sameCategory['nom']); ?></h2>
                    </a>
                    <div class="h-[100px]">
                        <a href="outil/<?php echo $sameCategory['slug']; ?>" title="En savoir +">
                            <img class="w-full h-auto mb-2 rounded-md transition-transform duration-300 ease-in-out hover:scale-105 max-h-[100px]" src="<?php echo htmlspecialchars($sameCategory['logo']); ?>" alt="Logo de <?php echo htmlspecialchars($sameCategory['nom']); ?>">
                        </a>
                    </div>
                    <p class="text-sm"><?php echo htmlspecialchars($sameCategory['description']); ?></p>
                </div>            
            <?php
            endforeach;
        }
        echo '
    </div>';
    ?>

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

//schema
if((isset($stats_note)) && ($stats_note['average'] > 0)) {
    echo '
        <script type="application/ld+json">
            { "@context": "http://schema.org",
                "@type": "WebApplication",
                "name": "'.$data_outil['nom'].'",
                "image": "'.$data_outil['screenshot'].'",
                "description": "'.$data_outil['description'].'",
                "applicationCategory": "Utility",
                "operatingSystem": "Web",
                "aggregateRating":
                    {"@type": "AggregateRating",
                    "ratingValue": "'.$stats_note['average'].'",
                    "reviewCount": "'.$stats_note['nb'].'"
                    }
            }
        </script>
    ';
}

include 'includes/footer.php';
?>