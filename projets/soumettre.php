<?php
include '../includes/config.php';
include 'includes/auth.php';
include 'includes/functions.php';

// Vérifier que l'utilisateur est connecté
requireLogin();

$title = "Soumettre un projet — Projets eXtragone";
$description = "Soumets ton projet pour obtenir une revue détaillée de notre équipe.";
$url_canon = 'https://projets.extrag.one/soumettre';

include 'includes/header.php';
?>

<!-- SimpleMDE CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
<style>
    /* Override SimpleMDE pour dark mode */
    .dark .CodeMirror {
        background-color: rgb(51, 65, 85);
        color: rgb(226, 232, 240);
        border-color: rgb(71, 85, 105);
    }
    .dark .CodeMirror-cursor {
        border-left-color: white;
    }
    .dark .editor-toolbar {
        background-color: rgb(30, 41, 59);
        border-color: rgb(71, 85, 105);
    }
    .dark .editor-toolbar a {
        color: rgb(203, 213, 225) !important;
    }
    .dark .editor-toolbar a:hover {
        background-color: rgb(51, 65, 85);
        border-color: rgb(100, 116, 139);
    }
    .dark .editor-toolbar.disabled-for-preview a:not(.no-disable) {
        background-color: transparent;
    }
</style>

<div class="w-full max-w-4xl mx-auto px-5 py-8">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Soumettre un projet</h1>
        <p class="text-gray-600 dark:text-gray-300">
            Remplis ce formulaire pour soumettre ton projet. Il sera ensuite reviewé par notre équipe avant publication.
        </p>
    </div>

    <!-- Formulaire -->
    <form method="post" action="functions/projects/submit-project.php" enctype="multipart/form-data" id="submitForm">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-lg space-y-6">
            
            <!-- Titre du projet -->
            <div>
                <label for="title" class="block font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fa-solid fa-heading text-blue-500 mr-2"></i>
                    Titre du projet *
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    required
                    maxlength="200"
                    class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Ex: MonSuperOutil - Générateur de palettes de couleurs">
                <p class="text-xs text-gray-500 mt-1">Un titre clair et descriptif (max 200 caractères)</p>
            </div>

            <!-- Description courte -->
            <div>
                <label for="short_description" class="block font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fa-solid fa-align-left text-green-500 mr-2"></i>
                    Description courte *
                </label>
                <textarea 
                    id="short_description" 
                    name="short_description" 
                    required
                    rows="3"
                    maxlength="500"
                    class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Résume ton projet en quelques phrases..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Résumé visible sur la page d'accueil (max 500 caractères)</p>
            </div>

            <!-- Description longue (Markdown) -->
            <div>
                <label for="long_description" class="block font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fa-solid fa-file-lines text-purple-500 mr-2"></i>
                    Description détaillée (optionnel)
                </label>
                <textarea 
                    id="long_description" 
                    name="long_description" 
                    rows="8"
                    class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Décris ton projet en détail : fonctionnalités, process, défis rencontrés..."
                    enterkeyhint="enter"></textarea>
                <p class="text-xs text-gray-500 mt-1">Markdown supporté (gras, italique, liens, titres...)</p>
            </div>

            <!-- Lien démo -->
            <div>
                <label for="demo_link" class="block font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fa-solid fa-link text-orange-500 mr-2"></i>
                    Lien vers le site/démo (optionnel)
                </label>
                <input 
                    type="url" 
                    id="demo_link" 
                    name="demo_link" 
                    class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="https://monprojet.com">
            </div>

            <!-- Outils utilisés -->
            <div>
                <label for="tools_used" class="block font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fa-solid fa-toolbox text-yellow-500 mr-2"></i>
                    Outils / Technologies utilisés (optionnel)
                </label>
                <input 
                    type="text" 
                    id="tools_used" 
                    name="tools_used" 
                    class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Ex: Vue.js, Tailwind CSS, Firebase, Figma">
                <p class="text-xs text-gray-500 mt-1">Séparés par des virgules</p>
            </div>

            <!-- Upload d'images -->
            <div>
                <label class="block font-semibold text-gray-900 dark:text-white mb-2">
                    <i class="fa-solid fa-images text-red-500 mr-2"></i>
                    Screenshots / Images (max 5)
                </label>
                
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                    <input 
                        type="file" 
                        id="images" 
                        name="images[]" 
                        multiple
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        class="hidden"
                        onchange="previewImages(this)">
                    
                    <label for="images" class="cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 mb-3 block"></i>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                            Clique ou glisse tes images ici
                        </p>
                        <p class="text-xs text-gray-500">
                            JPEG, PNG, GIF ou WebP - Max 5 Mo par image - Max 5 images
                        </p>
                    </label>
                </div>
                
                <!-- Prévisualisation des images -->
                <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4 hidden"></div>
                
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    La première image sera utilisée comme couverture (modifiable par le reviewer)
                </p>
            </div>

            <!-- Bouton de soumission -->
            <div class="pt-4 flex gap-4">
                <button 
                    type="submit" 
                    id="submitBtn"
                    class="flex-1 px-8 py-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submitText">
                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        Soumettre mon projet
                    </span>
                    <span id="submitLoading" class="hidden">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                        Envoi en cours...
                    </span>
                </button>
                
                <a href="<?=$base?>" class="px-6 py-4 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-xl transition-all">
                    Annuler
                </a>
            </div>
        </div>
    </form>

    <!-- Informations -->
    <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
        <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">
            <i class="fa-solid fa-info-circle mr-2"></i>
            Que se passe-t-il après la soumission ?
        </h3>
        <ol class="text-sm text-blue-800 dark:text-blue-200 space-y-2 list-decimal list-inside">
            <li>Ton projet est mis en file d'attente pour review</li>
            <li>Un reviewer de l'équipe s'attribue ton projet</li>
            <li>Il rédige une revue détaillée (analyse, points forts, suggestions)</li>
            <li>Ton projet est publié avec sa revue sur le site</li>
            <li>Tu reçois une notification par email</li>
        </ol>
    </div>
