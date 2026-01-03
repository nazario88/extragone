<?php

include 'includes/config.php';

$title = "Trouver des outils et des alternatives françaises !";
$description = "Extragone recense les meilleures alternatives françaises aux outils web. Toi aussi, soutiens la French tech' !";
include 'includes/header.php';

$stmt = $pdo->query('
    SELECT a.* 
    FROM extra_articles a
    WHERE image IS NOT NULL 
    ORDER BY a.created_at DESC
    LIMIT 4
');

$articles = $stmt->fetchAll();

?>

<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "url": "https://www.extrag.one/",
    "potentialAction": {
    "@type": "SearchAction",
    "target": {
        "@type": "EntryPoint",
        "urlTemplate": "https://www.extrag.one/outils?q={search_term_string}"
    },
    "query-input": "required name=search_term_string"
    }
}
</script>

<div class="w-full lg:w-1/2 mx-auto">
    <!-- Accroche -->
    <h1 class="p-4 m-4 mx-auto text-xl md:text-4xl  text-center font-bold tracking-tight dark:text-slate-500">
        <img src="assets/img/logo.webp" class="w-1/3 mx-auto" alt="Logo d'Extragone">
        Trouve <span class="dark:text-white font-semibold">l'équivalent français</span> d'un outil !
    </h1>

  <!-- Barre de recherche -->
<div class="w-full mx-auto mb-8 sm:px-6 lg:px-8 relative">
    <form action="outils.php">
        <label
            class="mx-auto mt-8 relative bg-white dark:bg-slate-800 flex flex-col md:flex-row items-center justify-center border dark:border-slate-500 py-2 px-2 rounded-2xl gap-2 shadow-2xl focus-within:border-gray-300 dark:focus-within:border-slate-500"
            for="search-bar">

            <input id="search-bar" placeholder="Exemple: ChatGPT, Slack, Notion, &hellip;" name="q"
                class="px-6 py-2 w-full rounded-md flex-1 outline-none bg-white dark:bg-slate-800" autofocus oninput="handleSearch(this.value)" required="">
            <button type="submit"
                class="w-full md:w-auto px-6 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white fill-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all">
                <div class="flex items-center transition-all opacity-1">
                    <span class="text-sm font-semibold whitespace-nowrap truncate mx-auto">
                        Chercher
                    </span>
                </div>
            </button>
        </label>
    </form>
    <!-- Résultats dynamiques -->
    <div id="search-results" class="hidden z-50 w-full max-w-3xl mx-auto bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-600 rounded-2xl shadow-lg absolute">
      <!-- Les résultats JS viendront ici -->
    </div>
</div>


    <!-- Separateur -->
    <hr class="my-8 h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

    <?php include 'includes/index/tools.php'; ?>
    
    <!-- Explications -->
    <div class="w-full mb-8 px-5 py-5 bg-slate-100 rounded-xl shadow border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <h2 class="font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">extragone, c'est quoi ?</h2>
        <div class="flex gap-4 m-2">
            <div class="hidden text-center md:flex md:items-center w-[100px]"><i class="my-auto fa-solid fa-question text-primary text-xl md:text-4xl"></i></div>
            <p class="text-sm text-justify">
                eXtragone a pour objectif de recenser les outils web les plus utilisés, de les classer par catégorie et de les rendre facilement accessibles. Mais surtout, son moteur de recherche permet de <span class="font-bold">trouver des alternatives françaises</span>, pour mettre en valeur nos solutions locales et favoriser l’usage de produits made in France.
            </p>
        </div>
        <p class="mt-3">
            <a href="outils" role="button" class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                <i class="fa-solid fa-arrow-right"></i> Voir les outils
            </a>
        </p>
    </div>

    <!-- Separateur -->
    <hr class="my-8 h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

    <!-- Derniers articles -->
    <div class="w-full mb-8 px-5 py-5 bg-slate-100 rounded-xl shadow border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <h2 class="font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">derniers articles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 rounded-xl relative z-20 p-8 sm:p-4">
            <!-- Articles -->
            <?php foreach ($articles as $article): ?>
            <article class="p-2 flex flex-col justify-between">
                <a href="article/<?=htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8')?>" class="group">
                    <img class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105 rounded-xl" src="<?=htmlspecialchars($article['image'], ENT_QUOTES, 'UTF-8')?>" alt="<?=htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8')?>">
                </a>
                <div class="p-2 flex flex-col flex-grow">
                    <h3 class="text-lg font-semibold mb-2"><?=htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8')?></h3>
                    <p class="flex-grow text-sm text-justify"><?=htmlspecialchars($article['description'], ENT_QUOTES, 'UTF-8')?></p>
                    <div class="mt-4">
                        <a href="article/<?=htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8')?>" role="button" class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                            <i class="fa-solid fa-arrow-right"></i> Consulter l'article
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bouton voir tous les articles -->
    <div class="text-center mb-6">
        <a href="articles" class="w-full md:w-auto px-6 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white fill-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all hover:border-blue-400 dark:hover:border-blue-950 transition-all duration-200 group">
            <span>Voir tous les articles</span>
            <i class="fa-solid fa-arrow-right ml-2 transition-transform duration-200 group-hover:translate-x-1"></i>
        </a>
    </div>
</div>

<?php
include 'includes/footer.php';
?>