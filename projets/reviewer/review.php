<?php
include '../../includes/config.php';
include '../includes/auth.php';
include '../includes/functions.php';

// Vérifier que l'utilisateur est reviewer
requireRole('reviewer');

$user = getCurrentUser();
$project_id = (int)($_GET['id'] ?? 0);

// Récupérer le projet
$stmt = $pdo->prepare('
    SELECT p.*, u.username, u.display_name, u.avatar, u.email
    FROM extra_proj_projects p
    JOIN extra_proj_users u ON p.user_id = u.id
    WHERE p.id = ? AND p.reviewer_id = ? AND p.status = "in_review"
');
$stmt->execute([$project_id, $user['id']]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    $_SESSION['error'] = 'Projet non trouvé ou non assigné à toi.';
    header('Location: /reviewer/dashboard');
    exit;
}

// Récupérer les images
$images = getProjectImages($project_id);

// Parser les outils
$tools = $project['tools_used'] ? json_decode($project['tools_used'], true) : [];

$title = "Review : " . htmlspecialchars($project['title']) . " — Reviewer";
$description = "Rédaction de la review pour " . htmlspecialchars($project['title']);
$url_canon = 'https://projets.extrag.one/reviewer/review/' . $project_id;
$noindex = TRUE;

include '../includes/header.php';
?>

<!-- SimpleMDE CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
<style>
    .dark .CodeMirror {
        background-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
        border-color: rgb(71, 85, 105);
    }
    .dark .CodeMirror-cursor { border-left-color: white; }
    .dark .editor-toolbar {
        background-color: rgb(30, 41, 59);
        border-color: rgb(71, 85, 105);
    }
    .dark .editor-toolbar a { color: rgb(203, 213, 225) !important; }
    .dark .editor-toolbar a:hover {
        background-color: rgb(51, 65, 85);
        border-color: rgb(100, 116, 139);
    }
</style>

<div class="w-full max-w-7xl mx-auto px-5 py-8">
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <a href="/reviewer/dashboard" class="hover:text-blue-500">Dashboard</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span>Review en cours</span>
        </div>
        
        <h1 class="text-3xl font-bold mb-2">
            Review : <?= htmlspecialchars($project['title']) ?>
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
            Projet soumis par <?= htmlspecialchars($project['display_name']) ?> le <?= date('d/m/Y', strtotime($project['created_at'])) ?>
        </p>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        
        <!-- Colonne principale : Formulaire de review -->
        <div class="lg:col-span-2">
            <form method="post" action="/functions/reviews/publish-project.php" id="reviewForm" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="project_id" value="<?= $project_id ?>">
                
                <!-- Meta description -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                    <label for="meta_description" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        <i class="fa-solid fa-tag text-blue-500 mr-2"></i>
                        Meta description (SEO) *
                    </label>
                    <textarea 
                        id="meta_description" 
                        name="meta_description" 
                        required
                        maxlength="300"
                        rows="2"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Description optimisée pour le SEO (max 300 caractères)"><?= htmlspecialchars($project['short_description']) ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Cette description apparaîtra dans les moteurs de recherche</p>
                </div>

                <!-- Texte de la review -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                    <label for="review_text" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        <i class="fa-solid fa-star text-purple-500 mr-2"></i>
                        Texte de la review *
                    </label>
                    <textarea 
                        id="review_text" 
                        name="review_text" 
                        required
                        rows="15"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Rédige ta review détaillée..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Markdown supporté : **gras**, *italique*, ## Titre, [lien](url)</p>
                </div>

                <!-- Gestion de l'image de couverture -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                    <label class="block font-semibold text-gray-900 dark:text-white mb-3">
                        <i class="fa-solid fa-image text-green-500 mr-2"></i>
                        Image de couverture
                    </label>
                    
                    <?php if (!empty($images)): ?>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($images as $index => $image): ?>
                            <label class="relative cursor-pointer group">
                                <input 
                                    type="radio" 
                                    name="cover_image_id" 
                                    value="<?= $image['id'] ?>"
                                    <?= $image['is_cover'] ? 'checked' : '' ?>
                                    class="absolute top-2 right-2 w-5 h-5">
                                <img src="<?= htmlspecialchars($image['filepath']) ?>" 
                                     alt="Screenshot <?= $index + 1 ?>"
                                     class="w-full h-32 object-cover rounded-lg border-2 transition-all <?= $image['is_cover'] ? 'border-blue-500' : 'border-slate-300 dark:border-slate-600 group-hover:border-blue-400' ?>">
                                <?php if ($image['is_cover']): ?>
                                <span class="absolute bottom-2 left-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                    Couverture
                                </span>
                                <?php endif; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Sélectionne l'image qui servira de couverture</p>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">Aucune image uploadée par l'auteur</p>
                    <?php endif; ?>
                </div>

                <!-- ID vidéo YouTube (optionnel) -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                    <label for="youtube_video_id" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        <i class="fa-brands fa-youtube text-red-500 mr-2"></i>
                        ID vidéo YouTube (optionnel)
                    </label>
                    <input 
                        type="text" 
                        id="youtube_video_id" 
                        name="youtube_video_id" 
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Ex: dQw4w9WgXcQ">
                    <p class="text-xs text-gray-500 mt-1">Si tu as fait une vidéo sur ce projet, ajoute l'ID YouTube ici</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button 
                        type="submit" 
                        id="publishBtn"
                        class="flex-1 px-8 py-4 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                        <span id="publishText">
                            <i class="fa-solid fa-check-circle mr-2"></i>
                            Publier le projet
                        </span>
                        <span id="publishLoading" class="hidden">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                            Publication en cours...
                        </span>
                    </button>
                    
                    <a href="/reviewer/dashboard" class="px-6 py-4 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-xl transition-all">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Sidebar : Aperçu du projet -->
        <div class="space-y-6">
            
            <!-- Info projet -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="font-bold mb-4 flex items-center">
                    <i class="fa-solid fa-info-circle text-blue-500 mr-2"></i>
                    Informations du projet
                </h3>
                
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-500">Auteur :</span>
                        <div class="flex items-center gap-2 mt-1">
                            <img src="<?= $project['avatar'] ?: '/images/default-avatar.png' ?>" 
                                 class="w-6 h-6 rounded-full"
                                 alt="<?= htmlspecialchars($project['display_name']) ?>">
                            <span class="font-medium"><?= htmlspecialchars($project['display_name']) ?></span>
                        </div>
                    </div>
                    
                    <div>
                        <span class="text-gray-500">Soumis le :</span>
                        <div class="font-medium"><?= date('d/m/Y à H:i', strtotime($project['created_at'])) ?></div>
                    </div>
                    
                    <?php if ($project['demo_link']): ?>
                    <div>
                        <span class="text-gray-500">Lien :</span>
                        <a href="<?= htmlspecialchars($project['demo_link']) ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="block text-blue-500 hover:underline break-all mt-1">
                            <?= htmlspecialchars($project['demo_link']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($tools)): ?>
                    <div>
                        <span class="text-gray-500">Technologies :</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <?php foreach ($tools as $tool): ?>
                            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-xs rounded">
                                <?= htmlspecialchars($tool) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description du projet -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="font-bold mb-3">Description courte</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    <?= nl2br(htmlspecialchars($project['short_description'])) ?>
                </p>
            </div>

            <?php if ($project['long_description']): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="font-bold mb-3">Description détaillée</h3>
                <div class="text-sm text-gray-700 dark:text-gray-300 prose dark:prose-invert max-w-none">
                    <?php
                    require_once '../../includes/Parsedown.php';
                    $parsedown = new Parsedown();
                    echo $parsedown->text($project['long_description']);
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Images du projet -->
            <?php if (!empty($images)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="font-bold mb-3">Screenshots (<?= count($images) ?>)</h3>
                <div class="space-y-2">
                    <?php foreach ($images as $image): ?>
                    <img src="<?= htmlspecialchars($image['filepath']) ?>" 
                         alt="Screenshot"
                         class="w-full rounded-lg cursor-pointer hover:opacity-80 transition-opacity"
                         onclick="window.open('<?= htmlspecialchars($image['filepath']) ?>', '_blank')">
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Conseils pour la review -->
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-2xl p-6 border border-purple-200 dark:border-purple-800">
                <h3 class="font-semibold text-purple-900 dark:text-purple-100 mb-3">
                    <i class="fa-solid fa-lightbulb mr-2"></i>
                    Conseils pour une bonne review
                </h3>
                <ul class="text-sm text-purple-800 dark:text-purple-200 space-y-2">
                    <li>✓ Commence par un résumé global</li>
                    <li>✓ Mentionne les points forts</li>
                    <li>✓ Identifie les axes d'amélioration</li>
                    <li>✓ Commente l'UI/UX et l'ergonomie</li>
                    <li>✓ Évalue la qualité technique</li>
                    <li>✓ Sois constructif et bienveillant</li>
                    <li>✓ Termine par une conclusion</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- SimpleMDE JS -->
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
<script>
// Initialiser SimpleMDE pour la review
const simplemde = new SimpleMDE({
    element: document.getElementById("review_text"),
    spellChecker: false,
    placeholder: "Rédige ta review détaillée du projet...",
    toolbar: ["bold", "italic", "heading-2", "heading-3", "|", "quote", "unordered-list", "ordered-list", "|", "link", "|", "preview", "guide"],
    status: false
});

// Gestion du formulaire
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('publishBtn');
    const text = document.getElementById('publishText');
    const loading = document.getElementById('publishLoading');
    
    btn.disabled = true;
    text.classList.add('hidden');
    loading.classList.remove('hidden');
});
</script>

<?php include '../includes/footer.php'; ?>