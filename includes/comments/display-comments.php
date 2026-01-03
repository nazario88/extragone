<?php
/**
 * Section d'affichage des avis et commentaires sur un outil
 * À inclure dans outil.php
 */

// Variables attendues : $data_outil, $tool_comments, $user_comment, $comments_count
?>

<div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg">
    <h2 class="text-2xl font-bold mb-6 flex items-center">
        <i class="fa-solid fa-comments text-blue-500 mr-3"></i>
        Avis de la communauté (<?= $comments_count ?>)
    </h2>
    
    <?php if (isLoggedIn()): ?>
        <?php if ($user_comment): ?>
            <!-- L'utilisateur a déjà commenté - Affichage + édition -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border-2 border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-blue-900 dark:text-blue-100">
                        <i class="fa-solid fa-user-check mr-2"></i>
                        Votre avis
                    </h3>
                    <button onclick="editMyComment()" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        <i class="fa-solid fa-edit mr-1"></i>Modifier
                    </button>
                </div>
                
                <div id="myCommentDisplay">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="font-medium">Note :</span>
                        <div class="flex">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-solid fa-star <?= $i <= $user_comment['rating'] ? 'text-yellow-400' : 'text-gray-300' ?> text-sm"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-sm text-gray-500">(<?= $user_comment['rating'] ?>/5)</span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300"><?= nl2br(htmlspecialchars($user_comment['comment'])) ?></p>
                    <?php if ($user_comment['is_edited']): ?>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fa-solid fa-clock mr-1"></i>
                        Modifié le <?= date('d/m/Y', strtotime($user_comment['updated_at'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
                
                <!-- Formulaire d'édition (caché par défaut) -->
                <form id="editCommentForm" class="hidden" onsubmit="submitEditComment(event)">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="comment_id" value="<?= $user_comment['id'] ?>">
                    
                    <div class="mb-4">
                        <label class="block font-medium mb-2">Note</label>
                        <div class="flex items-center gap-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" 
                                    class="edit-star text-2xl <?= $i <= $user_comment['rating'] ? 'text-yellow-400' : 'text-gray-300' ?> hover:text-yellow-400 transition"
                                    data-value="<?= $i ?>">
                                <i class="fa-solid fa-star"></i>
                            </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="editRating" value="<?= $user_comment['rating'] ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block font-medium mb-2">Commentaire</label>
                        <textarea 
                            name="comment" 
                            rows="4" 
                            required
                            maxlength="2000"
                            class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ><?= htmlspecialchars($user_comment['comment']) ?></textarea>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                            <i class="fa-solid fa-save mr-2"></i>Sauvegarder
                        </button>
                        <button type="button" onclick="cancelEditComment()" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                            Annuler
                        </button>
                        <button type="button" onclick="deleteMyComment()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition ml-auto">
                            <i class="fa-solid fa-trash mr-2"></i>Supprimer
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Formulaire d'ajout de commentaire -->
            <form id="addCommentForm" class="mb-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800" onsubmit="submitAddComment(event)">
                <h3 class="font-bold text-lg mb-4 text-blue-900 dark:text-blue-100">
                    <i class="fa-solid fa-pen mr-2"></i>
                    Laisser un avis
                </h3>
                
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="tool_id" value="<?= $data_outil['id'] ?>">
                
                <div class="mb-4">
                    <label class="block font-medium mb-2">Note *</label>
                    <div class="flex items-center gap-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" 
                                class="add-star text-2xl text-gray-300 hover:text-yellow-400 transition"
                                data-value="<?= $i ?>">
                            <i class="fa-solid fa-star"></i>
                        </button>
                        <?php endfor; ?>
                        <span id="addRatingLabel" class="text-sm text-gray-500 ml-2">0/5</span>
                    </div>
                    <input type="hidden" name="rating" id="addRating" value="0" required>
                </div>
                
                <div class="mb-4">
                    <label class="block font-medium mb-2">Votre avis *</label>
                    <textarea 
                        name="comment" 
                        rows="4" 
                        required
                        maxlength="2000"
                        placeholder="Partagez votre expérience avec cet outil..."
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 2000 caractères</p>
                </div>
                
                <button type="submit" class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Publier mon avis
                </button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <!-- Invitation à se connecter -->
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800 text-center">
            <i class="fa-solid fa-user-plus text-4xl text-blue-500 mb-3"></i>
            <p class="text-blue-800 dark:text-blue-200 mb-4">
                <strong>Connectez-vous</strong> pour laisser un avis sur cet outil
            </p>
            <a href="connexion?redirect=outil/<?= htmlspecialchars($data_outil['slug']) ?>" 
               class="inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>
                Se connecter / S'inscrire
            </a>
        </div>
    <?php endif; ?>
    
    <!-- Liste des commentaires -->
    <?php if (empty($tool_comments)): ?>
        <div class="text-center py-12 text-gray-500">
            <i class="fa-solid fa-inbox text-4xl mb-4 opacity-50"></i>
            <p>Aucun avis pour le moment. Soyez le premier à partager votre expérience !</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <h3 class="font-bold text-lg mb-4">Tous les avis</h3>
            
            <?php foreach ($tool_comments as $comment): ?>
            <div class="border-l-4 border-blue-500 pl-4 py-3 bg-slate-50 dark:bg-slate-700/50 rounded-r-xl">
                <div class="flex items-start gap-3">
                    <img src="https://www.extrag.one<?= $comment['avatar'] ?: '/uploads/avatars/'.urlencode($comment['display_name']) ?>" 
                         class="w-12 h-12 object-cover rounded-full ring-2 ring-slate-300 dark:ring-slate-600"
                         alt="<?= htmlspecialchars($comment['display_name']) ?>">
                    
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <a href="https://www.extrag.one/membre/<?= htmlspecialchars($comment['username']) ?>" 
                               class="font-medium hover:text-blue-500 transition-colors">
                                <?= htmlspecialchars($comment['display_name']) ?>
                            </a>
                            
                            <div class="flex items-center gap-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-solid fa-star <?= $i <= $comment['rating'] ? 'text-yellow-400' : 'text-gray-300' ?> text-xs"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <span class="text-xs text-gray-500">
                                <?= timeAgoTool($comment['created_at']) ?>
                                <?php if ($comment['is_edited']): ?>
                                    <span class="ml-1">(modifié)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <p class="text-gray-700 dark:text-gray-300">
                            <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Scripts pour la gestion des commentaires -->
<script>
// === SYSTÈME D'AJOUT DE COMMENTAIRE ===
const addStars = document.querySelectorAll('.add-star');
const addRatingInput = document.getElementById('addRating');
const addRatingLabel = document.getElementById('addRatingLabel');

addStars.forEach(star => {
    star.addEventListener('click', function() {
        const value = parseInt(this.dataset.value);
        addRatingInput.value = value;
        addRatingLabel.textContent = value + '/5';
        
        addStars.forEach((s, i) => {
            if (i < value) {
                s.classList.remove('text-gray-300');
                s.classList.add('text-yellow-400');
            } else {
                s.classList.remove('text-yellow-400');
                s.classList.add('text-gray-300');
            }
        });
    });
});

async function submitAddComment(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    if (parseInt(formData.get('rating')) === 0) {
        alert('Veuillez attribuer une note');
        return;
    }
    
    try {
        const response = await fetch('includes/comments/add-comment.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur lors de l\'ajout');
        }
    } catch (error) {
        alert('Erreur réseau');
    }
}

// === SYSTÈME D'ÉDITION DE COMMENTAIRE ===
const editStars = document.querySelectorAll('.edit-star');
const editRatingInput = document.getElementById('editRating');

editStars.forEach(star => {
    star.addEventListener('click', function() {
        const value = parseInt(this.dataset.value);
        editRatingInput.value = value;
        
        editStars.forEach((s, i) => {
            if (i < value) {
                s.classList.remove('text-gray-300');
                s.classList.add('text-yellow-400');
            } else {
                s.classList.remove('text-yellow-400');
                s.classList.add('text-gray-300');
            }
        });
    });
});

function editMyComment() {
    document.getElementById('myCommentDisplay').classList.add('hidden');
    document.getElementById('editCommentForm').classList.remove('hidden');
}

function cancelEditComment() {
    document.getElementById('myCommentDisplay').classList.remove('hidden');
    document.getElementById('editCommentForm').classList.add('hidden');
}

async function submitEditComment(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('includes/comments/edit-comment.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur lors de la modification');
        }
    } catch (error) {
        alert('Erreur réseau');
    }
}

async function deleteMyComment() {
    if (!confirm('Supprimer définitivement votre avis ?')) return;
    
    const formData = new FormData();
    formData.append('csrf_token', '<?= generateCSRFToken() ?>');
    formData.append('comment_id', '<?= $user_comment['id'] ?? 0 ?>');
    
    try {
        const response = await fetch('includes/comments/delete-comment.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur lors de la suppression');
        }
    } catch (error) {
        alert('Erreur réseau');
    }
}
</script>