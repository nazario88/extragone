<?php
include 'includes/config.php';

$title = "Liste des articles — eXtragone";
$description = "Découvrez tous nos articles sur les meilleurs outils à utiliser ! Conseils, découvertes, alternatives, etc. En privilégiant les outils français !";
include 'includes/header.php';

// Récupération de tous les articles triés du plus récent au plus ancien
$stmt = $pdo->query('
    SELECT a.* 
    FROM extra_articles a
    WHERE image IS NOT NULL 
    ORDER BY a.created_at DESC
');

$articles = $stmt->fetchAll();
?>

<div class="w-full lg:w-4/5 xl:w-3/4 2xl:w-2/3 mx-auto">
    <!-- Tous les articles -->
    <div class="w-full px-5 py-5">
        <h2 class="font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">tous nos articles (<?= count($articles) ?>)</h2>
        
        <?php if ($articles): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                <!-- Articles -->
                <?php foreach ($articles as $article): ?>
                    <article class="group bg-white dark:bg-slate-800 rounded-2xl shadow-lg hover:shadow-2xl border border-gray-100 dark:border-slate-700 transition-all duration-300 overflow-hidden">
                    <a href="article/<?=htmlspecialchars($article['slug'], ENT_QUOTES, 'UTF-8')?>" class="block">
                        <div class="relative overflow-hidden">
                            <img class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105 rounded-xl" src="<?=htmlspecialchars($article['image'], ENT_QUOTES, 'UTF-8')?>" alt="<?=htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8')?>">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>
                    </a>
                    <div class="p-4 flex flex-col flex-grow">
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
        <?php else: ?>
            <p class="text-center m-5">
                Aucun article trouvé ☹️.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>