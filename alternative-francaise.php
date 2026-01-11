<?php
include 'includes/config.php';

// Récupérer le slug de l'outil depuis l'URL
$tool_slug = $_GET['outil'] ?? '';

if (empty($tool_slug)) {
    header('Location: https://www.extrag.one/outils');
    exit;
}

// Récupérer l'outil
$stmt = $pdo->prepare('
    SELECT t.*, c.nom as categorie_nom, c.slug as categorie_slug
    FROM extra_tools t
    LEFT JOIN extra_tools_categories c ON t.categorie_id = c.id
    WHERE t.slug = ? AND t.is_valid = 1
');
$stmt->execute([$tool_slug]);
$tool = $stmt->fetch();

if (!$tool) {
    errorPage("Outil non trouvé");
}

// Récupérer les alternatives FRANÇAISES uniquement
$stmt = $pdo->prepare('
    SELECT t2.* 
    FROM extra_alternatives a
    JOIN extra_tools t2 ON a.id_alternative = t2.id
    WHERE a.id_outil = ? AND t2.is_french = 1 AND t2.is_valid = 1
    ORDER BY t2.hits DESC
');
$stmt->execute([$tool['id']]);
$alternatives = $stmt->fetchAll();

// Récupérer d'autres outils de la même catégorie (français)
$stmt = $pdo->prepare('
    SELECT * FROM extra_tools
    WHERE categorie_id = ? AND is_french = 1 AND is_valid = 1 AND id != ?
    ORDER BY hits DESC
    LIMIT 6
');
$stmt->execute([$tool['categorie_id'], $tool['id']]);
$same_category = $stmt->fetchAll();

/* SEO */
$title = "Alternative française à " . $tool['nom'] . " en " . date('Y') . " — eXtragone";
$description = "Découvrez les meilleures alternatives françaises à " . $tool['nom'] . ". Solutions conformes RGPD, hébergées en France, conformes aux réglementations européennes.";
$url_canon = 'https://www.extrag.one/alternative-francaise-' . $tool_slug;
$image_seo = $tool['logo'];

include 'includes/header.php';
?>

<div class="w-full max-w-6xl mx-auto px-5 py-8">
    
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="" class="hover:text-blue-500">Accueil</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <a href="outils" class="hover:text-blue-500">Outils</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span>Alternative à <?= htmlspecialchars($tool['nom']) ?></span>
    </div>

    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-8 mb-8 border-2 border-blue-200 dark:border-blue-700">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <img src="<?= htmlspecialchars($tool['logo']) ?>" 
                 alt="<?= htmlspecialchars($tool['nom']) ?>"
                 class="w-24 h-24 object-contain">
            
            <div class="flex-1 text-center md:text-left">
                <h1 class="text-3xl md:text-4xl font-bold mb-3">
                    Alternative française à <?= htmlspecialchars($tool['nom']) ?>
                    <?= $flag_FR ?>
                </h1>
                <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                    <?= count($alternatives) ?> solution<?= count($alternatives) > 1 ? 's' : '' ?> française<?= count($alternatives) > 1 ? 's' : '' ?> 
                    conforme<?= count($alternatives) > 1 ? 's' : '' ?> RGPD pour remplacer <?= htmlspecialchars($tool['nom']) ?>
                </p>
                <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                    <span class="px-3 py-1 bg-blue-500 text-white text-sm rounded-full">
                        <?= htmlspecialchars($tool['categorie_nom']) ?>
                    </span>
                    <?php if ($tool['is_free']): ?>
                    <span class="px-3 py-1 bg-green-500 text-white text-sm rounded-full">
                        Alternatives gratuites disponibles
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pourquoi chercher une alternative française ? -->
    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 mb-8 border border-slate-200 dark:border-slate-700">
        <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-blue-500"></i>
            Pourquoi choisir une alternative française à <?= htmlspecialchars($tool['nom']) ?> ?
        </h2>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div class="flex gap-3">
                <i class="fa-solid fa-check text-green-500 mt-1"></i>
                <div>
                    <strong>Conformité RGPD totale</strong>
                    <p class="text-gray-600 dark:text-gray-400">Données hébergées en France et en Europe</p>
                </div>
            </div>
            <div class="flex gap-3">
                <i class="fa-solid fa-check text-green-500 mt-1"></i>
                <div>
                    <strong>Support en français</strong>
                    <p class="text-gray-600 dark:text-gray-400">Équipes francophones disponibles</p>
                </div>
            </div>
            <div class="flex gap-3">
                <i class="fa-solid fa-check text-green-500 mt-1"></i>
                <div>
                    <strong>Soutien à l'économie locale</strong>
                    <p class="text-gray-600 dark:text-gray-400">Favoriser les entreprises françaises</p>
                </div>
            </div>
            <div class="flex gap-3">
                <i class="fa-solid fa-check text-green-500 mt-1"></i>
                <div>
                    <strong>Souveraineté numérique</strong>
                    <p class="text-gray-600 dark:text-gray-400">Indépendance technologique européenne</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des alternatives -->
    <?php if (empty($alternatives)): ?>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-8 text-center border border-yellow-200 dark:border-yellow-800">
            <i class="fa-solid fa-info-circle text-4xl text-yellow-500 mb-4"></i>
            <h3 class="text-xl font-bold mb-2">Aucune alternative française référencée</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Nous n'avons pas encore d'alternative française pour <?= htmlspecialchars($tool['nom']) ?>.
            </p>
            <a href="ajouter?site=<?= urlencode($tool['url']) ?>" 
               class="inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                <i class="fa-solid fa-plus mr-2"></i>
                Proposer une alternative
            </a>
        </div>
    <?php else: ?>
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
            <?= $flag_FR ?>
            Les meilleures alternatives françaises
        </h2>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <?php foreach ($alternatives as $alt): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="p-6">
                    <img src="<?= htmlspecialchars($alt['logo']) ?>" 
                         alt="<?= htmlspecialchars($alt['nom']) ?>"
                         class="w-16 h-16 object-contain mb-4 mx-auto">
                    
                    <h3 class="text-xl font-bold text-center mb-2">
                        <?= htmlspecialchars($alt['nom']) ?>
                    </h3>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-4">
                        <?= htmlspecialchars($alt['description']) ?>
                    </p>
                    
                    <div class="flex flex-wrap gap-2 justify-center mb-4">
                        <?php if ($alt['is_free']): ?>
                        <span class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 text-xs rounded-full">
                            Gratuit
                        </span>
                        <?php endif; ?>
                        <?php if ($alt['is_paid']): ?>
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 text-xs rounded-full">
                            Payant
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <a href="outil/<?= $alt['slug'] ?>" 
                       class="block text-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition">
                        <i class="fa-solid fa-arrow-right mr-2"></i>
                        Découvrir
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Autres outils de la même catégorie -->
    <?php if (!empty($same_category)): ?>
    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <i class="fa-solid fa-layer-group text-blue-500"></i>
            Autres outils français de la catégorie "<?= htmlspecialchars($tool['categorie_nom']) ?>"
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php foreach ($same_category as $other): ?>
            <a href="outil/<?= $other['slug'] ?>" 
               class="bg-white dark:bg-slate-800 rounded-lg p-4 text-center hover:shadow-md transition group">
                <img src="<?= htmlspecialchars($other['logo']) ?>" 
                     alt="<?= htmlspecialchars($other['nom']) ?>"
                     class="w-12 h-12 object-contain mx-auto mb-2">
                <p class="text-sm font-medium group-hover:text-blue-500 transition">
                    <?= htmlspecialchars($other['nom']) ?>
                </p>
            </a>
            <?php endforeach; ?>
        </div>
        
        <a href="outils/categorie/<?= $tool['categorie_slug'] ?>" 
           class="inline-block mt-4 text-sm text-blue-500 hover:underline">
            Voir tous les outils <?= htmlspecialchars($tool['categorie_nom']) ?> →
        </a>
    </div>
    <?php endif; ?>

    <!-- CTA -->
    <div class="mt-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl p-8 text-center text-white">
        <h2 class="text-2xl font-bold mb-3">Vous connaissez une autre alternative ?</h2>
        <p class="mb-6 opacity-90">
            Aidez la communauté en proposant d'autres solutions françaises
        </p>
        <a href="ajouter?site=<?= urlencode($tool['url']) ?>" 
           class="inline-block px-8 py-3 bg-white text-blue-500 font-bold rounded-lg hover:bg-gray-100 transition">
            <i class="fa-solid fa-plus mr-2"></i>
            Proposer une alternative
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>