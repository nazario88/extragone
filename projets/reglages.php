<?php
include '../includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

$user = getCurrentUser();

$title = "Réglages — Projets eXtragone";
$description = "Gère ton profil et tes préférences.";
$url_canon = 'https://projets.extrag.one/reglages';
$noindex = TRUE;

include 'includes/header.php';
?>

<div class="w-full max-w-4xl mx-auto px-5 py-8">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Réglages</h1>
        <p class="text-gray-600 dark:text-gray-300">
            Gère ton profil et tes préférences de notifications
        </p>
    </div>

    <div class="space-y-8">
        
        <!-- Informations du profil -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg">
            <h2 class="text-xl font-bold mb-6 flex items-center">
                <i class="fa-solid fa-user text-blue-500 mr-3"></i>
                Informations du profil
            </h2>
            
            <form method="post" action="functions/settings/update-profile.php" enctype="multipart/form-data" id="profileForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <!-- Avatar -->
                <div class="mb-6">
                    <label class="block font-semibold text-gray-900 dark:text-white mb-3">
                        Photo de profil
                    </label>
                    <div class="flex items-center gap-6">
                        <img src="<?= $user['avatar'] ?: $base.'/uploads/avatars/'.$current_user['display_name'] ?>" 
                             id="avatar-preview"
                             alt="Avatar"
                             class="w-24 h-24 rounded-full ring-1 ring-slate-300/70 dark:ring-white/10">
                        
                        <div>
                            <input type="file" 
                                   id="avatar" 
                                   name="avatar" 
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   class="hidden"
                                   onchange="previewAvatar(this)">
                            <label for="avatar" class="cursor-pointer px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors inline-block">
                                <i class="fa-solid fa-upload mr-2"></i>
                                Changer l'avatar
                            </label>
                            <p class="text-xs text-gray-500 mt-2">JPG, PNG, GIF ou WebP - Max 2 Mo</p>
                        </div>
                    </div>
                </div>

                <!-- Nom d'affichage -->
                <div class="mb-6">
                    <label for="display_name" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        Nom d'affichage
                    </label>
                    <input 
                        type="text" 
                        id="display_name" 
                        name="display_name" 
                        value="<?= htmlspecialchars($user['display_name']) ?>"
                        maxlength="100"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <!-- Username (non modifiable) -->
                <div class="mb-6">
                    <label class="block font-semibold text-gray-900 dark:text-white mb-2">
                        Nom d'utilisateur
                    </label>
                    <input 
                        type="text" 
                        value="<?= htmlspecialchars($user['username']) ?>"
                        disabled
                        class="w-full px-4 py-3 rounded-xl text-sm bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Le nom d'utilisateur ne peut pas être modifié</p>
                </div>

                <!-- Bio -->
                <div class="mb-6">
                    <label for="bio" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        Bio
                    </label>
                    <textarea 
                        id="bio" 
                        name="bio" 
                        rows="4"
                        maxlength="500"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Parle un peu de toi..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Max 500 caractères</p>
                </div>

                <!-- Lien externe -->
                <div class="mb-6">
                    <label for="external_link" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        Lien vers ton site / portfolio
                    </label>
                    <input 
                        type="url" 
                        id="external_link" 
                        name="external_link" 
                        value="<?= htmlspecialchars($user['external_link'] ?? '') ?>"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="https://tonsite.com">
                </div>

                <button 
                    type="submit" 
                    class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-save mr-2"></i>
                    Enregistrer les modifications
                </button>
            </form>
        </div>

        <!-- Notifications par email -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg">
            <h2 class="text-xl font-bold mb-6 flex items-center">
                <i class="fa-solid fa-bell text-green-500 mr-3"></i>
                Notifications par email
            </h2>
            
            <form method="post" action="functions/settings/update-notifications.php" id="notifForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="space-y-4">
                    <!-- Notification projet publié -->
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input 
                            type="checkbox" 
                            name="email_notif_project_published" 
                            <?= $user['email_notif_project_published'] ? 'checked' : '' ?>
                            class="w-5 h-5 mt-0.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <div>
                            <div class="font-medium group-hover:text-blue-500 transition-colors">
                                Projet publié
                            </div>
                            <p class="text-sm text-gray-500">
                                Recevoir un email quand ton projet est publié avec sa review
                            </p>
                        </div>
                    </label>

                    <!-- Notification nouveau commentaire -->
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input 
                            type="checkbox" 
                            name="email_notif_new_comment" 
                            <?= $user['email_notif_new_comment'] ? 'checked' : '' ?>
                            class="w-5 h-5 mt-0.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <div>
                            <div class="font-medium group-hover:text-blue-500 transition-colors">
                                Nouveaux commentaires
                            </div>
                            <p class="text-sm text-gray-500">
                                Recevoir un email quand quelqu'un commente ton projet
                            </p>
                        </div>
                    </label>

                    <?php if (isReviewer()): ?>
                    <!-- Notification nouveau projet à reviewer -->
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input 
                            type="checkbox" 
                            name="email_notif_new_review_available" 
                            <?= $user['email_notif_new_review_available'] ? 'checked' : '' ?>
                            class="w-5 h-5 mt-0.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <div>
                            <div class="font-medium group-hover:text-blue-500 transition-colors">
                                Nouveaux projets à reviewer
                            </div>
                            <p class="text-sm text-gray-500">
                                Recevoir un email quand un nouveau projet est soumis
                            </p>
                        </div>
                    </label>
                    <?php endif; ?>
                </div>

                <button 
                    type="submit" 
                    class="w-full mt-6 px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-save mr-2"></i>
                    Enregistrer les préférences
                </button>
            </form>
        </div>

        <!-- Changer le mot de passe -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg">
            <h2 class="text-xl font-bold mb-6 flex items-center">
                <i class="fa-solid fa-lock text-orange-500 mr-3"></i>
                Changer le mot de passe
            </h2>
            
            <form method="post" action="functions/settings/change-password.php" id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="mb-4">
                    <label for="current_password" class="block font-medium mb-2">
                        Mot de passe actuel
                    </label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        required
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <div class="mb-4">
                    <label for="new_password" class="block font-medium mb-2">
                        Nouveau mot de passe
                    </label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 caractères</p>
                </div>

                <div class="mb-6">
                    <label for="confirm_password" class="block font-medium mb-2">
                        Confirmer le nouveau mot de passe
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <button 
                    type="submit" 
                    class="w-full px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-key mr-2"></i>
                    Changer le mot de passe
                </button>
            </form>
        </div>

        <!-- Zone dangereuse -->
        <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl p-6 border-2 border-red-200 dark:border-red-800">
            <h2 class="text-xl font-bold mb-3 text-red-900 dark:text-red-100 flex items-center">
                <i class="fa-solid fa-exclamation-triangle mr-3"></i>
                Zone dangereuse
            </h2>
            <p class="text-sm text-red-800 dark:text-red-200 mb-4">
                Ces actions sont irréversibles. Réfléchis bien avant de continuer.
            </p>
            <button 
                onclick="deleteAccount()" 
                class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl transition-all">
                <i class="fa-solid fa-trash mr-2"></i>
                Supprimer mon compte
            </button>
        </div>
    </div>
</div>

<script>
// Prévisualisation de l'avatar
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Suppression de compte
function deleteAccount() {
    if (!confirm('⚠️ ATTENTION !\n\nTu es sur le point de supprimer ton compte définitivement.\n\nTous tes projets, commentaires et données seront perdus.\n\nCette action est IRRÉVERSIBLE.\n\nVeux-tu vraiment continuer ?')) {
        return;
    }
    
    if (prompt('Pour confirmer, tape "SUPPRIMER" en majuscules :') !== 'SUPPRIMER') {
        alert('Suppression annulée.');
        return;
    }
    
    // TODO: Implémenter la suppression de compte
    alert('Fonctionnalité en cours de développement.');
}
</script>

<?php include 'includes/footer.php'; ?>