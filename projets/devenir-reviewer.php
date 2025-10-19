<?php
include '../includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

$user = getCurrentUser();

// Vérifier si l'utilisateur est déjà reviewer
if (isReviewer()) {
    $_SESSION['info'] = 'Tu es déjà reviewer !';
    header('Location: /reviewer/dashboard');
    exit;
}

// Vérifier si une demande est déjà en cours
$stmt = $pdo->prepare('
    SELECT * FROM extra_proj_reviewer_requests 
    WHERE user_id = ? AND status = "pending"
');
$stmt->execute([$user['id']]);
$pending_request = $stmt->fetch(PDO::FETCH_ASSOC);

$title = "Devenir reviewer — Projets eXtragone";
$description = "Rejoins l'équipe des reviewers et aide la communauté à découvrir les meilleurs projets.";
$url_canon = 'https://projets.extrag.one/devenir-reviewer';

include 'includes/header.php';
?>

<div class="w-full max-w-4xl mx-auto px-5 py-12">
    
    <div class="text-center mb-8">
        <div class="inline-block p-4 bg-purple-100 dark:bg-purple-900/30 rounded-full mb-4">
            <i class="fa-solid fa-star text-4xl text-purple-500"></i>
        </div>
        <h1 class="px-4 m-2 mx-auto text-xl md:text-4xl text-center font-bold tracking-tight dark:text-slate-500">Devenir reviewer</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            Rejoins notre équipe de reviewers et aide la communauté à découvrir les meilleurs projets
        </p>
    </div>

    <?php if ($pending_request): ?>
        <!-- Demande en attente -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-2xl p-8 border-2 border-yellow-200 dark:border-yellow-800 text-center mb-8">
            <i class="fa-solid fa-clock text-4xl text-yellow-500 mb-4"></i>
            <h2 class="text-2xl font-bold text-yellow-900 dark:text-yellow-100 mb-2">
                Demande en cours de traitement
            </h2>
            <p class="text-yellow-800 dark:text-yellow-200 mb-4">
                Tu as soumis ta candidature le <?= date('d/m/Y', strtotime($pending_request['created_at'])) ?>.
                <br>Notre équipe va l'examiner et te répondra bientôt.
            </p>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 text-left">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2"><strong>Ta motivation :</strong></p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <?= nl2br(htmlspecialchars($pending_request['motivation'])) ?>
                </p>
            </div>
        </div>
    <?php else: ?>
        <!-- Présentation du rôle -->
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-eye text-blue-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Analyser des projets</h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300">
                    Découvre en avant-première les projets de la communauté et rédige des reviews détaillées.
                </p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-users text-green-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Aider la communauté</h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300">
                    Partage ton expertise et aide les créateurs à améliorer leurs projets avec des feedbacks constructifs.
                </p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-trophy text-purple-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Visibilité</h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300">
                    Ton profil apparaîtra dans le classement des reviewers et tes reviews seront mises en avant.
                </p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-rocket text-orange-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Liberté</h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300">
                    Choisis les projets qui t'intéressent et travaille à ton rythme, sans obligation.
                </p>
            </div>
        </div>

        <!-- Critères recherchés -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800 mb-8">
            <h2 class="text-xl font-bold text-blue-900 dark:text-blue-100 mb-4">
                <i class="fa-solid fa-check-circle mr-2"></i>
                Ce que nous recherchons
            </h2>
            <ul class="space-y-2 text-blue-800 dark:text-blue-200">
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-star text-yellow-500 mt-1"></i>
                    <span>Une passion pour le web, le design ou le développement</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-star text-yellow-500 mt-1"></i>
                    <span>La capacité à rédiger des reviews constructives et bienveillantes</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-star text-yellow-500 mt-1"></i>
                    <span>Un esprit critique et objectif</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-star text-yellow-500 mt-1"></i>
                    <span>De la disponibilité pour reviewer 1-2 projets par mois minimum</span>
                </li>
            </ul>
        </div>

        <!-- Formulaire de candidature -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 overflow-hidden p-8 text-center">
            <h2 class="text-2xl font-bold mb-6">Soumets ta candidature</h2>
            
            <form method="post" action="functions/reviews/apply-reviewer.php" id="applyForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="mb-6">
                    <label for="motivation" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        Pourquoi veux-tu devenir reviewer ? *
                    </label>
                    <textarea 
                        id="motivation" 
                        name="motivation" 
                        required
                        rows="6"
                        minlength="100"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Parle-nous de ton expérience, ce qui te motive, et ce que tu peux apporter à la communauté... (min. 100 caractères)"></textarea>
                </div>
                
                <div class="mb-6 bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
                    <p class="text-sm text-purple-800 dark:text-purple-200">
                        <i class="fa-solid fa-info-circle mr-2"></i>
                        <strong>Après validation :</strong> Tu recevras un email de confirmation et tu pourras accéder au dashboard reviewer pour commencer à analyser des projets.
                    </p>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full px-8 py-4 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Envoyer ma candidature
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>