</div>

<!-- SimpleMDE JS -->
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
<script>
// Initialiser SimpleMDE pour la description longue
const simplemde = new SimpleMDE({
    element: document.getElementById("long_description"),
    spellChecker: false,
    placeholder: "Décris ton projet en détail...",
    toolbar: ["bold", "italic", "heading-2", "heading-3", "|", "quote", "unordered-list", "ordered-list", "|", "link", "|", "preview", "guide"],
    status: false
});

// Prévisualisation des images
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (input.files.length > 0) {
        preview.classList.remove('hidden');
        
        if (input.files.length > 5) {
            alert('Maximum 5 images autorisées');
            input.value = '';
            preview.classList.add('hidden');
            return;
        }
        
        Array.from(input.files).forEach((file, index) => {
            // Vérifier la taille
            if (file.size > 5 * 1024 * 1024) {
                alert(`${file.name} dépasse 5 Mo`);
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border-2 border-slate-200 dark:border-slate-600">
                    ${index === 0 ? '<span class="absolute top-1 left-1 bg-blue-500 text-white text-xs px-2 py-1 rounded">Couverture</span>' : ''}
                    <button type="button" onclick="this.parentElement.remove(); checkImagesCount()" class="absolute top-1 right-1 bg-red-500 text-white w-6 h-6 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="fa-solid fa-times text-xs"></i>
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    } else {
        preview.classList.add('hidden');
    }
}

function checkImagesCount() {
    const preview = document.getElementById('imagePreview');
    if (preview.children.length === 0) {
        preview.classList.add('hidden');
        document.getElementById('images').value = '';
    }
}

// Gestion du formulaire
document.getElementById('submitForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    const text = document.getElementById('submitText');
    const loading = document.getElementById('submitLoading');
    
    btn.disabled = true;
    text.classList.add('hidden');
    loading.classList.remove('hidden');
});
</script>

<?php include 'includes/footer.php'; ?>