<?php

include '../includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

$title = "Classement des reviewers — Projets eXtragone";
$description = "Classement des reviewers les plus actifs sur eXtragone, la plateforme qui met en avant les projets et avis de la communauté tech française.";
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
            Ils analysent, évaluent et aident la communauté à découvrir les meilleurs projets.
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

    <!-- Podium (Top 3) - Version épurée -->
<?php if (count($top_reviewers) >= 3): ?>
<div class="mb-12">
    <h2 class="text-2xl font-bold text-center mb-8 flex items-center justify-center gap-3">
        Podium
    </h2>
    
    <div class="relative flex items-end justify-center gap-4 max-w-4xl mx-auto">
        
        <!-- 2ème place -->
        <div class="flex-1 max-w-xs animate-fadeIn" style="animation-delay: 0.2s">
            <!-- Piédestal -->
            <div class="bg-gradient-to-b from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-800 rounded-t-2xl p-6 text-center border-t-4 border-slate-400 relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-8 h-8 bg-slate-400 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                    2
                </div>
                
                <div class="relative inline-block mb-4 mt-2">
                    <img src="https://www.extrag.one<?= $top_reviewers[1]['avatar'] ?: '/uploads/avatars/'.$top_reviewers[1]['display_name'] ?>" 
                         alt="<?= htmlspecialchars($top_reviewers[1]['display_name']) ?>"
                         class="w-20 h-20 object-cover rounded-full border-4 border-white dark:border-slate-600 shadow-lg">
                </div>

                <a href="https://www.extrag.one/membre/<?= htmlspecialchars($top_reviewers[1]['username']) ?>" 
                   class="font-bold text-lg hover:text-blue-500 transition-colors block mb-1">
                    <?= htmlspecialchars($top_reviewers[1]['display_name']) ?>
                </a>
                <p class="text-sm text-gray-500 mb-3">@<?= htmlspecialchars($top_reviewers[1]['username']) ?></p>
                
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-600 rounded-xl shadow-inner">
                    <span class="font-bold text-2xl">
                        <?= $top_reviewers[1]['review_count'] ?>
                    </span>
                    <span class="text-xs">reviews</span>
                </div>
            </div>
            <!-- Base du piédestal -->
            <div class="h-20 bg-slate-300 dark:bg-slate-700 rounded-b-xl shadow-lg"></div>
        </div>

        <!-- 1ère place -->
        <div class="flex-1 max-w-xs animate-fadeIn">
            <!-- Piédestal -->
            <div class="bg-gradient-to-b from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-900/50 rounded-t-2xl p-6 text-center border-t-4 border-purple-500 relative shadow-xl">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                    <i class="fa-solid fa-crown"></i>
                </div>
                
                <div class="relative inline-block mb-4 mt-2">
                    <img src="https://www.extrag.one<?= $top_reviewers[0]['avatar'] ?: '/uploads/avatars/'.$top_reviewers[0]['display_name'] ?>" 
                         alt="<?= htmlspecialchars($top_reviewers[0]['display_name']) ?>"
                         class="w-28 h-28 object-cover rounded-full border-4 border-purple-500 shadow-2xl ring-4 ring-purple-200 dark:ring-purple-800">
                </div>

                <a href="https://www.extrag.one/membre/<?= htmlspecialchars($top_reviewers[0]['username']) ?>" 
                   class="font-bold text-xl text-purple-900 dark:text-purple-100 hover:text-purple-600 dark:hover:text-purple-300 transition-colors block mb-1">
                    <?= htmlspecialchars($top_reviewers[0]['display_name']) ?>
                </a>
                <p class="text-sm text-purple-700 dark:text-purple-300 mb-3">@<?= htmlspecialchars($top_reviewers[0]['username']) ?></p>
                
                <div class="inline-flex items-center gap-2 px-5 py-2 bg-white dark:bg-purple-800 rounded-xl shadow-lg">
                    <span class="font-bold text-3xl">
                        <?= $top_reviewers[0]['review_count'] ?>
                    </span>
                    <span class="text-xs">reviews</span>
                </div>
            </div>
            <!-- Base du piédestal -->
            <div class="h-32 bg-purple-300 dark:bg-purple-800/50 rounded-b-xl shadow-lg"></div>
        </div>

        <!-- 3ème place -->
        <div class="flex-1 max-w-xs animate-fadeIn" style="animation-delay: 0.4s">
            <!-- Piédestal -->
            <div class="bg-gradient-to-b from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-900/30 rounded-t-2xl p-6 text-center border-t-4 border-amber-500 relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                    3
                </div>
                
                <div class="relative inline-block mb-4 mt-2">
                    <img src="https://www.extrag.one<?= $top_reviewers[2]['avatar'] ?: '/uploads/avatars/'.$top_reviewers[2]['display_name'] ?>" 
                         alt="<?= htmlspecialchars($top_reviewers[2]['display_name']) ?>"
                         class="w-20 h-20 object-cover rounded-full border-4 border-white dark:border-slate-600 shadow-lg">
                </div>

                <a href="https://www.extrag.one/membre/<?= htmlspecialchars($top_reviewers[2]['username']) ?>" 
                   class="font-bold text-lg hover:text-blue-500 transition-colors block mb-1">
                    <?= htmlspecialchars($top_reviewers[2]['display_name']) ?>
                </a>
                <p class="text-sm text-gray-500 mb-3">@<?= htmlspecialchars($top_reviewers[2]['username']) ?></p>
                
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-600 rounded-xl shadow-inner">
                    <span class="font-bold text-2xl">
                        <?= $top_reviewers[2]['review_count'] ?>
                    </span>
                    <span class="text-xs">reviews</span>
                </div>
            </div>
            <!-- Base du piédestal -->
            <div class="h-12 bg-amber-200 dark:bg-amber-800/30 rounded-b-xl shadow-lg"></div>
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- Classement complet -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
            <h2 class="text-2xl font-bold flex items-center">
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
                    <img src="https://www.extrag.one<?= $reviewer['avatar'] ?: '/uploads/avatars/'.$reviewer['display_name'] ?>" 
                         alt="<?= htmlspecialchars($reviewer['display_name']) ?>"
                         class="w-12 h-12 object-cover rounded-full">
                    
                    <div class="flex-1">
                        <a href="https://www.extrag.one/membre/<?= htmlspecialchars($reviewer['username']) ?>" 
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