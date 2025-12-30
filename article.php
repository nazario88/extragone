<?php
include 'includes/config.php';

/* Récupération des informations de l'article
——————————————————————————————————————————————————*/
$slug = isset($_GET['slug']) ? $_GET['slug'] : null;

$sql = $pdo->prepare('SELECT id, title, slug, content_html, image, description, created_at, DATE_FORMAT(created_at, "%d/%m/%Y à %Hh%i") as date_publi FROM extra_articles WHERE slug=?');
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

<!-- Schema.org Article en JSON-LD -->
<script type="application/ld+json">
<?php
$schema = [
    "@context" => "https://schema.org",
    "@type" => "Article",
    "headline" => $data_article['title'],
    "description" => $data_article['description'],
    "author" => [
        "@type" => "Person",
        "name" => "InnoSpira"
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => "eXtragone",
        "logo" => [
            "@type" => "ImageObject",
            "url" => "https://www.extrag.one/assets/img/logo.webp"
        ]
    ],
    "datePublished" => date('c', strtotime($data_article['created_at'])),
    "dateModified" => date('c', strtotime($data_article['created_at'] ?? $data_article['created_at'])),
    "mainEntityOfPage" => [
        "@type" => "WebPage",
        "@id" => $url_canon
    ],
    "image" => [
        "https://www.extrag.one/" . ltrim($data_article['image'], '/')
    ],
    "keywords" => "outils numériques, outils IA, alternatives françaises, projets, outils web, saas, outils gratuits"
];

echo json_encode(
    $schema,
    JSON_UNESCAPED_SLASHES      // Garde les / dans les URLs
    | JSON_UNESCAPED_UNICODE    // Garde les accents (é, è, ç, etc.)
    | JSON_PRETTY_PRINT         // Format lisible (tu peux retirer en prod pour minifier)
);
?>
</script>

<div class="w-full px-5 py-5">
    <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">&rarr; article</p>
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
                <?=$data_article['title']?>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <!-- Bloc de partage compact et moderne -->
            <div class="inline-flex items-center gap-2 bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm rounded-xl px-4 py-2 border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400 mr-1">Partager</span>
                
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?=urlencode($url_canon)?>" 
                   target="_blank"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-all hover:scale-110"
                   title="Partager sur LinkedIn">
                    <i class="fa-brands fa-linkedin text-sm"></i>
                </a>
                
                <a href="https://twitter.com/intent/tweet?url=<?=urlencode($url_canon)?>&text=<?=urlencode($data_article['title'])?>" 
                   target="_blank"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-black hover:bg-gray-800 text-white transition-all hover:scale-110"
                   title="Partager sur Twitter">
                    <i class="fa-brands fa-x-twitter text-sm"></i>
                </a>
                
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?=urlencode($url_canon)?>" 
                   target="_blank"
                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-500 hover:bg-blue-600 text-white transition-all hover:scale-110"
                   title="Partager sur Facebook">
                    <i class="fa-brands fa-facebook text-sm"></i>
                </a>
                
                <button onclick="navigator.clipboard.writeText('<?=$url_canon?>'); this.innerHTML='<i class=\'fa-solid fa-check text-sm\'></i>'; setTimeout(() => this.innerHTML='<i class=\'fa-solid fa-link text-sm\'></i>', 2000)" 
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-600 hover:bg-gray-700 text-white transition-all hover:scale-110"
                        title="Copier le lien">
                    <i class="fa-solid fa-link text-sm"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Container -->
<div class="w-full px-2 md:px-4">

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

    <!-- Bouton voir tous les articles -->
    <div class="text-center my-4 pt-6">
        <a href="articles" class="w-full md:w-auto px-6 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white fill-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 transition-all duration-200 group">
            <span>Voir tous les articles</span>
            <i class="fa-solid fa-arrow-right ml-2 transition-transform duration-200 group-hover:translate-x-1"></i>
        </a>
    </div>
</div>
<?php

include 'includes/footer.php';
?>