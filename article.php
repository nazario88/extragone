<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include 'includes/config.php';

/* Récupération des informations de l'article
——————————————————————————————————————————————————*/
$slug = isset($_GET['slug']) ? $_GET['slug'] : null;

$sql = $pdo->prepare('SELECT id, title, slug, content_html, image, description, created_at, DATE_FORMAT(created_at, "%d/%m/%Y à %Hh%i") as date_publi FROM extra_articles WHERE slug=?');
$sql->execute(array($slug));
$data_article = $sql->fetch();
if(!$data_article) errorPage("Les informations de l'article n'ont pas pu être récupérées ☹️");

/* +1 pour les stats
——————————————————————————————————————————————————*/
if($data_article['id']) {
    $sql = $pdo->prepare('UPDATE extra_articles SET hits=hits+1 WHERE id=?');
    $sql->execute(array($data_article['id']));
}

/* Charger les fonctions articles
——————————————————————————————————————————————————*/
require_once 'includes/article/functions.php';

/* Préparer la table des matières et injecter les ancres
——————————————————————————————————————————————————*/
$toc = extractTableOfContents($data_article['content_html']);
$data_article['content_html'] = injectAnchorsInContent($data_article['content_html'], $toc);

/* SEO
——————————————————————————————————————————————————*/
$title = $data_article['title'];
$description = $data_article['description'];
$image_seo = $data_article['image'];
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
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);
?>
</script>

<!-- CSS pour smooth scroll -->
<style>
html {
    scroll-behavior: smooth;
}

/* Offset pour les ancres (à cause du header sticky) */
h2[id], h3[id] {
    scroll-margin-top: 100px;
}
</style>

<div class="w-full px-2 md:px-4">

    <!-- En-tête de l'article -->
    <div class="max-w-7xl mx-auto">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400 mt-4">
        <a href="<?= $base ?>" class="hover:text-blue-500 transition-colors">
            <span class="ml-1">Accueil</span>
        </a>
        
        &rarr;
        
        <a href="articles" class="hover:text-blue-500 transition-colors">
            Articles
        </a>
        
        &rarr;
        
        <span class="text-gray-900 dark:text-gray-200 font-medium truncate">
            <?= htmlspecialchars($data_article['title']) ?>
        </span>
    </nav> 

        <h1 class="text-3xl md:text-4xl font-bold my-2">
            <?= htmlspecialchars($data_article['title']) ?>
        </h1>
        
        <!-- Date de publication -->
        <p class="flex mb-4 items-start gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">
            Publié le <?= $data_article['date_publi'] ?>
        </p>
    </div>

    <!-- Grille : Contenu principal + Sidebar -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Colonne principale (contenu de l'article) -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-6 md:p-8 border border-slate-200 dark:border-slate-700">
                
                <!-- Image principale (si présente) -->
                <?php if ($data_article['image']): ?>
                <img src="<?= htmlspecialchars($data_article['image']) ?>" 
                     alt="<?= htmlspecialchars($data_article['title']) ?>"
                     class="w-full h-auto rounded-xl mb-6 shadow-lg">
                <?php endif; ?>
                
                <!-- Contenu de l'article -->
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    <?= addCssClasses($data_article['content_html']) ?>
                </div>
                
                <!-- Bouton admin (si connecté en admin) -->
                <?php if(isAdmin()): ?>
                <div class="w-full mt-8 pt-6 border-t border-slate-200 dark:border-slate-700 flex items-center justify-center gap-3">
                    <a class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-700 transition" 
                       href="admin/edit-article.php?id=<?= $data_article['id'] ?>" 
                       title="Modifier">
                        <i class="fa-solid fa-pen-to-square mr-2"></i>Modifier
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Navigation Article Précédent / Suivant -->
            <?php include 'includes/article/navigation.php'; ?>
        </div>

        <!-- Sidebar sticky -->
        <div class="lg:col-span-1">
            <?php include 'includes/article/sidebar.php'; ?>
        </div>
    </div>

    <!-- Bouton retour vers tous les articles (mobile-friendly) -->
    <div class="text-center my-8 pt-6">
        <a href="articles" 
           class="inline-block px-8 py-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Voir tous les articles
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>