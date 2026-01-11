<?php
// ===============================================
// TEMPLATE : alternative-francaise-[SLUG].php
// Pages alternatives enrichies avec contenu DB
// ===============================================

include 'includes/config.php';

// Récupération du slug depuis l'URL
$slug_parent = isset($_GET['nom']) ? $_GET['nom'] : null;

if (!$slug_parent) {
    errorPage("Outil non spécifié");
}

// ===============================================
// 1. RÉCUPÉRATION DE L'OUTIL PARENT
// ===============================================

$stmt = $pdo->prepare('SELECT * FROM extra_tools WHERE slug = ? AND is_valid = 1');
$stmt->execute([$slug_parent]);
$tool_parent = $stmt->fetch();

if (!$tool_parent) {
    errorPage("Outil non trouvé");
}

// ===============================================
// 2. RÉCUPÉRATION DES ALTERNATIVES FRANÇAISES
// ===============================================

$stmt = $pdo->prepare('
    SELECT C.* 
    FROM extra_alternatives A
    INNER JOIN extra_tools C ON C.id = A.id_alternative
    WHERE A.id_outil = ? AND C.is_french = 1
    ORDER BY C.nom ASC
');
$stmt->execute([$tool_parent['id']]);
$alternatives = $stmt->fetchAll();

if (count($alternatives) < 3) {
    errorPage("Pas assez d'alternatives françaises pour cette page");
}

// ===============================================
// 3. RÉCUPÉRATION DU CONTENU ENRICHI (DB)
// ===============================================

$stmt = $pdo->prepare('
    SELECT * FROM extra_alternatives_content 
    WHERE slug = ? AND is_active = 1
');
$stmt->execute([$slug_parent]);
$custom_content = $stmt->fetch();

// ===============================================
// 4. GÉNÉRATION DU CONTENU (custom OU auto)
// ===============================================

// --- INTRODUCTION ---
if ($custom_content && !empty($custom_content['intro_text'])) {
    $intro_html = nl2br(htmlspecialchars($custom_content['intro_text']));
} else {
    // Fallback : génération automatique
    $intro_html = generateDefaultIntro($tool_parent, count($alternatives));
}

// --- TABLEAU COMPARATIF ---
if ($custom_content && !empty($custom_content['comparison_table_json'])) {
    $comparison_data = json_decode($custom_content['comparison_table_json'], true);
} else {
    // Fallback : génération automatique depuis les données des outils
    $comparison_data = generateDefaultComparison($alternatives);
}

// --- DESCRIPTIONS DÉTAILLÉES ---
if ($custom_content && !empty($custom_content['tools_details_json'])) {
    $tools_details = json_decode($custom_content['tools_details_json'], true);
} else {
    // Fallback : génération automatique
    $tools_details = generateDefaultToolsDetails($alternatives);
}

// --- FAQ ---
if ($custom_content && !empty($custom_content['faq_json'])) {
    $faq_data = json_decode($custom_content['faq_json'], true);
} else {
    // Fallback : FAQ générique
    $faq_data = generateDefaultFAQ($tool_parent, $alternatives);
}

// ===============================================
// 5. SEO & META
// ===============================================

$title = ($custom_content && $custom_content['meta_title']) 
    ? $custom_content['meta_title'] 
    : count($alternatives) . " alternatives françaises à " . $tool_parent['nom'];

$description = ($custom_content && $custom_content['meta_description'])
    ? $custom_content['meta_description']
    : "Découvrez " . count($alternatives) . " alternatives françaises à " . $tool_parent['nom'] . " : " . implode(', ', array_slice(array_column($alternatives, 'nom'), 0, 3));

$url_canon = 'https://www.extrag.one/alternative-francaise/' . $slug_parent;
$image_seo = $tool_parent['screenshot'] ?: $tool_parent['logo'];

include 'includes/header.php';
?>

<!-- ===============================================
     CONTENU DE LA PAGE
     =============================================== -->

<div class="w-full max-w-5xl mx-auto px-5 py-8">
    
    <!-- Breadcrumb -->
    <nav class="text-sm mb-6 text-gray-600 dark:text-gray-400">
        <a href="<?=$base?>" class="hover:text-blue-500">Accueil</a>
        <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
        <a href="outils" class="hover:text-blue-500">Outils</a>
        <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
        <span>Alternatives à <?= htmlspecialchars($tool_parent['nom']) ?></span>
    </nav>

    <!-- Titre principal -->
    <h1 class="text-4xl font-bold mb-4">
        <?= count($alternatives) ?> alternatives françaises à <?= htmlspecialchars($tool_parent['nom']) ?>
    </h1>

    <!-- ===============================================
         SECTION 1 : INTRODUCTION
         =============================================== -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 mb-8">
        <h2 class="text-2xl font-bold mb-4 flex items-center">
            <i class="fa-solid fa-lightbulb text-yellow-500 mr-3"></i>
            Pourquoi chercher une alternative française ?
        </h2>
        <div class="text-gray-700 dark:text-gray-300 leading-relaxed">
            <?= $intro_html ?>
        </div>
    </div>

    <!-- ===============================================
         SECTION 2 : TABLEAU COMPARATIF
         =============================================== -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fa-solid fa-table text-blue-500 mr-3"></i>
            Tableau comparatif des alternatives
        </h2>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-700">
                        <th class="p-3 text-left font-bold">Outil</th>
                        <th class="p-3 text-left font-bold">Tarifs</th>
                        <th class="p-3 text-left font-bold">Fonctionnalités clés</th>
                        <th class="p-3 text-left font-bold">Idéal pour</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comparison_data as $item): ?>
                    <tr class="border-b border-slate-200 dark:border-slate-600">
                        <td class="p-3 font-semibold">
                            <a href="outil/<?= $item['slug'] ?>" class="text-blue-500 hover:underline">
                                <?= htmlspecialchars($item['tool']) ?>
                            </a>
                        </td>
                        <td class="p-3"><?= htmlspecialchars($item['pricing']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($item['features']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($item['best_for']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===============================================
         SECTION 3 : DESCRIPTIONS DÉTAILLÉES
         =============================================== -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fa-solid fa-list text-green-500 mr-3"></i>
            Les <?= count($alternatives) ?> alternatives françaises en détail
        </h2>
        
        <?php foreach ($tools_details as $index => $detail): ?>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 mb-6">
            
            <!-- En-tête outil -->
            <div class="flex items-center gap-4 mb-4">
                <img src="<?= htmlspecialchars($detail['logo']) ?>" 
                     alt="<?= htmlspecialchars($detail['name']) ?>"
                     class="w-16 h-16 object-contain rounded-lg">
                <div>
                    <h3 class="text-xl font-bold">
                        <?= ($index + 1) ?>. <?= htmlspecialchars($detail['name']) ?>
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <?= htmlspecialchars($detail['tagline']) ?>
                    </p>
                </div>
            </div>

            <!-- Points forts -->
            <div class="mb-4">
                <h4 class="font-bold mb-2 flex items-center">
                    <i class="fa-solid fa-star text-yellow-500 mr-2"></i>
                    Points forts
                </h4>
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
                    <?php foreach ($detail['strengths'] as $strength): ?>
                    <li><?= htmlspecialchars($strength) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Cas d'usage -->
            <div class="mb-4">
                <h4 class="font-bold mb-2 flex items-center">
                    <i class="fa-solid fa-check-circle text-green-500 mr-2"></i>
                    Cas d'usage
                </h4>
                <p class="text-gray-700 dark:text-gray-300">
                    <?= nl2br(addCssClasses($detail['use_cases'])) ?>
                </p>
            </div>

            <!-- Tarifs -->
            <div class="mb-4">
                <h4 class="font-bold mb-2 flex items-center">
                    <i class="fa-solid fa-euro-sign text-blue-500 mr-2"></i>
                    Tarifs
                </h4>
                <p class="text-gray-700 dark:text-gray-300">
                    <?= htmlspecialchars($detail['pricing_details']) ?>
                </p>
            </div>

            <!-- CTA -->
            <a href="outil/<?= htmlspecialchars($detail['slug']) ?>" 
               class="inline-block px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
                <i class="fa-solid fa-arrow-right mr-2"></i>
                Découvrir <?= htmlspecialchars($detail['name']) ?>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ===============================================
         SECTION 4 : FAQ (avec Schema.org)
         =============================================== -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fa-solid fa-question-circle text-purple-500 mr-3"></i>
            Questions fréquentes
        </h2>
        
        <div class="space-y-4">
            <?php foreach ($faq_data as $faq): ?>
            <div class="border-b border-slate-200 dark:border-slate-600 pb-4 last:border-0">
                <h3 class="font-bold text-lg mb-2">
                    <?= htmlspecialchars($faq['question']) ?>
                </h3>
                <p class="text-gray-700 dark:text-gray-300">
                    <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ===============================================
     SCHEMA.ORG - FAQ PAGE
     =============================================== -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php foreach ($faq_data as $i => $faq): ?>
    {
      "@type": "Question",
      "name": "<?= addslashes($faq['question']) ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?= addslashes(strip_tags($faq['answer'])) ?>"
      }
    }<?= $i < count($faq_data) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ]
}
</script>

<?php
// ===============================================
// FONCTIONS DE GÉNÉRATION AUTOMATIQUE (FALLBACK)
// ===============================================

/**
 * Génère l'introduction par défaut
 */
function generateDefaultIntro($tool, $nb_alternatives) {
    $nom = htmlspecialchars($tool['nom']);
    $categorie = htmlspecialchars($tool['categorie_nom'] ?? 'productivité');
    
    return "<p><strong>{$nom}</strong> est un outil de {$categorie} très utilisé dans le monde professionnel. 
    Cependant, de nombreuses entreprises cherchent aujourd'hui des <strong>alternatives françaises</strong> 
    pour plusieurs raisons :</p>
    
    <ul class='list-disc list-inside space-y-2 mt-3'>
        <li><strong>Conformité RGPD</strong> : Les données restent hébergées en France ou en Europe</li>
        <li><strong>Souveraineté numérique</strong> : Soutenir l'écosystème tech français</li>
        <li><strong>Support en français</strong> : Service client dans votre langue</li>
        <li><strong>Proximité culturelle</strong> : Outils pensés pour le marché français</li>
    </ul>
    
    <p class='mt-3'>Nous avons identifié <strong>{$nb_alternatives} alternatives françaises sérieuses</strong> 
    qui peuvent remplacer {$nom}. Voici notre comparatif détaillé.</p>";
}

/**
 * Génère le tableau comparatif par défaut
 */
function generateDefaultComparison($alternatives) {
    $comparison = [];
    
    foreach ($alternatives as $alt) {
        $pricing = 'Non communiqué';
        if ($alt['is_free'] && $alt['is_paid']) {
            $pricing = 'Freemium';
        } elseif ($alt['is_free']) {
            $pricing = 'Gratuit';
        } elseif ($alt['is_paid']) {
            $pricing = 'Payant';
        }
        
        $comparison[] = [
            'tool' => $alt['nom'],
            'slug' => $alt['slug'],
            'pricing' => $pricing,
            'features' => substr($alt['description'], 0, 80) . '...',
            'best_for' => 'Entreprises et professionnels'
        ];
    }
    
    return $comparison;
}

/**
 * Génère les descriptions détaillées par défaut
 */
function generateDefaultToolsDetails($alternatives) {
    $details = [];
    
    foreach ($alternatives as $alt) {
        $details[] = [
            'name' => $alt['nom'],
            'slug' => $alt['slug'],
            'logo' => $alt['logo'],
            'tagline' => $alt['description'],
            'strengths' => [
                '🇫🇷 Solution française et souveraine',
                '✅ Conforme RGPD',
                '🎯 ' . ($alt['description_longue'] ? substr(strip_tags($alt['description_longue']), 0, 60) . '...' : 'Interface intuitive')
            ],
            'use_cases' => $alt['description_longue'] ?: $alt['description'],
            'pricing_details' => $alt['is_free'] ? 'Version gratuite disponible' : 'Tarifs sur demande'
        ];
    }
    
    return $details;
}

/**
 * Génère la FAQ par défaut
 */
function generateDefaultFAQ($tool, $alternatives) {
    $nom = $tool['nom'];
    $noms_alts = implode(', ', array_column($alternatives, 'nom'));
    
    return [
        [
            'question' => "Quelle est la meilleure alternative française à {$nom} ?",
            'answer' => "La meilleure alternative dépend de vos besoins spécifiques. Parmi les solutions françaises, nous recommandons particulièrement {$alternatives[0]['nom']} pour sa complétude et {$alternatives[1]['nom']} pour sa simplicité d'utilisation."
        ],
        [
            'question' => "Ces alternatives sont-elles conformes RGPD ?",
            'answer' => "Oui, toutes les alternatives listées ({$noms_alts}) sont des solutions françaises ou européennes, conformes au RGPD. Vos données restent hébergées en France ou en Europe."
        ],
        [
            'question' => "Puis-je migrer facilement depuis {$nom} ?",
            'answer' => "La plupart de ces alternatives proposent des outils d'import pour faciliter la migration. Nous vous recommandons de contacter leur support pour un accompagnement personnalisé."
        ],
        [
            'question' => "Y a-t-il des alternatives gratuites ?",
            'answer' => count(array_filter($alternatives, fn($a) => $a['is_free'])) > 0 
                ? "Oui, certaines alternatives proposent des versions gratuites ou freemium. Consultez le tableau comparatif ci-dessus pour plus de détails."
                : "La plupart de ces alternatives sont payantes, mais offrent souvent des périodes d'essai gratuites."
        ]
    ];
}

include 'includes/footer.php';
?>