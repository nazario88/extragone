<?php
include '../includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

// Récupérer le slug depuis l'URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: /');
    exit;
}

// Récupérer le projet
$project = getProjectBySlug($slug);

if (!$project) {
    $_SESSION['error'] = 'Projet non trouvé.';
    header('Location: /');
    exit;
}

// Incrémenter les vues
incrementProjectViews($project['id']);

// Récupérer les images
$images = getProjectImages($project['id']);

// Récupérer les commentaires
$comments = getProjectComments($project['id']);

// Parser les outils utilisés
$tools = $project['tools_used'] ? json_decode($project['tools_used'], true) : [];

$title = htmlspecialchars($project['title']) . " — Projets eXtragone";
$description = $project['meta_description'] ?: htmlspecialchars($project['short_description']);
$url_canon = 'https://projets.extrag.one/projet/' . htmlspecialchars($slug);
$image_seo = !empty($images) ? 'https://projets.extrag.one' . $images[0]['filepath'] : 'https://projets.extrag.one/images/og-default.png';

include 'includes/header.php';

// Inclure Parsedown pour le markdown
require_once '../includes/Parsedown.php';
$parsedown = new Parsedown();
?>

<div class="w-full max-w-6xl mx-auto px-5 py-8">
    
    <!-- En-tête du projet -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <a href="<?=$base?>" class="hover:text-blue-500">Projets</a>
            <i class="fa-solid fa-chevron-right text-xs"></i>
            <span><?= htmlspecialchars($project['title']) ?></span>
        </div>
        
        <h1 class="text-4xl font-bold mb-4"><?= htmlspecialchars($project['title']) ?></h1>
        
        <!-- Métadonnées -->
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
            <a href="membre/<?= htmlspecialchars($project['username']) ?>" class="flex items-center gap-2 hover:text-blue-500 transition-colors">
                <img src="<?= $project['avatar'] ?: '/images/default-avatar.png' ?>" 
                     class="w-8 h-8 rounded-full"
                     alt="<?= htmlspecialchars($project['display_name']) ?>">
                <span class="font-medium"><?= htmlspecialchars($project['display_name']) ?></span>
            </a>
            
            <span>•</span>
            <span><?= date('d/m/Y', strtotime($project['published_at'])) ?></span>
            
            <span>•</span>
            <span class="flex items-center">
                <i class="fa-solid fa-eye mr-1"></i>
                <?= $project['view_count'] ?> vues
            </span>
            
            <span>•</span>
            <span class="flex items-center">
                <i class="fa-solid fa-comment mr-1"></i>
                <?= count($comments) ?> commentaires
            </span>
        </div>
    </div>

    <!-- Galerie d'images -->
    <?php if (!empty($images)): ?>
    <div class="mb-8">
        <div class="grid grid-cols-1 gap-4">
            <!-- Image principale (cover) -->
            <div class="rounded-2xl overflow-hidden">
                <img src="<?= htmlspecialchars($images[0]['filepath']) ?>" 
                     alt="<?= htmlspecialchars($project['title']) ?>"
                     class="w-full h-auto object-cover cursor-pointer"
                     onclick="openLightbox(0)">
            </div>
            
            <!-- Miniatures des autres images -->
            <?php if (count($images) > 1): ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php for ($i = 1; $i < count($images); $i++): ?>
                <div class="rounded-lg overflow-hidden cursor-pointer hover:opacity-80 transition-opacity"
                     onclick="openLightbox(<?= $i ?>)">
                    <img src="<?= htmlspecialchars($images[$i]['filepath']) ?>" 
                         alt="Screenshot <?= $i + 1 ?>"
                         class="w-full h-32 object-cover">
                </div>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Contenu principal -->
    <div class="grid lg:grid-cols-3 gap-8">
        
        <!-- Colonne principale -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Description courte -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h2 class="text-xl font-bold mb-3">À propos du projet</h2>
                <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                    <?= nl2br(htmlspecialchars($project['short_description'])) ?>
                </p>
            </div>

            <!-- Description longue (markdown) -->
            <?php if ($project['long_description']): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h2 class="text-xl font-bold mb-3">Description détaillée</h2>
                <div class="prose dark:prose-invert max-w-none">
                    <?= $parsedown->text($project['long_description']) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Revue du reviewer -->
            <?php if ($project['review_text']): ?>
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-2xl p-6 border-2 border-purple-200 dark:border-purple-800">
                <div class="flex items-center gap-3 mb-4">
                    <i class="fa-solid fa-star text-2xl text-purple-500"></i>
                    <div>
                        <h2 class="text-xl font-bold text-purple-900 dark:text-purple-100">Revue officielle</h2>
                        <p class="text-sm text-purple-700 dark:text-purple-300">
                            Par 
                            <a href="membre/<?= htmlspecialchars($project['reviewer_username']) ?>" class="font-medium hover:underline">
                                <?= htmlspecialchars($project['reviewer_name']) ?>
                            </a>
                            • <?= date('d/m/Y', strtotime($project['review_date'])) ?>
                        </p>
                    </div>
                </div>
                <div class="prose dark:prose-invert max-w-none text-purple-900 dark:text-purple-100">
                    <?= $parsedown->text($project['review_text']) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Section commentaires -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <i class="fa-solid fa-comments text-green-500 mr-3"></i>
                    Commentaires (<?= count($comments) ?>)
                </h2>
                
                <?php if (isLoggedIn()): ?>
                <!-- Formulaire d'ajout de commentaire -->
                <form method="post" action="functions/comments/add-comment.php" class="mb-6" id="commentForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    
                    <textarea 
                        name="content" 
                        rows="3" 
                        required
                        placeholder="Partage ton avis sur ce projet..."
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all mb-3"></textarea>
                    
                    <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        Publier le commentaire
                    </button>
                </form>
                <?php else: ?>
                <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                    <p class="text-blue-800 dark:text-blue-200">
                        <i class="fa-solid fa-info-circle mr-2"></i>
                        <a href="connexion" class="font-medium hover:underline">Connecte-toi</a> pour commenter ce projet.
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- Liste des commentaires -->
                <?php if (empty($comments)): ?>
                    <p class="text-gray-500 text-center py-8">Aucun commentaire pour le moment. Sois le premier !</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($comments as $comment): ?>
                        <div class="border-l-4 border-blue-500 pl-4 py-2" id="comment-<?= $comment['id'] ?>">
                            <div class="flex items-start gap-3">
                                <img src="<?= $comment['avatar'] ?: '/images/default-avatar.png' ?>" 
                                     class="w-10 h-10 rounded-full"
                                     alt="<?= htmlspecialchars($comment['display_name']) ?>">
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <a href="membre/<?= htmlspecialchars($comment['username']) ?>" class="font-medium hover:text-blue-500 transition-colors">
                                            <?= htmlspecialchars($comment['display_name']) ?>
                                        </a>
                                        <span class="text-xs text-gray-500">
                                            <?= timeAgo($comment['created_at']) ?>
                                            <?php if ($comment['is_edited']): ?>
                                                <span class="ml-1">(modifié)</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-700 dark:text-gray-300 mb-2 comment-content">
                                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                    </p>
                                    
                                    <!-- Actions (si c'est son commentaire) -->
                                    <?php if (isLoggedIn() && getCurrentUser()['id'] == $comment['user_id']): ?>
                                    <div class="flex gap-3 text-xs">
                                        <button onclick="editComment(<?= $comment['id'] ?>, <?= htmlspecialchars(json_encode($comment['content']), ENT_QUOTES) ?>)" 
                                                class="text-blue-500 hover:underline">
                                            <i class="fa-solid fa-edit mr-1"></i>Modifier
                                        </button>
                                        <button onclick="deleteComment(<?= $comment['id'] ?>)" 
                                                class="text-red-500 hover:underline">
                                            <i class="fa-solid fa-trash mr-1"></i>Supprimer
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Lien démo -->
            <?php if ($project['demo_link']): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="font-bold mb-3 flex items-center">
                    <i class="fa-solid fa-link text-blue-500 mr-2"></i>
                    Lien
                </h3>
                <a href="<?= htmlspecialchars($project['demo_link']) ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="block px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white text-center rounded-xl transition-colors font-medium">
                    <i class="fa-solid fa-external-link-alt mr-2"></i>
                    Voir le projet
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Outils utilisés -->
            <?php if (!empty($tools)): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="font-bold mb-3 flex items-center">
                    <i class="fa-solid fa-toolbox text-yellow-500 mr-2"></i>
                    Technologies
                </h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($tools as $tool): ?>
                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-sm rounded-lg">
                        <?= htmlspecialchars($tool) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Vidéo YouTube si présente -->
            <?php if ($project['youtube_video_id']): ?>
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="font-bold mb-3 flex items-center">
                    <i class="fa-brands fa-youtube text-red-500 mr-2"></i>
                    Présenté sur YouTube
                </h3>
                <div class="aspect-video rounded-lg overflow-hidden">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        src="https://www.youtube.com/embed/<?= htmlspecialchars($project['youtube_video_id']) ?>" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen></iframe>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Partage -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <h3 class="font-bold mb-3 flex items-center">
                    <i class="fa-solid fa-share-nodes text-green-500 mr-2"></i>
                    Partager
                </h3>
                <div class="flex gap-2">
                    <button onclick="shareOnTwitter()" class="flex-1 px-3 py-2 bg-blue-400 hover:bg-blue-500 text-white rounded-lg transition-colors text-sm">
                        <i class="fa-brands fa-twitter"></i>
                    </button>
                    <button onclick="shareOnLinkedIn()" class="flex-1 px-3 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors text-sm">
                        <i class="fa-brands fa-linkedin"></i>
                    </button>
                    <button onclick="copyUrl()" class="flex-1 px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors text-sm">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox pour les images -->
<div id="lightbox" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300" onclick="closeLightbox()">
        <i class="fa-solid fa-times"></i>
    </button>
    <button class="absolute left-4 text-white text-3xl hover:text-gray-300" onclick="event.stopPropagation(); prevImage()">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button class="absolute right-4 text-white text-3xl hover:text-gray-300" onclick="event.stopPropagation(); nextImage()">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
    <img id="lightbox-img" src="" class="max-w-full max-h-full" onclick="event.stopPropagation()">
</div>

<script>
const images = <?= json_encode(array_column($images, 'filepath')) ?>;
let currentImageIndex = 0;

function openLightbox(index) {
    currentImageIndex = index;
    document.getElementById('lightbox-img').src = images[index];
    document.getElementById('lightbox').classList.remove('hidden');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden');
}

function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % images.length;
    document.getElementById('lightbox-img').src = images[currentImageIndex];
}

