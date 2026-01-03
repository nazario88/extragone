<!-- Modal : Proposition de laisser un avis détaillé -->
<div id="ratingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md mx-4 overflow-hidden border border-slate-200 dark:border-slate-700 animate-fadeIn">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-500 p-6 text-white text-center">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-star text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold mb-2">Merci pour votre note !</h3>
            <p class="text-blue-100 text-sm">Votre avis de <span id="modalRating" class="font-bold">5</span>/5 a été enregistré</p>
        </div>
        
        <!-- Body -->
        <div class="p-6">
            <p class="text-center text-gray-700 dark:text-gray-300 mb-6">
                Voulez-vous <strong>partager votre expérience</strong> avec la communauté ?<br>
                <span class="text-sm text-gray-500">Votre avis détaillé aidera les autres utilisateurs 🙂</span>
            </p>
            
            <!-- Actions -->
            <div class="flex flex-col gap-3">
                <a href="connexion?redirect=outil/<?= htmlspecialchars($data_outil['slug']) ?>" 
                   class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all text-center shadow-lg hover:shadow-xl">
                    <i class="fa-solid fa-comment-dots mr-2"></i>
                    Oui, laisser un avis détaillé
                </a>
                
                <button 
                    onclick="closeRatingModal()" 
                    class="w-full px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-200 font-medium rounded-xl transition-all">
                    Non merci, peut-être plus tard
                </button>
            </div>
            
            <p class="text-xs text-gray-500 text-center mt-4">
                💡 Vous devez être connecté pour laisser un avis détaillé
            </p>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { 
        opacity: 0; 
        transform: scale(0.95) translateY(-10px); 
    }
    to { 
        opacity: 1; 
        transform: scale(1) translateY(0); 
    }
}
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>

<script>
function openRatingModal(rating) {
    document.getElementById('modalRating').textContent = rating;
    document.getElementById('ratingModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRatingModal() {
    document.getElementById('ratingModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Fermer avec ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRatingModal();
    }
});

// Fermer en cliquant sur le fond
document.getElementById('ratingModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRatingModal();
    }
});
</script>