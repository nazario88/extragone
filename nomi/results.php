<?php
include '../includes/config.php';

// Récupération du token
$share_token = $_GET['token'] ?? '';

if (empty($share_token)) {
    header('Location: generate');
    exit;
}

// Récupération des données de génération
$stmt = $pdo->prepare('SELECT * FROM nomi_generations WHERE share_token = ?');
$stmt->execute([$share_token]);
$generation = $stmt->fetch();

if (!$generation) {
    header('Location: generate');
    exit;
}

// Décoder les noms générés
$generated_data = json_decode($generation['generated_names'], true);

if (!$generated_data) {
    $_SESSION['error'] = 'Erreur lors du chargement des résultats.';
    header('Location: generate');
    exit;
}

$title = "Résultats de génération — Nomi";
$description = "Découvre les noms générés pour ton projet avec explications et vérification de disponibilité.";

$url_canon = 'https://extrag.one/nomi/results?token=' . $share_token;
$noindex = TRUE; // On indexe pas les résultats

include 'includes/header.php';
?>

<style>
.name-card {
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.name-card:hover {
    transform: translateY(-2px);
}
.domain-btn {
    cursor: pointer;
    font-weight: 600;
    min-width: 40px;
}
.domain-btn.available {
    background-color: #10b981;
    border-color: #059669;
    color: white;
}
.domain-btn.taken {
    background-color: #ef4444;
    border-color: #dc2626;
    color: white;
}
.domain-btn.checking {
    background-color: #f59e0b;
    border-color: #d97706;
    color: white;
    cursor: not-allowed;
}
.no-results {
    grid-column: 1 / -1; /* Prend toute la largeur de la grille */
    padding: 2rem;
    margin: 1rem 0;
}

</style>

<div class="w-full max-w-7xl mx-auto">
    
    <!-- Header -->
    <div class="w-full px-5 py-5">
        <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">
            &rarr; résultats
        </p>
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mt-2 text-3xl font-medium tracking-tight text-gray-950 dark:text-white">
                    20 noms pour ton projet
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-300 max-w-2xl">
                    <?= htmlspecialchars($generation['project_description']) ?>
                </p>
            </div>
            <div class="mt-4 lg:mt-0 flex gap-2">
                <button onclick="shareResults()" class="px-3 py-1 text-sm rounded border bg-orange-500 text-white hover:bg-orange-600 border-orange-700 transition">
                    <i class="fa-solid fa-share mr-2"></i>Partager
                </button>
                <a href="generate" class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                    <i class="fa-solid fa-plus mr-2"></i>Nouvelle génération
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres et actions -->
    <div class="px-5 mb-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-filter text-gray-500"></i>
                    <span class="text-sm font-medium">Filtres :</span>
                </div>
                <button onclick="filterByAvailability('all')" class="filter-btn active px-3 py-1 rounded-lg text-sm transition-all">
                    Tous
                </button>
                <button onclick="filterByAvailability('available')" class="filter-btn px-3 py-1 rounded-lg text-sm transition-all">
                    Domaines disponibles
                </button>
                <button onclick="filterByAvailability('short')" class="filter-btn px-3 py-1 rounded-lg text-sm transition-all">
                    Noms courts
                </button>
                <div class="ml-auto">
                    <button onclick="checkAllDomains()" class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                        <i class="fa-solid fa-globe mr-1"></i>
                        Vérifier les domaines (.com & .fr)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Résultats par catégories -->
    <div class="px-5">
        <?php foreach ($generated_data['categories'] as $index => $category): ?>
        <div class="mb-8">
            <!-- Titre de catégorie -->
            <div class="mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <span class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm mr-3">
                        <?= $index + 1 ?>
                    </span>
                    <?= htmlspecialchars($category['name']) ?>
                </h2>
                <?php if (isset($category['description'])): ?>
                <p class="text-gray-600 dark:text-gray-300 ml-11 mt-1">
                    <?= htmlspecialchars($category['description']) ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Grille des noms -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 ml-11">
                <?php foreach ($category['names'] as $nameData): ?>
                <?php 
                $name = $nameData['name'];
                $explanation = $nameData['explanation'];
                $nameLength = strlen($name);
                ?>
                <div class="name-card bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700 hover:shadow-lg" data-length="<?= $nameLength ?>">
                    <!-- Nom principal -->
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">
                            <?= htmlspecialchars($name) ?>
                        </h3>
                        <button onclick="addToFavorites(<?= htmlspecialchars(json_encode($name), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($category['name']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($explanation), ENT_QUOTES, 'UTF-8') ?>)" 
                                class="favorite-btn text-gray-400 hover:text-red-500 transition-colors" 
                                title="Ajouter aux favoris">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    
                    <!-- Explication -->
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 flex-grow">
                        <?= htmlspecialchars($explanation) ?>
                    </p>
                    
                    <!-- Infos et actions -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500 uppercase">
                            <?= $nameLength ?> lettres
                        </span>
                        <div class="flex items-center gap-1">
                            <!-- Vérification domaines .com et .fr -->
                            <button class="domain-btn text-xs px-2 py-1 rounded border text-gray-600 border-gray-300 dark:text-gray-500 dark:border-gray-500 hover:border-gray-400 transition-all"
                                    onclick="checkDomain('<?= strtolower($name) ?>', 'com', this)"
                                    data-domain="<?= strtolower($name) ?>"
                                    data-tld="com"
                                    title="Vérifier disponibilité .com">
                                .COM
                            </button>
                            <button class="domain-btn text-xs px-2 py-1 rounded border text-gray-600 border-gray-300 dark:text-gray-500 dark:border-gray-500 hover:border-gray-400 transition-all"
                                    onclick="checkDomain('<?= strtolower($name) ?>', 'fr', this)"
                                    data-domain="<?= strtolower($name) ?>"
                                    data-tld="fr"
                                    title="Vérifier disponibilité .fr">
                                .FR
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Message si aucun résultat après filtre -->
                <div class="no-results hidden text-center py-8">
                    <div class="text-gray-400 mb-2">
                        <i class="fa-solid fa-search text-3xl"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">Aucun résultat avec ce filtre 😣.</p>
                    <button onclick="filterByAvailability('all')" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all text-sm">
                        Réinitialiser les filtres
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Section favoris -->
    <div class="px-5 py-8">
        <div id="favorites-section" class="hidden">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                <i class="fa-solid fa-heart text-red-500 mr-2"></i>
                Tes favoris
            </h2>
            <div id="favorites-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Les favoris seront ajoutés ici via JavaScript -->
            </div>
            <div class="mt-4 text-center">
                <button onclick="exportFavorites()" class="px-6 py-3 rounded-xl text-white border bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 hover:border-blue-400 dark:hover:border-blue-950 transition-all">
                    <i class="fa-solid fa-download mr-2"></i>
                    Exporter mes favoris
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de partage -->
<div id="shareModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-md mx-4">
        <h3 class="text-xl font-bold mb-4">Partager ces résultats</h3>
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Lien de partage :</label>
            <div class="flex">
                <input id="shareUrl" type="text" readonly 
                       value="<?= $url_canon ?>"
                       class="flex-1 px-3 py-2 border border-blue-600 rounded-l-lg bg-gray-50 dark:bg-gray-700">
                <button onclick="copyShareUrl()" class="px-4 py-2 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700">
                    <i class="fa-solid fa-copy"></i>
                </button>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="closeShareModal()" class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
let favorites = [];

// Gestion des filtres
function filterByAvailability(filter) {
    // Mise à jour des boutons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
    });
    
    event.target.classList.add('active', 'bg-blue-600', 'text-white');
    event.target.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
    
    const cards = document.querySelectorAll('.name-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        let show = true;
        
        if (filter === 'available') {
            const domainButtons = card.querySelectorAll('.domain-btn');
            show = Array.from(domainButtons).some(btn => btn.classList.contains('available'));
        } else if (filter === 'short') {
            const length = parseInt(card.dataset.length);
            show = length <= 6;
        }
        
        card.style.display = show ? 'block' : 'none';
        if (show) visibleCount++;
    });
    
    // Afficher/masquer le message "aucun résultat"
    const noResultsElements = document.querySelectorAll('.no-results');
    noResultsElements.forEach(element => {
        element.classList.toggle('hidden', visibleCount > 0);
    });
}

