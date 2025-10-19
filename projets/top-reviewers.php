<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include '../includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

$title = "Top Reviewers — Projets eXtragone";
$description = "Découvre les reviewers les plus actifs de la communauté eXtragone.";
$url_canon = 'https://projets.extrag.one/top-reviewers';

// Récupérer les top reviewers
$top_reviewers = getTopReviewers(50);

include 'includes/header.php';
?>

<div class="w-full max-w-5xl mx-auto px-5 py-8">
    
    <!-- Header -->
    <div class="text-center mb-12">
        <div class="inline-block p-4 bg-purple-100 dark:bg-purple-900/30 rounded-full mb-4">
            <i class="fa-solid fa-trophy text-4xl text-purple-500"></i>
        </div>
        <h1 class="px-4 m-2 mx-auto text-xl md:text-4xl text-center font-bold tracking-tight dark:text-slate-500">Top Reviewers</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            Ils analysent, évaluent et aident la communauté à découvrir les meilleurs projets
        </p>
    </div>

    <!-- Stats globales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <?php
        $total_reviewers = count($top_reviewers);
        $total_reviews = array_sum(array_column($top_reviewers, 'review_count'));
        $avg_reviews = $total_reviewers > 0 ? round($total_reviews / $total_reviewers, 1) : 0;
        ?>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold text-purple-500 mb-2"><?= $total_reviewers ?></div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Reviewers actifs</div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold text-blue-500 mb-2"><?= $total_reviews ?></div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Reviews publiées</div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold text-green-500 mb-2"><?= $avg_reviews ?></div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Moyenne / reviewer</div>
        </div>
    </div>

    <!-- Podium (Top 3) -->
    <?php if (count($top_reviewers) >= 3): ?>
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-center mb-8">🏆 Podium</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
            <!-- 2ème place -->
            <div class="order-2 md:order-1">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border-2 border-slate-300 dark:border-slate-600 text-center">
                    <div class="relative inline-block mb-4">
                        <img src="<?= $top_reviewers[1]['avatar'] ?: '/images/default-avatar.png' ?>" 
                             alt="<?= htmlspecialchars($top_reviewers[1]['display_name']) ?>"
                             class="w-24 h-24 rounded-full border-4 border-slate-300">
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-slate-300 text-slate-700 rounded-full flex items-center justify-center font-bold text-lg">
                            2
                        </div>
                    </div>
                    <a href="/membre/<?= htmlspecialchars($top_reviewers[1]['username']) ?>" 
                       class="font-bold text-lg hover:text-blue-500 transition-colors block mb-1">
                        <?= htmlspecialchars($top_reviewers[1]['display_name']) ?>
                    </a>
                    <p class="text-sm text-gray-500 mb-3">@<?= htmlspecialchars($top_reviewers[1]['username']) ?></p>
                    <div class="inline-block px-4 py-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                        <span class="font-bold text-xl text-slate-600 dark:text-slate-300">
                            <?= $top_reviewers[1]['review_count'] ?>
                        </span>
                        <span class="text-xs text-gray-500 ml-1">reviews</span>
                    </div>
                </div>
            </div>

            <!-- 1ère place -->
            <div class="order-1 md:order-2">
                <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl p-6 border-4 border-yellow-300 text-center transform md:-translate-y-4">
                    <div class="relative inline-block mb-4">
                        <img src="<?= $top_reviewers[0]['avatar'] ?: '/images/default-avatar.png' ?>" 
                             alt="<?= htmlspecialchars($top_reviewers[0]['display_name']) ?>"
                             class="w-32 h-32 rounded-full border-4 border-white">
                        <div class="absolute -top-2 -right-2 w-12 h-12 bg-white text-yellow-600 rounded-full flex items-center justify-center font-bold text-xl shadow-lg">
                            👑
                        </div>
                    </div>
                    <a href="/membre/<?= htmlspecialchars($top_reviewers[0]['username']) ?>" 
                       class="font-bold text-xl text-white hover:text-yellow-100 transition-colors block mb-1">
                        <?= htmlspecialchars($top_reviewers[0]['display_name']) ?>
                    </a>
                    <p class="text-sm text-yellow-100 mb-3">@<?= htmlspecialchars($top_reviewers[0]['username']) ?></p>
                    <div class="inline-block px-4 py-2 bg-white rounded-lg">
                        <span class="font-bold text-2xl text-yellow-600">
                            <?= $top_reviewers[0]['review_count'] ?>
                        </span>
                        <span class="text-xs text-yellow-700 ml-1">reviews</span>
                    </div>
                </div>
            </div>

            <!-- 3ème place -->
            <div class="order-3">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border-2 border-orange-300 dark:border-orange-600 text-center">
                    <div class="relative inline-block mb-4">
                        <img src="<?= $top_reviewers[2]['avatar'] ?: '/images/default-avatar.png' ?>" 
                             alt="<?= htmlspecialchars($top_reviewers[2]['display_name']) ?>"
                             class="w-24 h-24 rounded-full border-4 border-orange-300">
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-orange-400 text-white rounded-full flex items-center justify-center font-bold text-lg">
                            3
                        </div>
                    </div>
                    <a href="/membre/<?= htmlspecialchars($top_reviewers[2]['username']) ?>" 
                       class="font-bold text-lg hover:text-blue-500 transition-colors block mb-1">
                        <?= htmlspecialchars($top_reviewers[2]['display_name']) ?>
                    </a>
                    <p class="text-sm text-gray-500 mb-3">@<?= htmlspecialchars($top_reviewers[2]['username']) ?></p>
                    <div class="inline-block px-4 py-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                        <span class="font-bold text-xl text-orange-600 dark:text-orange-400">
                            <?= $top_reviewers[2]['review_count'] ?>
                        </span>
                        <span class="text-xs text-orange-500 ml-1">reviews</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Classement complet -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-2xl font-bold flex items-center">
                <i class="fa-solid fa-list text-blue-500 mr-3"></i>
                Classement complet
            </h2>
        </div>
        
        <div class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach ($top_reviewers as $index => $reviewer): ?>
            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <div class="flex items-center gap-4">
                    <!-- Rang -->
                    <div class="w-12 text-center">
                        <?php if ($index < 3): ?>
                            <span class="text-2xl">
                                <?php 
                                echo $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉');
                                ?>
                            </span>
                        <?php else: ?>
                            <span class="text-xl font-bold text-gray-400">
                                #<?= $index + 1 ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Avatar & Info -->
                    <img src="<?= $reviewer['avatar'] ?: '/images/default-avatar.png' ?>" 
                         alt="<?= htmlspecialchars($reviewer['display_name']) ?>"
                         class="w-12 h-12 rounded-full">
                    
                    <div class="flex-1">
                        <a href="/membre/<?= htmlspecialchars($reviewer['username']) ?>" 
                           class="font-bold hover:text-blue-500 transition-colors">
                            <?= htmlspecialchars($reviewer['display_name']) ?>
                        </a>
                        <p class="text-sm text-gray-500">@<?= htmlspecialchars($reviewer['username']) ?></p>
                    </div>
                    
                    <!-- Badge reviewer -->
                    <div class="hidden md:block">
                        <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-sm font-medium rounded-full">
                            <i class="fa-solid fa-star mr-1"></i>
                            <?= $reviewer['role'] === 'admin' ? 'Admin' : 'Reviewer' ?>
                        </span>
                    </div>
                    
                    <!-- Nombre de reviews -->
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-500">
                            <?= $reviewer['review_count'] ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            review<?= $reviewer['review_count'] > 1 ? 's' : '' ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($top_reviewers)): ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fa-solid fa-inbox text-4xl mb-4"></i>
                <p>Aucun reviewer pour le moment.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CTA -->
    <div class="mt-12 bg-white dark:bg-slate-800 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 overflow-hidden p-8 text-center">
        <h2 class="text-2xl font-bold mb-3">Tu veux rejoindre l'équipe ?</h2>
        <p class="text-lg mb-6 opacity-90">
            Deviens reviewer et aide la communauté à découvrir les meilleurs projets
        </p>
        <a href="devenir-reviewer" class="inline-block px-8 py-4 bg-primary text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all hover:scale-105">
            <i class="fa-solid fa-star mr-2"></i>
            Devenir reviewer
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>