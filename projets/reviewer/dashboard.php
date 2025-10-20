<?php
include '../../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

// Vérifier que l'utilisateur est reviewer
requireRole('reviewer');

$user = getCurrentUser();

// Récupérer les projets en attente (draft)
$stmt = $pdo->prepare('
    SELECT p.*, u.username, u.display_name, u.avatar,
           (SELECT filepath FROM extra_proj_images WHERE project_id = p.id AND is_cover = 1 LIMIT 1) as cover_image_path
    FROM extra_proj_projects p
    JOIN extra_proj_users u ON p.user_id = u.id
    WHERE p.status = "draft"
    ORDER BY p.created_at ASC
');
$stmt->execute();
$pending_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les projets que je suis en train de reviewer
$stmt = $pdo->prepare('
    SELECT p.*, u.username, u.display_name, u.avatar,
           (SELECT filepath FROM extra_proj_images WHERE project_id = p.id AND is_cover = 1 LIMIT 1) as cover_image_path
    FROM extra_proj_projects p
    JOIN extra_proj_users u ON p.user_id = u.id
    WHERE p.status = "in_review" AND p.reviewer_id = ?
    ORDER BY p.updated_at DESC
');
$stmt->execute([$user['id']]);
$my_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer mes reviews publiées (historique)
$stmt = $pdo->prepare('
    SELECT p.*, u.username, u.display_name,
           (SELECT filepath FROM extra_proj_images WHERE project_id = p.id AND is_cover = 1 LIMIT 1) as cover_image_path
    FROM extra_proj_projects p
    JOIN extra_proj_users u ON p.user_id = u.id
    WHERE p.status = "published" AND p.reviewer_id = ?
    ORDER BY p.published_at DESC
    LIMIT 10
');
$stmt->execute([$user['id']]);
$published_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Dashboard Reviewer — Projets eXtragone";
$description = "Gère les projets à reviewer.";
$url_canon = 'https://projets.extrag.one/reviewer/dashboard';
$noindex = TRUE;

include '../includes/header.php';
?>

<div class="w-full max-w-7xl mx-auto px-5 py-8">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2 flex items-center">
            <i class="fa-solid fa-clipboard-check text-purple-500 mr-3"></i>
            Dashboard Reviewer
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
            Bienvenue <?= htmlspecialchars($user['display_name']) ?> ! Voici les projets en attente de review.
        </p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-yellow-500"><?= count($pending_projects) ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">En attente</div>
                </div>
                <i class="fa-solid fa-clock text-3xl text-yellow-500 opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-blue-500"><?= count($my_reviews) ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">En cours</div>
                </div>
                <i class="fa-solid fa-pencil text-3xl text-blue-500 opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold text-green-500"><?= count($published_reviews) ?></div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Publiées (total)</div>
                </div>
                <i class="fa-solid fa-check-circle text-3xl text-green-500 opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Alerte candidatures en attente (Admin uniquement) -->
    <?php if (isAdmin()): ?>
        <?php
        $stmt = $pdo->query('SELECT COUNT(*) FROM extra_proj_reviewer_requests WHERE status = "pending"');
        $pending_candidates = (int)$stmt->fetchColumn();
        if ($pending_candidates > 0):
        ?>
        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-2xl p-6 border-2 border-orange-200 dark:border-orange-800 mb-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-user-plus text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-orange-900 dark:text-orange-100 mb-2">
                        <?= $pending_candidates ?> candidature<?= $pending_candidates > 1 ? 's' : '' ?> reviewer en attente
                    </h3>
                    <p class="text-sm text-orange-800 dark:text-orange-200 mb-4">
                        Des membres souhaitent rejoindre l'équipe des reviewers. Consulte leurs candidatures pour les accepter ou les refuser.
                    </p>
                    <a href="admin/reviewer-requests" 
                       class="inline-block px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-shield-halved mr-2"></i>
                        Gérer les candidatures
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Mes reviews en cours -->
    <?php if (!empty($my_reviews)): ?>
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fa-solid fa-pencil text-blue-500 mr-3"></i>
            Mes reviews en cours (<?= count($my_reviews) ?>)
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($my_reviews as $project): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-300 dark:border-blue-700 overflow-hidden hover:shadow-xl transition-all">
                
                <!-- Image -->
                <?php if ($project['cover_image_path']): ?>
                    <img src="<?= htmlspecialchars($project['cover_image_path']) ?>" 
                         alt="<?= htmlspecialchars($project['title']) ?>"
                         class="w-full h-40 object-cover">
                <?php else: ?>
                    <div class="w-full h-40 bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                        <i class="fa-solid fa-image text-white text-3xl opacity-50"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Contenu -->
                <div class="p-5">
                    <h3 class="font-bold text-lg mb-2 line-clamp-2">
                        <?= htmlspecialchars($project['title']) ?>
                    </h3>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-2">
                        <?= htmlspecialchars($project['short_description']) ?>
                    </p>
                    
                    <div class="flex items-center gap-2 mb-4 text-xs text-gray-500">
                        <img src="<?= $project['avatar'] ?: '/images/default-avatar.png' ?>" 
                             class="w-5 h-5 rounded-full"
                             alt="<?= htmlspecialchars($project['display_name']) ?>">
                        <span><?= htmlspecialchars($project['display_name']) ?></span>
                    </div>
                    
                    <a href="reviewer/review/<?= $project['id'] ?>" 
                       class="block w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-center rounded-lg transition-colors font-medium">
                        <i class="fa-solid fa-edit mr-2"></i>
                        Continuer la review
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Projets en attente -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fa-solid fa-clock text-yellow-500 mr-3"></i>
            Projets en attente de review (<?= count($pending_projects) ?>)
        </h2>
        
        <?php if (empty($pending_projects)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-12 border border-slate-200 dark:border-slate-700 text-center">
                <i class="fa-solid fa-check-circle text-4xl text-green-500 mb-4"></i>
                <p class="text-gray-600 dark:text-gray-300">Aucun projet en attente ! Tout est à jour 🎉</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($pending_projects as $project): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all">
                    
                    <!-- Badge "Nouveau" si moins de 24h -->
                    <?php 
                    $is_new = (time() - strtotime($project['created_at'])) < 86400;
                    if ($is_new): 
                    ?>
                    <div class="px-4 py-2 bg-red-500 text-white text-xs font-medium text-center">
                        <i class="fa-solid fa-sparkles mr-1"></i>
                        NOUVEAU
                    </div>
                    <?php endif; ?>
                    
                    <!-- Image -->
                    <?php if ($project['cover_image_path']): ?>
                        <img src="<?= htmlspecialchars($project['cover_image_path']) ?>" 
                             alt="<?= htmlspecialchars($project['title']) ?>"
                             class="w-full h-40 object-cover">
                    <?php else: ?>
                        <div class="w-full h-40 bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                            <i class="fa-solid fa-image text-white text-3xl opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Contenu -->
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-2 line-clamp-2">
                            <?= htmlspecialchars($project['title']) ?>
                        </h3>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-3">
                            <?= htmlspecialchars($project['short_description']) ?>
                        </p>
                        
                        <div class="flex items-center gap-2 mb-4 text-xs text-gray-500">
                            <img src="<?= $project['avatar'] ?: '/images/default-avatar.png' ?>" 
                                 class="w-5 h-5 rounded-full"
                                 alt="<?= htmlspecialchars($project['display_name']) ?>">
                            <span><?= htmlspecialchars($project['display_name']) ?></span>
                            <span>•</span>
                            <span><?= timeAgo($project['created_at']) ?></span>
                        </div>
                        
                        <form method="post" action="/functions/reviews/claim-review.php">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-colors font-medium">
                                <i class="fa-solid fa-hand-paper mr-2"></i>
                                Prendre en charge
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Historique des reviews publiées -->
    <?php if (!empty($published_reviews)): ?>
    <div>
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fa-solid fa-history text-green-500 mr-3"></i>
            Mes dernières reviews publiées
        </h2>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach ($published_reviews as $project): ?>
            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <div class="flex items-center gap-4">
                    <?php if ($project['cover_image_path']): ?>
                        <img src="<?= htmlspecialchars($project['cover_image_path']) ?>" 
                             alt="<?= htmlspecialchars($project['title']) ?>"
                             class="w-20 h-20 object-cover rounded-lg">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-image text-white opacity-50"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex-1">
                        <a href="projet/<?= htmlspecialchars($project['slug']) ?>" 
                           class="font-bold hover:text-blue-500 transition-colors">
                            <?= htmlspecialchars($project['title']) ?>
                        </a>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            Par <?= htmlspecialchars($project['display_name']) ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Publié le <?= date('d/m/Y', strtotime($project['published_at'])) ?>
                        </p>
                    </div>
                    
                    <a href="projet/<?= htmlspecialchars($project['slug']) ?>" 
                       class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors text-sm">
                        <i class="fa-solid fa-eye mr-1"></i>
                        Voir
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>