<?php
include 'includes/config.php';

/* Récupération des informations de l'article
——————————————————————————————————————————————————*/
$slug = isset($_GET['slug']) ? $_GET['slug'] : null;

$sql = $pdo->prepare('SELECT id, title, slug, content_html, image, description, DATE_FORMAT(created_at, "%d/%m/%Y à %Hh%i") as date_publi FROM extra_articles WHERE slug=?');
$sql->execute(array($slug));
$data_article = $sql->fetch();
if(!$data_article) errorPage("Les informations de l'article n'ont pas pu être récupérées ☹️");

/* Récupération d'autres outils 
——————————————————————————————————————————————————*/
if($data_article['id']) {

    /* +1 pour les stats
    ——————————————————————————————————————————————————*/
    $sql = $pdo->prepare('UPDATE extra_articles SET hits=hits+1 WHERE id=?');
    $sql->execute(array($data_article['id']));

}

/* SEO
——————————————————————————————————————————————————*/
$title = $data_article['title'];
$description = $data_article['description'];
$image_seo = $data_article['image'];

require_once 'admin/includes/auth.php';

$url_canon = 'https://www.extrag.one/article/'.$data_article['slug'];

include 'includes/header.php';
?>

<div class="w-full px-5 py-5">
    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; article</p>
    <div class="grid grid-cols-2">
        <div class="col-span-2 md:col-span-1">
            <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
                <?=$data_article['title']?>
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

<!-- Container -->
<div class="w-full w-full px-2 md:px-4">

    <!-- Détail de l'article -->
    <div class="bg-white rounded-xl shadow p-4 border border-slate-200 dark:bg-slate-800 dark:border-slate-700">

        <!-- Date -->
        <p class="flex mb-2 items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">
            publié le <?=$data_article['date_publi']?>
        </p>
        <!-- Content -->
        <?php

        echo addCssClasses($data_article['content_html']);
        
        if(is_admin_logged_in()) {
            echo '
            <div class="w-full mt-5 flex items-center justify-center gap-3">
                <a class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-700 transition" href="admin/edit-article.php?id='.$data_article['id'].'" title="Modifier"><i class="fa-solid fa-pen-to-square"></i> Modifier</a>
            </div>
           ';
        }
        ?>
        </div>
    </div>
</div>
<?php

include 'includes/footer.php';
?>