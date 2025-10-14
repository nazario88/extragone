<?php
include '../includes/config.php';
include 'includes/auth.php';

// Si déjà connecté, rediriger
if (isLoggedIn()) {
    header('Location: /');
    exit;
}

$title = "Connexion / Inscription — Projets eXtragone";
$description = "Connecte-toi pour soumettre tes projets et participer à la communauté.";
$url_canon = 'https://projets.extrag.one/connexion';

include 'includes/functions.php';
include 'includes/header.php';
?>

<div class="w-full max-w-4xl mx-auto px-5 py-12">
    
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold mb-2">Rejoins la communauté</h1>
        <p class="text-gray-600 dark:text-gray-300">
            Connecte-toi ou crée un compte pour soumettre tes projets
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">
        
        <!-- Formulaire de connexion -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <i class="fa-solid fa-right-to-bracket text-blue-500 mr-3"></i>
                Connexion
            </h2>
            
            <form method="post" action="functions/auth/login.php" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="mb-4">
                    <label for="login_email" class="block font-medium mb-2">Email</label>
                    <input 
                        type="email" 
                        id="login_email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="ton@email.com">
                </div>
                
                <div class="mb-6">
                    <label for="login_password" class="block font-medium mb-2">Mot de passe</label>
                    <input 
                        type="password" 
                        id="login_password" 
                        name="password" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="••••••••">
                </div>
                
                <button 
                    type="submit" 
                    class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>
                    Se connecter
                </button>
            </form>
        </div>

        <!-- Formulaire d'inscription -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg">
            <h2 class="text-2xl font-bold mb-6 flex items-center">
                <i class="fa-solid fa-user-plus text-green-500 mr-3"></i>
                Inscription
            </h2>
            
            <form method="post" action="functions/register.php" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="mb-4">
                    <label for="register_email" class="block font-medium mb-2">Email</label>
                    <input 
                        type="email" 
                        id="register_email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="ton@email.com">
                </div>
                
                <div class="mb-4">
                    <label for="register_username" class="block font-medium mb-2">
                        Nom d'utilisateur
                        <span class="text-xs text-gray-500">(3-50 caractères)</span>
                    </label>
                    <input 
                        type="text" 
                        id="register_username" 
                        name="username" 
                        required
                        minlength="3"
                        maxlength="50"
                        pattern="[a-zA-Z0-9_-]+"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="tonpseudo">
                    <p class="text-xs text-gray-500 mt-1">Lettres, chiffres, _ et - uniquement</p>
                </div>
                
                <div class="mb-4">
                    <label for="register_display_name" class="block font-medium mb-2">
                        Nom d'affichage <span class="text-xs text-gray-500">(optionnel)</span>
                    </label>
                    <input 
                        type="text" 
                        id="register_display_name" 
                        name="display_name" 
                        maxlength="100"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="Ton Nom Public">
                </div>
                
                <div class="mb-6">
                    <label for="register_password" class="block font-medium mb-2">
                        Mot de passe
                        <span class="text-xs text-gray-500">(min. 8 caractères)</span>
                    </label>
                    <input 
                        type="password" 
                        id="register_password" 
                        name="password" 
                        required
                        minlength="8"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        placeholder="••••••••">
                </div>
                
                <button 
                    type="submit" 
                    class="w-full px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-user-plus mr-2"></i>
                    Créer mon compte
                </button>
            </form>
        </div>
    </div>

    <!-- Informations supplémentaires -->
    <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
        <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">
            <i class="fa-solid fa-info-circle mr-2"></i>
            Pourquoi créer un compte ?
        </h3>
        <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Soumettre tes projets pour obtenir une revue</li>
            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Commenter et échanger avec la communauté</li>
            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Candidater pour devenir reviewer</li>
            <li><i class="fa-solid fa-check text-green-500 mr-2"></i>Créer ton profil public avec tes réalisations</li>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>