// Vérification des domaines
async function checkDomain(domain, tld, button) {
    // Éviter les clics multiples
    if (button.classList.contains('checking')) return;
    
    button.classList.add('checking');
    button.textContent = '...';
    
    try {
        const response = await fetch('https://nomi.extrag.one/functions/check-domain.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ domain: domain + '.' + tld })
        });
        
        const result = await response.json();
        
        button.classList.remove('checking');
        
        if (result.available) {
            button.classList.add('available');
            button.classList.remove('taken');
            button.textContent = '.' + tld.toUpperCase();
            button.title = 'Domaine .' + tld + ' disponible !';
        } else {
            button.classList.add('taken');
            button.classList.remove('available');
            button.textContent = '.' + tld.toUpperCase();
            button.title = 'Domaine .' + tld + ' pris';
        }
    } catch (error) {
        button.classList.remove('checking');
        button.classList.add('taken');
        button.textContent = '.' + tld.toUpperCase();
        button.title = 'Erreur de vérification';
    }
}

// Vérifier tous les domaines
function checkAllDomains() {
    const domainButtons = document.querySelectorAll('.domain-btn');
    domainButtons.forEach((button, index) => {
        setTimeout(() => {
            const domain = button.dataset.domain;
            const tld = button.dataset.tld;
            checkDomain(domain, tld, button);
        }, index * 300); // Délai pour éviter de surcharger l'API
    });
}

