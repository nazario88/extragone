<?php
include 'includes/config.php';

/* SEO
——————————————————————————————————————————————————*/
$title = 'Liste des catégories';
$description = "Naviguez dans les différentes thématiques des outils listés au sein d'eXtragone pour mieux vous y retrouver.";

// Récupérer toutes les catégories pour le filtre
$stmt = $pdo->query('
    SELECT a.*, count(b.categorie_id) as nb_tools
    FROM extra_tools_categories a
    LEFT JOIN extra_tools b ON b.categorie_id=a.id
    GROUP BY a.id ORDER BY a.nom ASC');
$categories = $stmt->fetchAll();

require_once 'admin/includes/auth.php';

$url_canon = 'https://extrag.one/categories';

include 'includes/header.php';
?>

<div class="w-full px-5 py-5">
    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; Catégories</p>
    <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
        Liste catégories des meilleurs outils
    </h1>
    <p class="text-sm">
        Découvre les catégories des meilleurs outils autour de l'intelligence artificielle, la productivité, la création d'images, &hellip;<br>
        Sélectionne une catégorie pour découvrir les outils associés et les alternatives françaises.
    </p>
</div>

<!-- Separateur -->
<hr class="h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

<div class="px-5 py-5 grid grid-cols-4 gap-6">
    <?php foreach ($categories as $categorie): ?>
    <div class="bg-slate-100 hover:bg-white rounded-xl shadow p-4 flex flex-col items-center text-center border border-slate-200 dark:bg-slate-800 dark:border-slate-700 hover:dark:bg-slate-700 transition-colors duration-300">      
         <a class="font-medium" href="outils/categorie/<?=$categorie['slug']; ?>" title="Naviguer vers la catégorie">
            <i class="w-full text-3xl text-primary <?=$categorie['class_icon']; ?>"></i>
            <?php echo $categorie['nom']; ?>
         </a>
         <p class="text-sm"><?=$categorie['description'];?></p>
     </div>
     <?php endforeach; ?>
 </div>

 <?php
include 'includes/footer.php';
?>