function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
    document.getElementById('lightbox-img').src = images[currentImageIndex];
}

// Éditer un commentaire
function editComment(commentId, content) {
    const commentDiv = document.getElementById('comment-' + commentId);
    const contentElement = commentDiv.querySelector('.comment-content');
    
    const textarea = document.createElement('textarea');
    textarea.className = 'w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2';
    textarea.value = content;
    textarea.rows = 3;
    
    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Sauvegarder';
    saveBtn.className = 'px-3 py-1 bg-blue-500 text-white rounded mr-2 text-sm';
    saveBtn.onclick = function() {
        saveCommentEdit(commentId, textarea.value);
    };
    
    const cancelBtn = document.createElement('button');
    cancelBtn.textContent = 'Annuler';
    cancelBtn.className = 'px-3 py-1 bg-gray-500 text-white rounded text-sm';
    cancelBtn.onclick = function() {
        location.reload();
    };
    
    contentElement.replaceWith(textarea);
    commentDiv.querySelector('.flex.gap-3').innerHTML = '';
    commentDiv.querySelector('.flex.gap-3').appendChild(saveBtn);
    commentDiv.querySelector('.flex.gap-3').appendChild(cancelBtn);
}

function saveCommentEdit(commentId, content) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= generateCSRFToken() ?>');
    formData.append('comment_id', commentId);
    formData.append('content', content);
    
    fetch('functions/edit-comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur lors de la modification');
        }
    });
}

function deleteComment(commentId) {
    if (!confirm('Supprimer ce commentaire ?')) return;
    
    const formData = new FormData();
    formData.append('csrf_token', '<?= generateCSRFToken() ?>');
    formData.append('comment_id', commentId);
    
    fetch('functions/delete-comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur lors de la suppression');
        }
    });
}

// Partage
function shareOnTwitter() {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent('<?= htmlspecialchars($project['title']) ?> sur @extragone');
    window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
}

function shareOnLinkedIn() {
    const url = encodeURIComponent(window.location.href);
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank');
}

function copyUrl() {
    navigator.clipboard.writeText(window.location.href);
    alert('Lien copié !');
}
</script>

<style>
.prose h1, .prose h2, .prose h3 {
    color: inherit;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
}
.prose p {
    margin-bottom: 1em;
}
.prose a {
    color: #3b82f6;
    text-decoration: underline;
}
.prose code {
    background-color: rgba(0,0,0,0.1);
    padding: 0.2em 0.4em;
    border-radius: 0.25em;
    font-size: 0.9em;
}
</style>

<?php include 'includes/footer.php'; ?>