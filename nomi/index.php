<?php
include '../includes/config.php';

$title = "Trouve le nom parfait pour ton projet — Nomi by eXtragone";
$description = "Décris ton idée en quelques mots et laisse Nomi générer des dizaines de propositions créatives, avec explications et vérification de disponibilité.";

$url_canon = 'https://nomi.extrag.one';

include 'includes/header.php';
?>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.6s ease-out forwards;
}

.animate-delay-200 {
    animation-delay: 0.2s;
}

.animate-delay-400 {
    animation-delay: 0.4s;
}

.word-animation {
    display: inline-block;
    min-width: 120px;
    text-align: left;
}
</style>

<div class="w-full lg:w-3/4 xl:w-2/3 2xl:w-1/2 mx-auto">
    <!-- Hero Section -->
    <div class="px-5 py-12 text-center">
        <!-- Logo/Branding -->
        <div class="mb-8 opacity-0 animate-fadeInUp">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">
                <span class="text-blue-600">Nomi</span>
                <span class="text-gray-600 dark:text-gray-400 text-lg">by eXtragone</span>
            </h1>
        </div>

        <!-- Titre principal -->
        <div class="mb-6 opacity-0 animate-fadeInUp animate-delay-200">
            <h2 class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                Trouve le nom parfait pour ton projet
            </h2>
            
            <!-- Animation des mots -->
            <div class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 mb-2">
                Comme <span class="word-animation text-blue-600 font-semibold" id="animatedWord">Nova</span>
            </div>
        </div>

        <!-- Sous-titre -->
        <div class="mb-8 opacity-0 animate-fadeInUp animate-delay-400">
            <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                Décris ton idée en quelques mots et laisse Nomi générer des dizaines de propositions créatives, 
                avec explications et vérification de disponibilité.
            </p>
        </div>

        <!-- CTA Principal -->
        <div class="mb-8 opacity-0 animate-fadeInUp animate-delay-400">
            <a href="generate" class="inline-block px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-lg rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-magic mr-2"></i>
                Générer des noms
            </a>
        </div>

        <!-- Preuve sociale -->
        <div class="opacity-0 animate-fadeInUp animate-delay-400">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Propulsé par l'IA et pensé pour les créateurs, freelances et startups
            </p>
        </div>
    </div>

    <!-- Séparateur -->
    <hr class="my-16 h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

    <!-- Section explicative -->
    <div class="px-5 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Étape 1 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-edit text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">1. Décris ton projet</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">
                    Une phrase simple sur l'objectif, quelques mots-clés et tes préférences de style
                </p>
            </div>

            <!-- Étape 2 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-robot text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">2. L'IA génère</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">
                    50 propositions créatives organisées par thèmes avec explications détaillées
                </p>
            </div>

            <!-- Étape 3 -->
            <div class="text-center p-6">
                <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-globe text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">3. Vérifie & choisis</h3>
                <p class="text-gray-600 dark:text-gray-300 text-sm">
                    Disponibilité des domaines, concurrence et partage facile de tes favoris
                </p>
            </div>
        </div>
    </div>

    <!-- CTA secondaire -->
    <div class="text-center py-8">
        <a href="generate" class="px-6 py-3 bg-white dark:bg-slate-800 border-2 border-blue-600 text-blue-600 font-semibold rounded-xl hover:bg-blue-50 dark:hover:bg-slate-700 transition-all duration-300">
            Commencer maintenant
            <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>

<script>
// Animation des mots qui changent
const words = [
    'Nova', 'Kairo', 'Plume', 'Axio', 'Zenyx', 'Kora', 'Flux', 'Vibe',
    'Pixel', 'Echo', 'Spark', 'Flow', 'Edge', 'Core', 'Sync', 'Wave'
];

let currentIndex = 0;
const animatedWord = document.getElementById('animatedWord');

function changeWord() {
    // Fade out
    animatedWord.style.opacity = '0';
    animatedWord.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
        currentIndex = (currentIndex + 1) % words.length;
        animatedWord.textContent = words[currentIndex];
        
        // Fade in
        animatedWord.style.opacity = '1';
        animatedWord.style.transform = 'translateY(0)';
    }, 300);
}

// Démarrer l'animation après le chargement
document.addEventListener('DOMContentLoaded', function() {
    animatedWord.style.transition = 'all 0.3s ease';
    
    // Changer de mot toutes les 2 secondes
    setInterval(changeWord, 2000);
});
</script>

<?php
include 'includes/footer.php';
?>