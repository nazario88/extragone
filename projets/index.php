<?php

include '../includes/config.php';
include '../includes/auth.php';
include 'includes/functions.php';

$title = "Projets de la communauté — eXtragone";
$description = "Découvre les projets créatifs de la communauté eXtragone : outils, apps, sites web... avec revues détaillées et commentaires.";
$url_canon = 'https://projets.extrag.one';

// Récupérer les derniers projets
$latest_projects = getLatestProjects(12);

// Récupérer les derniers commentaires
$latest_comments = getLatestComments(5);

include 'includes/header.php';
?>

<div class="w-full max-w-7xl mx-auto px-5 py-8">
    
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="p-4 m-4 mx-auto text-xl md:text-4xl  text-center font-bold tracking-tight dark:text-slate-500">
            Les <span class="dark:text-white font-semibold">projets</span> de la communauté !
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto mb-6">
            Découvre les créations de la communauté eXtragone : applications, sites web, outils... 
            Chaque projet bénéficie d'une revue détaillée par notre équipe.
        </p>
        
        <div class="flex flex-wrap justify-center gap-4">
            <a href="soumettre" class="inline-block px-8 py-4 bg-blue-500 hover:bg-blue-700 text-white font-semibold text-lg rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-plus mr-2"></i>
                Soumettre ton projet
            </a>
            <a href="devenir-reviewer" class="inline-block px-8 py-4 bg-orange-500 hover:bg-orange-700 text-white font-semibold text-lg rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-star mr-2"></i>
                Devenir reviewer
            </a>
        </div>
    </div>

    <!-- Stats rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <?php
        $stmt = $pdo->query('SELECT COUNT(*) FROM extra_proj_projects WHERE status = "published"');
        $total_projects = $stmt->fetchColumn();
        
        $stmt = $pdo->query('SELECT COUNT(*) FROM extra_proj_users WHERE role IN ("reviewer", "admin")');
        $total_reviewers = $stmt->fetchColumn();
        
        $stmt = $pdo->query('SELECT COUNT(*) FROM extra_proj_comments');
        $total_comments = $stmt->fetchColumn();
        ?>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold text-blue-500 mb-2"><?= $total_projects ?></div>
            <div class="text-sm text-gray-600 dark:text-gray-300">Projets publiés</div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold text-orange-500 mb-2"><?= $total_reviewers ?></div>
            <div class="text-sm text-gray-600 dark:text-gray-300">Reviewers actifs</div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold text-green-500 mb-2"><?= $total_comments ?></div>
            <div class="text-sm text-gray-600 dark:text-gray-300">Commentaires</div>
        </div>
    </div>

    <!-- Grille des projets -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold mb-2 flex items-center">
            <i class="fa-solid fa-folder-open text-blue-500 mr-3"></i>
            Derniers projets publiés
        </h2>
        
        <?php if (empty($latest_projects)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-12 border border-slate-200 dark:border-slate-700 text-center">
                <i class="fa-solid fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 dark:text-gray-300 mb-4">Aucun projet publié pour le moment.</p>
                <a href="soumettre" class="inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all">
                    Sois le premier à soumettre !
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($latest_projects as $project): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fadeIn">
                    
                    <!-- Image de couverture -->
                    <a href="projet/<?= htmlspecialchars($project['slug']) ?>" class="block">
                        <?php if ($project['cover_image_path']): ?>
                            <img src="<?= $base.htmlspecialchars($project['cover_image_path']) ?>" 
                                 alt="<?= htmlspecialchars($project['title']) ?>"
                                 class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                                <i class="fa-solid fa-image text-white text-4xl opacity-50"></i>
                            </div>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Contenu -->
                    <div class="p-5">
                        <!-- Titre -->
                        <a href="projet/<?= htmlspecialchars($project['slug']) ?>">
                            <h3 class="font-bold text-lg mb-2 hover:text-blue-500 transition-colors line-clamp-2">
                                <?= htmlspecialchars($project['title']) ?>
                            </h3>
                        </a>
                        
                        <!-- Description courte -->
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-3">
                            <?= htmlspecialchars($project['short_description']) ?>
                        </p>
                        
                        <!-- Métadonnées -->
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                            <span class="flex items-center">
                                <i class="fa-solid fa-eye mr-1"></i>
                                <?= $project['view_count'] ?> vues
                            </span>
                            <span class="flex items-center">
                                <i class="fa-solid fa-comment mr-1"></i>
                                <?= countProjectComments($project['id']) ?>
                            </span>
                        </div>
                        
                        <!-- Auteur -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                            <a href="https://www.extrag.one/membre/<?= htmlspecialchars($project['username']) ?>" class="flex items-center gap-2 hover:text-blue-500 transition-colors">
                                <img src="https://www.extrag.one<?= $project['avatar'] ?: '/uploads/avatars/'.urlencode($project['display_name']) ?>" 
                                     class="w-6 h-6 object-cover rounded-full"
                                     alt="<?= htmlspecialchars($project['display_name']) ?>">
                                <span class="text-sm font-medium">
                                    <?= htmlspecialchars($project['display_name']) ?>
                                </span>
                            </a>
                            <span class="text-xs text-gray-500">
                                <?= timeAgo($project['published_at']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section commentaires récents -->
    <?php if (!empty($latest_comments)): ?>
    <div class="mb-12">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fa-solid fa-comments text-green-500 mr-3"></i>
            Derniers commentaires
        </h2>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach ($latest_comments as $comment): ?>
            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <div class="flex items-start gap-3">
                    <img src="https://www.extrag.one<?= $comment['avatar'] ?: '/uploads/avatars/'.urlencode($comment['display_name']) ?>" 
                         class="w-10 h-10 object-cover rounded-full"
                         alt="<?= htmlspecialchars($comment['display_name']) ?>">
                    
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <a href="https://www.extrag.onemembre/<?= htmlspecialchars($comment['username']) ?>" class="font-medium hover:text-blue-500 transition-colors">
                                <?= htmlspecialchars($comment['display_name']) ?>
                            </a>
                            <span class="text-xs text-gray-500">
                                a commenté sur
                            </span>
                            <a href="projet/<?= htmlspecialchars($comment['project_slug']) ?>" class="text-sm text-blue-500 hover:underline">
                                <?= htmlspecialchars($comment['project_title']) ?>
                            </a>
                        </div>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                            <?= htmlspecialchars($comment['content']) ?>
                        </p>
                        
                        <span class="text-xs text-gray-500 mt-1 inline-block">
                            <?= timeAgo($comment['created_at']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- CTA final -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 overflow-hidden p-8 text-center">
        <h2 class="text-3xl font-bold mb-4">Prêt à partager ton projet ?</h2>
        <p class="text-lg mb-6 opacity-90">
            Soumets ton projet et obtiens une revue détaillée de notre équipe
        </p>
        <a href="soumettre" class="inline-block px-8 py-4 bg-primary text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all hover:scale-105">
            <i class="fa-solid fa-rocket mr-2"></i>
            Soumettre mon projet
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>