// Gestion des favoris
function addToFavorites(name, category, explanation) {
    const existing = favorites.find(f => f.name === name);
    if (existing) return;
    
    favorites.push({ name, category, explanation });
    updateFavoritesDisplay();
    
    // Mise à jour visuelle du bouton
    event.target.classList.remove('far');
    event.target.classList.add('fas', 'text-red-500');
}

function updateFavoritesDisplay() {
    const section = document.getElementById('favorites-section');
    const list = document.getElementById('favorites-list');
    
    if (favorites.length === 0) {
        section.classList.add('hidden');
        return;
    }
    
    section.classList.remove('hidden');
    
    list.innerHTML = favorites.map(fav => `
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <div class="flex items-start justify-between mb-2">
                <h4 class="font-bold text-lg">${fav.name}</h4>
                <button onclick="removeFromFavorites('${fav.name}')" class="text-red-500 hover:text-red-700">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">${fav.explanation}</p>
            <span class="text-xs text-gray-500">${fav.category}</span>
        </div>
    `).join('');
}

function removeFromFavorites(name) {
    favorites = favorites.filter(f => f.name !== name);
    updateFavoritesDisplay();
    
    // Remettre le bouton heart à vide
    const cards = document.querySelectorAll('.name-card');
    cards.forEach(card => {
        const cardName = card.querySelector('h3').textContent.trim();
        if (cardName === name) {
            const heartBtn = card.querySelector('.favorite-btn i');
            heartBtn.classList.remove('fas', 'text-red-500');
            heartBtn.classList.add('far');
        }
    });
}

// Export des favoris
function exportFavorites() {
    if (favorites.length === 0) return;
    
    const content = favorites.map(fav => 
        `${fav.name} - ${fav.explanation} (${fav.category})`
    ).join('\n');
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'mes-noms-favoris.txt';
    a.click();
    URL.revokeObjectURL(url);
}

// Partage
function shareResults() {
    document.getElementById('shareModal').classList.remove('hidden');
}

function closeShareModal() {
    document.getElementById('shareModal').classList.add('hidden');
}

function copyShareUrl() {
    const input = document.getElementById('shareUrl');
    input.select();
    document.execCommand('copy');
    
    const btn = event.target;
    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
    setTimeout(() => {
        btn.innerHTML = '<i class="fa-solid fa-copy"></i>';
    }, 2000);
}
</script>

<?php
include 'includes/footer.php';
?>