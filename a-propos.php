<?php
include 'includes/config.php';

// Récupérer les statistiques en temps réel
$stmt = $pdo->query('SELECT COUNT(*) FROM extra_tools WHERE is_valid = 1');
$total_tools = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM extra_tools WHERE is_valid = 1 AND is_french = 1');
$french_tools = $stmt->fetchColumn();

$french_percentage = $total_tools > 0 ? round(($french_tools / $total_tools) * 100) : 0;

$stmt = $pdo->query('SELECT COUNT(*) FROM extra_tools_categories');
$total_categories = $stmt->fetchColumn();

/* SEO */
$title = "À propos d'eXtragone — La plateforme des alternatives françaises";
$description = "Découvrez l'histoire d'eXtragone, plateforme indépendante de référencement d'outils numériques français. Créée par Jérémie G., passionné d'outils digitaux et défenseur de la French Tech.";
$url_canon = 'https://www.extrag.one/a-propos';

include 'includes/header.php';
?>

<div class="w-full max-w-4xl mx-auto px-5 py-12">
    
    <!-- Header -->
    <div class="text-center mb-12">
        <img src="assets/img/logo.webp" alt="Logo eXtragone" class="w-24 h-24 mx-auto mb-6">
        <h1 class="text-4xl font-bold mb-4">À propos d'eXtragone</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">
            La plateforme indépendante pour découvrir les alternatives françaises aux outils numériques
        </p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-3 gap-6 mb-12">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold mb-2"><?= number_format($total_tools, 0, ',', ' ') ?></div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Outils référencés</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold mb-2"><?= number_format($french_tools, 0, ',', ' ') ?></div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Outils français (<?= $french_percentage ?>%)</div>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 text-center">
            <div class="text-3xl font-bold mb-2">2 000+</div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Visites mensuelles</div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="space-y-12">
        
        <!-- Qui je suis -->
        <section class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700">
            <h2 class="md:text-lg uppercase tracking-wider font-bold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user"></i>
                Qui suis-je ?
            </h2>
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <div class="flex-1">
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        Passionné d'outils numériques et défenseur de la French Tech. 
                        Je développe, pilote et mets en place des outils digitaux régulièrement dans mes projets personnels et professionnels.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        J'adore découvrir de nouveaux outils et partager mes découvertes sur mon site 
                        <a href="https://www.innospira.fr" target="_blank" class="border-b-2 border-blue-500 hover:border-dotted hover:text-blue-500 transition-colors duration-300">InnoSpira.fr</a>, 
                        où je teste des outils IA, partage des méthodes et retours d'expérience concrets.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        Ma mission avec <strong>eXtragone</strong> : mettre en avant les alternatives françaises 
                        et favoriser notre écosystème tech local face aux géants américains.
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4">
                        N'hésitez pas à <a href="contact" class="border-b-2 border-blue-500 hover:border-dotted hover:text-blue-500 transition-colors duration-300">me contacter</a> 
                        pour toute question ou suggestion !
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mt-4 italic">
                        Jérémie G.<br>
                        Fondateur d'eXtragone
                    </p>
                </div>
            </div>
        </section>

        <!-- Pourquoi eXtragone existe -->
        <section class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700">
            <h2 class="md:text-lg uppercase tracking-wider font-bold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-lightbulb"></i>
                Pourquoi eXtragone existe ?
            </h2>
            <div class="space-y-4 text-gray-700 dark:text-gray-300 leading-relaxed">
                <p>
                    <strong>Le constat :</strong> Face à l'omniprésence des outils américains (Google, Notion, ChatGPT...), 
                    il était devenu difficile de savoir quels outils étaient français et 
                    quelles alternatives locales existaient.
                </p>
                <p>
                    Les comparateurs existants manquaient de clarté sur l'origine géographique des solutions, 
                    et aucune plateforme ne mettait réellement en avant <strong>la French Tech</strong> et 
                    <strong>la souveraineté numérique européenne</strong>.
                </p>
                <p>
                    <strong>L'intention du projet :</strong> Créer une plateforme simple, transparente et indépendante 
                    pour référencer les meilleurs outils numériques en mettant en lumière les 
                    <strong>solutions françaises conformes RGPD</strong>, hébergées en France.
                </p>
                <p class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                    <strong>L'objectif :</strong> Aider les professionnels et particuliers à faire des choix éclairés 
                    tout en soutenant l'économie locale et la protection des données personnelles.
                </p>
            </div>
        </section>

        <!-- Comment les outils sont sélectionnés -->
        <section class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700">
            <h2 class="md:text-lg uppercase tracking-wider font-bold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-filter"></i>
                Comment les outils sont sélectionnés ?
            </h2>
            <div class="space-y-6">
                <div>
                    <h3 class="font-bold md:text-lg mb-2">
                        Critères d'acceptation
                    </h3>
                    <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-green-500 dark:text-green-400"></i>
                            <span><strong>Outil fonctionnel et accessible</strong> : L'outil doit être en production et utilisable publiquement</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-green-500 dark:text-green-400"></i>
                            <span><strong>Valeur ajoutée claire</strong> : L'outil doit résoudre un besoin réel et apporter une solution concrète</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-green-500 dark:text-green-400"></i>
                            <span><strong>Popularité ou potentiel</strong> : Outils reconnus ou startups prometteuses avec traction</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-green-500 dark:text-green-400"></i>
                            <span><strong>Testabilité</strong> : Je teste personnellement chaque outil, parfois de manière approfondie</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-green-500 dark:text-green-400"></i>
                            <span><strong>Vérification assistée</strong> : Les fiches sont ensuite vérifiées et complétées par un agent IA (Mistral)</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold text-lg mb-2">
                        Critères de refus
                    </h3>
                    <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-times mt-1 text-red-500 dark:text-red-400"></i>
                            <span><strong>Outils abandonnés</strong> : Projets non maintenus ou sites inactifs</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-times mt-1 text-red-500 dark:text-red-400"></i>
                            <span><strong>Startups trop précoces</strong> : MVP sans utilisateurs réels ou landing pages sans produit</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-times mt-1 text-red-500 dark:text-red-400"></i>
                            <span><strong>Outils illégaux ou contraires à l'éthique</strong> : Tout ce qui viole les lois françaises ou européennes</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-times mt-1 text-red-500 dark:text-red-400"></i>
                            <span><strong>Duplicatas évidents</strong> : Clones sans valeur ajoutée d'outils existants</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Ce qu'eXtragone n'est pas -->
        <section class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700">
            <h2 class="md:text-lg uppercase tracking-wider font-bold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-ban"></i>
                Ce qu'eXtragone n'est pas
            </h2>
            <div class="space-y-3 text-gray-700 dark:text-gray-300">
                <div class="flex items-start gap-3 rounded-lg">
                    <i class="fa-solid fa-circle-xmark text-orange-500 mt-1"></i>
                    <div>
                        <strong>Un comparateur exhaustif :</strong> Je ne prétends pas référencer TOUS les outils existants, 
                        mais une sélection pertinente et testée.
                    </div>
                </div>
                <div class="flex items-start gap-3 rounded-lg">
                    <i class="fa-solid fa-circle-xmark text-orange-500 mt-1"></i>
                    <div>
                        <strong>Un site sponsorisé :</strong> eXtragone est indépendant. 
                        Certains liens sont affiliés (transparence totale), mais cela n'influence pas la sélection des outils.
                    </div>
                </div>
                <div class="flex items-start gap-3 rounded-lg">
                    <i class="fa-solid fa-circle-xmark text-orange-500 mt-1"></i>
                    <div>
                        <strong>Un classement manipulé :</strong> Les outils ne sont pas classés selon des partenariats, 
                        mais selon leur pertinence, popularité et qualité réelle.
                    </div>
                </div>
            </div>
        </section>

        <!-- Ce que vous pouvez attendre -->
        <section class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700">
            <h2 class="md:text-lg uppercase tracking-wider font-bold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-handshake"></i>
                Ce que vous pouvez attendre
            </h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="p-4 rounded-lg">
                    <h3 class="font-bold mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-eye text-purple-500"></i>
                        Transparence totale
                    </h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Critères de sélection clairs, liens affiliés mentionnés, 
                        aucune manipulation des classements.
                    </p>
                </div>
                <div class="p-4 rounded-lg">
                    <h3 class="font-bold mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-sync text-purple-500"></i>
                        Mises à jour régulières
                    </h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Nouveaux outils ajoutés chaque semaine, fiches mises à jour, 
                        outils obsolètes retirés.
                    </p>
                </div>
                <div class="p-4 rounded-lg">
                    <h3 class="font-bold mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-book-open text-purple-500"></i>
                        Explications simples
                    </h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Descriptions claires, sans jargon technique inutile, 
                        accessibles à tous.
                    </p>
                </div>
                <div class="p-4 rounded-lg">
                    <h3 class="font-bold mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-purple-500"></i>
                        Limites assumées
                    </h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Je ne prétends pas tout savoir. Si je manque d'infos, 
                        je l'indique clairement.
                    </p>
                </div>
            </div>
        </section>

        <!-- Qui est derrière le projet -->
        <section class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-8 border-2 border-blue-200 dark:border-blue-700">
            <h2 class="md:text-lg uppercase tracking-wider font-bold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-rocket"></i>
                Qui est derrière le projet ?
            </h2>
            <div class="space-y-4 text-gray-700 dark:text-gray-300">
                <p>
                    <strong>eXtragone</strong> est un <strong>projet personnel</strong> et <strong>indépendant</strong>, 
                    créé et maintenu par Jérémie G.
                </p>
                <p>
                    Aucune équipe marketing, aucun investisseur, aucune pression commerciale. 
                    Juste une passion pour les outils numériques et la volonté de mettre en avant 
                    <strong>la French Tech</strong>.
                </p>
                <p class="bg-white dark:bg-slate-800 border-l-4 border-blue-500 p-4 rounded">
                    <strong>Un projet en évolution :</strong> eXtragone s'améliore constamment grâce à vos retours. 
                    N'hésitez pas à <a href="contact" class="border-b-2 border-blue-500 hover:border-dotted hover:text-blue-500 transition-colors duration-300">me contacter</a> 
                    pour proposer des outils ou signaler des erreurs.
                </p>
            </div>
        </section>

        <!-- Me retrouver ailleurs -->
        <section class="bg-white dark:bg-slate-800 rounded-xl p-8 border border-slate-200 dark:border-slate-700">
            <h2 class="md:text-lg uppercase tracking-wider font-bold mb-4 flex items-center gap-2">
                <i class="fa-solid fa-link"></i>
                Me retrouver ailleurs
            </h2>
            <div class="flex flex-wrap gap-4">
                <a href="https://www.linkedin.com/in/jeremie-galindo/" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="flex items-center gap-2 px-4 py-2 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                    <i class="fa-brands fa-linkedin"></i>
                    LinkedIn
                </a>
                <a href="https://x.com/nzr_g" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="flex items-center gap-2 px-4 py-2 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                    <i class="fa-brands fa-x-twitter"></i>
                    Twitter / X
                </a>
                <a href="https://www.innospira.fr" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="flex items-center gap-2 px-4 py-2 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                    <i class="fa-solid fa-globe"></i>
                    InnoSpira.fr
                </a>
            </div>
        </section>

        <!-- Footer de la page -->
        <div class="text-center text-sm text-gray-500 border-t border-slate-300 dark:border-slate-700 pt-6">
            <p>Projet créé en 2024.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>