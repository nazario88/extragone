<!-- Modal : Proposer un guide pour un outil -->
<div id="addGuideModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-lg mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-500 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-book-open text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Proposer un guide</h3>
                        <p class="text-blue-100 text-sm">Partagez votre expertise !</p>
                    </div>
                </div>
                <button onclick="closeAddGuideModal()" class="text-white/80 hover:text-white transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Body -->
        <form id="addGuideForm" onsubmit="submitAddGuide(event)" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="tool_id" id="guideToolId" value="<?= $data_outil['id'] ?>">
            
            <!-- Titre du guide -->
            <div class="mb-4">
                <label for="guideTitle" class="block font-semibold mb-2">
                    Titre du guide *
                </label>
                <input 
                    type="text" 
                    id="guideTitle" 
                    name="title" 
                    required
                    minlength="10"
                    maxlength="200"
                    placeholder="Ex: Guide complet pour débutants sur <?= htmlspecialchars($data_outil['nom']) ?>"
                    class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Entre 10 et 200 caractères</p>
            </div>
            
            <!-- URL du guide -->
            <div class="mb-4">
                <label for="guideUrl" class="block font-semibold mb-2">
                    URL du guide *
                </label>
                <input 
                    type="url" 
                    id="guideUrl" 
                    name="url" 
                    required
                    placeholder="https://monsite.com/mon-guide"
                    class="w-full px-4 py-3 rounded-xl text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Lien vers votre guide (article, vidéo, tutoriel...)</p>
            </div>
            
            <!-- Info modération -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-info-circle text-blue-500 mt-0.5"></i>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-semibold mb-1">Votre guide sera modéré</p>
                        <p>Pour garantir la qualité des contenus, chaque guide est vérifié avant publication. Vous serez notifié dès validation !</p>
                    </div>
                </div>
            </div>
            
            <!-- Message de retour -->
            <div id="guideMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>
            
            <!-- Actions -->
            <div class="flex gap-3">
                <button 
                    type="submit" 
                    id="submitGuideBtn"
                    class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Soumettre le guide
                </button>
                
                <button 
                    type="button" 
                    onclick="closeAddGuideModal()" 
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-200 font-medium rounded-xl transition-all">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Ouvrir la modal
function openAddGuideModal() {
    document.getElementById('addGuideModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Fermer la modal
function closeAddGuideModal() {
    document.getElementById('addGuideModal').classList.add('hidden');
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('addGuideForm').reset();
    document.getElementById('guideMessage').classList.add('hidden');
}

// Fermer avec ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddGuideModal();
    }
});

// Fermer en cliquant sur le fond
document.getElementById('addGuideModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddGuideModal();
    }
});

// Soumettre le formulaire
async function submitAddGuide(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitGuideBtn');
    const messageDiv = document.getElementById('guideMessage');
    
    // Désactiver le bouton
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Envoi en cours...';
    
    try {
        const response = await fetch('includes/guides/add-guide.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Succès
            messageDiv.className = 'mb-4 p-3 rounded-lg text-sm bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200';
            messageDiv.textContent = data.message;
            messageDiv.classList.remove('hidden');
            
            // Reset form
            form.reset();
            
            // Fermer après 2 secondes et recharger
            setTimeout(() => {
                closeAddGuideModal();
                location.reload();
            }, 2000);
            
        } else {
            // Erreur
            messageDiv.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200';
            messageDiv.textContent = data.error || 'Erreur lors de l\'envoi';
            messageDiv.classList.remove('hidden');
            
            // Réactiver le bouton
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i>Soumettre le guide';
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        messageDiv.className = 'mb-4 p-3 rounded-lg text-sm bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200';
        messageDiv.textContent = 'Erreur réseau. Veuillez réessayer.';
        messageDiv.classList.remove('hidden');
        
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i>Soumettre le guide';
    }
}
</script>
