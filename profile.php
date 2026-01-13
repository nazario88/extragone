<?php
/**
 * Page de profil utilisateur
 * URL : www.extrag.one/membre/username
 */

include 'includes/config.php';
require_once 'includes/auth.php'; // besoin de le charge avant l'header.php

// Récupérer le username depuis l'URL
$username = $_GET['username'] ?? '';

if (empty($username)) {
    header('Location: https://www.extrag.one');
    exit;
}

// Récupérer l'utilisateur par username
$stmt = $pdo->prepare('SELECT * FROM extra_proj_users WHERE username = ? AND is_active = 1');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    errorPage('Utilisateur non trouvé.');
}

// Inclure les fonctions profil
include 'includes/profil/functions.php';

// Récupérer les stats
$stats = getUserStats($user['id']);

// Récupérer les projets
$current_user = getCurrentUser();
$include_drafts = ($current_user && $current_user['id'] == $user['id']);
$user_projects = getUserProjects($user['id'], $include_drafts);

/* SEO
——————————————————————————————————————————————————*/
$title = htmlspecialchars($user['display_name']) . " — Profil eXtragone";
$description = "Profil de " . htmlspecialchars($user['display_name']) . " sur eXtragone.";
$url_canon = 'https://www.extrag.one/membre/' . htmlspecialchars($username);
$image_seo = $user['avatar'] ?: 'https://www.extrag.one/uploads/avatars/' . urlencode($user['display_name']);

include 'includes/header.php';
?>

<div class="w-full max-w-6xl mx-auto px-5 py-8">
    
    <!-- Header du profil -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 shadow-lg mb-8">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            
            <!-- Avatar -->
            <img src="<?= $user['avatar'] ?: 'https://www.extrag.one/uploads/avatars/' . urlencode($user['display_name']) ?>" 
                 alt="<?= htmlspecialchars($user['display_name']) ?>"
                 class="w-32 h-32 object-cover rounded-full ring-4 ring-slate-300/70 dark:ring-white/10 shadow-lg">
            
            <!-- Informations -->
            <div class="flex-1 text-center md:text-left">
                <div class="flex flex-col md:flex-row md:items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold">
                        <?= htmlspecialchars($user['display_name']) ?>
                    </h1>
                    
                    <!-- Badge rôle -->
                    <?php if ($user['role'] === 'reviewer' || $user['role'] === 'admin'): ?>
                    <span class="inline-block px-3 py-1 bg-purple-500 text-white text-sm font-medium rounded-full">
                        <i class="fa-solid fa-star mr-1"></i>
                        <?= $user['role'] === 'admin' ? 'Admin' : 'Reviewer' ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <p class="text-gray-600 dark:text-gray-400 mb-1">@<?= htmlspecialchars($user['username']) ?></p>
                
                <?php if ($user['bio']): ?>
                <p class="text-gray-700 dark:text-gray-300 mb-4 max-w-2xl">
                    <?= nl2br(htmlspecialchars($user['bio'])) ?>
                </p>
                <?php endif; ?>
                
                <?php if ($user['external_link']): ?>
                <a href="<?= htmlspecialchars($user['external_link']) ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center text-blue-500 hover:underline mb-4">
                    <i class="fa-solid fa-link mr-2"></i>
                    <?= htmlspecialchars($user['external_link']) ?>
                </a>
                <?php endif; ?>
                
                <!-- Stats -->
                <div class="flex flex-wrap gap-6 mt-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-500"><?= $stats['published_count'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Projets publiés</div>
                    </div>
                    
                    <?php if ($stats['review_count'] > 0): ?>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-500"><?= $stats['review_count'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Reviews rédigées</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-500"><?= $stats['comment_count'] ?></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Commentaires</div>
                    </div>
                </div>
                
                <!-- Bouton éditer profil si c'est son propre profil -->
                <?php if ($current_user && $current_user['id'] == $user['id']): ?>
                <div class="mt-4">
                    <a href="reglages" class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                        <i class="fa-solid fa-gear mr-2"></i>
                        Modifier mon profil
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Section des projets -->
    <div>
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fa-solid fa-folder-open text-blue-500 mr-3"></i>
            Projets
            <?php if ($include_drafts): ?>
                <span class="ml-2 text-sm text-gray-500">(incluant les brouillons)</span>
            <?php endif; ?>
        </h2>
        
        <?php if (empty($user_projects)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-12 border border-slate-200 dark:border-slate-700 text-center">
                <i class="fa-solid fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 dark:text-gray-300">
                    <?php if ($current_user && $current_user['id'] == $user['id']): ?>
                        Tu n'as pas encore soumis de projet 🙄.
                        <br>
                        <a href="https://projets.extrag.one/soumettre" class="border-b-2 border-blue-500 hover:border-dotted hover:text-blue-500 transition-colors duration-300 inline-block mt-2">
                            Soumettre ton premier projet
                        </a>
                    <?php else: ?>
                        Aucun projet publié pour le moment.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($user_projects as $project): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fadeIn">
                    
                    <!-- Badge statut si brouillon/en review -->
                    <?php if ($project['status'] !== 'published'): ?>
                    <div class="px-4 py-2 bg-yellow-100 dark:bg-yellow-900/30 border-b border-yellow-200 dark:border-yellow-800">
                        <span class="text-xs font-medium text-yellow-800 dark:text-yellow-200">
                            <i class="fa-solid fa-clock mr-1"></i>
                            <?php
                            switch ($project['status']) {
                                case 'draft': echo 'En attente de review'; break;
                                case 'in_review': echo 'En cours de review'; break;
                                case 'rejected': echo 'Rejeté'; break;
                            }
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Image de couverture -->
                    <?php if ($project['status'] === 'published'): ?>
                    <a href="https://projets.extrag.one/projet/<?= htmlspecialchars($project['slug']) ?>" class="block">
                    <?php endif; ?>
                        <?php if ($project['cover_image_path']): ?>
                            <img src="https://projets.extrag.one<?= htmlspecialchars($project['cover_image_path']) ?>" 
                                 alt="<?= htmlspecialchars($project['title']) ?>"
                                 class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                                <i class="fa-solid fa-image text-white text-4xl opacity-50"></i>
                            </div>
                        <?php endif; ?>
                    <?php if ($project['status'] === 'published'): ?>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Contenu -->
                    <div class="p-5">
                        <?php if ($project['status'] === 'published'): ?>
                        <a href="https://projets.extrag.one/projet/<?= htmlspecialchars($project['slug']) ?>">
                        <?php endif; ?>
                            <h3 class="font-bold text-lg mb-2 <?= $project['status'] === 'published' ? 'hover:text-blue-500 transition-colors' : '' ?> line-clamp-2">
                                <?= htmlspecialchars($project['title']) ?>
                            </h3>
                        <?php if ($project['status'] === 'published'): ?>
                        </a>
                        <?php endif; ?>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-3">
                            <?= htmlspecialchars($project['short_description']) ?>
                        </p>
                        
                        <?php if ($project['status'] === 'published'): ?>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fa-solid fa-eye mr-1"></i>
                                <?= $project['view_count'] ?> vues
                            </span>
                            <span>
                                <?= date('d/m/Y', strtotime($project['published_at'])) ?>
                            </span>
                        </div>
                        <?php else: ?>
                        <div class="text-xs text-gray-500">
                            Soumis le <?= date('d/m/Y', strtotime($project['created_at'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.4s ease-out;
}
</style>

<?php include 'includes/footer.php'; ?>