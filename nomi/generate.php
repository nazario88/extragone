<?php
include '../includes/config.php';

$title = "Générer des noms pour ton projet — Nomi";
$description = "Formulaire pour générer des noms créatifs avec l'IA pour ton projet, startup ou outil.";

$url_canon = 'https://nomi.extrag.one/generate';

// Initialisation des variables
$project_description = $example_names = $keywords = $message = '';
$preferences_length = 'moyen';
$preferences_style = 'moderne';

// Affichage des messages d'erreur
if (isset($_SESSION['error'])) {
    $message = '<div class="mx-auto text-center p-3 bg-red-100 text-red-800 rounded-xl border border-red-200 mt-4">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}

include 'includes/header.php';
?>

<div class="w-full lg:w-3/4 xl:w-2/3 2xl:w-1/2 mx-auto">
    
    <div class="w-full px-5 py-5">
        <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">
            &larr; génération avec l'IA
        </p>
        <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
            Générer des noms pour ton projet
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-300">
            Plus tu es précis, meilleurs seront les résultats ! Remplis les champs ci-dessous et laisse l'IA faire le reste.
        </p>
        <?= $message ?>
    </div>

    <!-- Formulaire -->
    <div class="px-5">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-lg p-6 border border-slate-200 dark:border-slate-700">
            <form method="post" action="functions/process.php" name="generate_names" id="generateForm">
                
                <!-- Description du projet -->
                <div class="mb-6">
                    <label for="project_description" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        <i class="fa-solid fa-lightbulb text-yellow-500 mr-2"></i>
                        Description de ton projet *
                    </label>
                    <textarea 
                        id="project_description" 
                        name="project_description" 
                        value="<?= htmlspecialchars($project_description) ?>"
                        placeholder="Ex: Une application mobile pour aider les étudiants à organiser leurs révisions avec des flashcards intelligentes"
                        rows="3" 
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                        required 
                        minlength="20"><?= htmlspecialchars($project_description) ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Décris ton projet en une ou deux phrases claires</p>
                </div>

                <!-- Noms d'exemple -->
                <div class="mb-6">
                    <label for="example_names" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        <i class="fa-solid fa-heart text-red-500 mr-2"></i>
                        Noms que tu aimes (optionnel)
                    </label>
                    <input 
                        type="text" 
                        id="example_names" 
                        name="example_names" 
                        value="<?= htmlspecialchars($example_names) ?>"
                        placeholder="Ex: Notion, Figma, Slack, Discord"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-500 mt-1">Quelques noms de marques ou outils que tu trouves réussis (séparés par des virgules)</p>
                </div>

                <!-- Mots-clés -->
                <div class="mb-6">
                    <label for="keywords" class="block font-semibold text-gray-900 dark:text-white mb-2">
                        <i class="fa-solid fa-tags text-blue-500 mr-2"></i>
                        Mots-clés associés (optionnel)
                    </label>
                    <input 
                        type="text" 
                        id="keywords" 
                        name="keywords" 
                        value="<?= htmlspecialchars($keywords) ?>"
                        placeholder="Ex: étude, apprentissage, mémorisation, flashcard, révision"
                        class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-500 mt-1">Mots importants liés à ton domaine d'activité</p>
                </div>

                <!-- Préférences -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <!-- Longueur -->
                    <div>
                        <label class="block font-semibold text-gray-900 dark:text-white mb-3">
                            <i class="fa-solid fa-ruler text-green-500 mr-2"></i>
                            Longueur préférée
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="preferences_length" value="court" <?= $preferences_length == 'court' ? 'checked' : '' ?> 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <span class="ml-2 text-sm">Court (3-5 lettres)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="preferences_length" value="moyen" <?= $preferences_length == 'moyen' ? 'checked' : '' ?> 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <span class="ml-2 text-sm">Moyen (6-10 lettres)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="preferences_length" value="long" <?= $preferences_length == 'long' ? 'checked' : '' ?> 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <span class="ml-2 text-sm">Long (10+ lettres)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Style -->
                    <div>
                        <label class="block font-semibold text-gray-900 dark:text-white mb-3">
                            <i class="fa-solid fa-palette text-purple-500 mr-2"></i>
                            Style préféré
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="preferences_style" value="moderne" <?= $preferences_style == 'moderne' ? 'checked' : '' ?> 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <span class="ml-2 text-sm">Moderne (Notion, Slack)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="preferences_style" value="tech" <?= $preferences_style == 'tech' ? 'checked' : '' ?> 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <span class="ml-2 text-sm">Tech (GitHub, Docker)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="preferences_style" value="creatif" <?= $preferences_style == 'creatif' ? 'checked' : '' ?> 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <span class="ml-2 text-sm">Créatif (Figma, Canva)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="preferences_style" value="classique" <?= $preferences_style == 'classique' ? 'checked' : '' ?> 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600">
                                <span class="ml-2 text-sm">Classique (Microsoft, Adobe)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Bouton de soumission -->
                <div class="text-center">
                    <button 
                        type="submit" 
                        id="generateBtn"
                        class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-lg rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                        <span id="btnText">
                            <i class="fa-solid fa-magic mr-2"></i>
                            Générer 30 noms créatifs
                        </span>
                        <span id="btnLoading" class="hidden">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                            Génération en cours...
                        </span>
                    </button>
                </div>

                <p class="text-xs text-gray-500 text-center mt-3">
                    La génération prend généralement 10-15 secondes
                </p>
            </form>
        </div>
    </div>

    <!-- Section conseils -->
    <div class="px-5 py-8">
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
            <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">
                <i class="fa-solid fa-lightbulb mr-2"></i>
                Conseils pour de meilleurs résultats
            </h3>
            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                <li>• Sois précis dans ta description : plus c'est clair, mieux c'est</li>
                <li>• Mentionne ton public cible (étudiants, professionnels, créateurs...)</li>
                <li>• Indique si c'est une app, un site web, un service, etc.</li>
                <li>• Les noms d'exemple aident l'IA à comprendre tes goûts</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.getElementById('generateForm').addEventListener('submit', function() {
    const btn = document.getElementById('generateBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    
    // Désactiver le bouton et afficher le loading
    btn.disabled = true;
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
});

// Auto-resize textarea
document.getElementById('project_description').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});
</script>

<?php
include 'includes/footer.php';
?>