</main>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-slate-950 shadow mt-6 px-6 py-8">
        <div class="container mx-auto">
            
            <!-- Main Footer Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                
                <!-- Column 1: About -->
                <div>
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        À propos d'eXtragone
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Plateforme de référencement des meilleurs outils numériques et alternatives françaises. 
                        Soutiens la French Tech avec des solutions locales et conformes RGPD.
                    </p>
                    <a href="https://chromewebstore.google.com/detail/fjcfkhkhplfpngmffdkfekcpelkaggof?utm_source=footer" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition text-sm">
                        <i class="fa-brands fa-chrome"></i>
                        Extension Chrome
                    </a>
                </div>

                <!-- Column 2: All Categories -->
                <div>
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-folder-open text-blue-500"></i>
                        Toutes les catégories
                    </h3>
                    <ul class="space-y-2 text-sm">
                        <?php
                        $stmt = $pdo->query('
                            SELECT c.nom, c.slug, c.class_icon, COUNT(t.id) as nb_tools
                            FROM extra_tools_categories c
                            LEFT JOIN extra_tools t ON c.id = t.categorie_id AND t.is_valid = 1
                            GROUP BY c.id
                            ORDER BY c.nom ASC
                        ');
                        $all_cats = $stmt->fetchAll();
                        
                        foreach ($all_cats as $cat):
                        ?>
                        <li>
                            <a href="outils/categorie/<?= $cat['slug'] ?>" 
                               class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition flex items-center gap-2 group">
                                <i class="<?= $cat['class_icon'] ?> text-xs text-gray-400 group-hover:text-blue-500"></i>
                                <?= htmlspecialchars($cat['nom']) ?>
                                <span class="text-xs text-gray-400">(<?= $cat['nb_tools'] ?>)</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Column 3: Top Alternatives (Static SEO Links) -->
                <div>
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <?= $flag_FR ?>
                        Alternatives françaises
                    </h3>
                    <ul class="space-y-2 text-sm">
                        <?php
                        $top_alternatives = [
                            'chatgpt' => 'ChatGPT',
                            'notion' => 'Notion',
                            'slack' => 'Slack',
                            'google-drive' => 'Google Drive',
                            'trello' => 'Trello',
                            'gmail' => 'Gmail',
                            'ideogram' => 'Ideogram',
                            'Bubble' => 'Bubble'
                        ];
                        
                        foreach ($top_alternatives as $slug => $name):
                        ?>
                        <li>
                            <a href="alternative-francaise-<?= $slug ?>" 
                            class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                                Alternative à <?= htmlspecialchars($name) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Column 4: Ecosystem & Resources -->
                <div>
                    <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-rocket text-blue-500"></i>
                        Ressources
                    </h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="articles" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition flex items-center gap-2">
                                <i class="fa-solid fa-newspaper text-xs"></i>
                                Articles & Guides
                            </a>
                        </li>
                        <li>
                            <a href="https://projets.extrag.one" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition flex items-center gap-2">
                                <i class="fa-solid fa-folder-open text-xs"></i>
                                Projets communauté
                            </a>
                        </li>
                        <li>
                            <a href="https://nomi.extrag.one" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition flex items-center gap-2">
                                <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                Nomi - Générateur IA
                            </a>
                        </li>
                        <li>
                            <a href="ajouter" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition flex items-center gap-2">
                                <i class="fa-solid fa-plus text-xs"></i>
                                Proposer un outil
                            </a>
                        </li>
                        <li>
                            <a href="contact" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition flex items-center gap-2">
                                <i class="fa-solid fa-envelope text-xs"></i>
                                Contact
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- SEO-Rich Section: Expert Text -->
            <div class="border-t border-slate-300 dark:border-slate-700 pt-6 mb-6">
                <div class="max-w-4xl mx-auto text-center">
                    <h3 class="font-bold text-lg mb-3 text-gray-700 dark:text-gray-300 flex items-center justify-center gap-2">
                        <?= $flag_FR ?>
                        Pourquoi choisir des outils numériques français ?
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        <strong>eXtragone</strong> référence les meilleures <strong>alternatives françaises</strong> aux outils web populaires. 
                        En privilégiant des <strong>solutions made in France</strong>, vous soutenez l'<strong>économie locale</strong>, 
                        bénéficiez d'un <strong>support en français</strong> et garantissez la <strong>conformité RGPD</strong> de vos données. 
                        Nos outils français couvrent toutes les catégories : <strong>intelligence artificielle</strong>, 
                        <strong>productivité</strong>, <strong>marketing digital</strong>, <strong>collaboration</strong>, 
                        <strong>gestion de projet</strong> et bien plus. Découvrez des <strong>logiciels français innovants</strong>, 
                        hébergés en France ou en Europe, pour une <strong>souveraineté numérique</strong> totale.
                    </p>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-slate-300 dark:border-slate-700 pt-4 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
                <div class="mb-2 md:mb-0">
                    © <?= date('Y') ?> eXtragone - 
                    <!-- <a href="mentions-legales" class="hover:text-blue-500 transition">Mentions légales</a> / A propos ? -->
                </div>
                <div class="flex items-center gap-2">
                    <img src="assets/img/logo.webp" alt="eXtragone" class="h-6">
                    <span>Hébergé en France par <a href="outil/ovh" class="hover:text-blue-500 transition">OVH</a></span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Fonctions -->    
    <script type="text/javascript" src="assets/js/fonctions.js"></script>
<?php
$page_php = basename($_SERVER['PHP_SELF']);

// Si page contact : Captcha
if($page_php == 'contact.php') {
    echo '
        <!-- Google reCaptcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=6Le4HBsrAAAAAIarr1KSSiPaloaocI6Arm_VQ2tQ"></script>
    ';
}

// Si page outil : modal Image
if($page_php == 'outil.php') {
    ?>
    <!-- Modal Popup -->
    <div id="imageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 opacity-0 invisible transition-all duration-300 ease-out">
        <div class="absolute inset-0" onclick="closeImageModal()"></div>
        <div class="relative max-w-screen-lg max-h-screen-lg mx-4 transform scale-95 transition-transform duration-300 ease-out">
            <button onclick="closeImageModal()" 
                    class="absolute -top-4 -right-4 z-10 w-10 h-10 bg-white hover:bg-gray-100 rounded-full shadow-lg flex items-center justify-center transition-colors duration-200 text-gray-700 hover:text-gray-900">
                <i class="fa-solid fa-times text-lg"></i>
            </button>
            <img id="modalImage" 
                class="max-w-full max-h-[90vh] w-auto h-auto rounded-lg shadow-2xl" 
                src="" alt="">
        </div>
    </div>

    <!-- Script -->
    <script>
        function openImageModal(imgElement) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            
            modalImage.src = imgElement.src;
            modalImage.alt = imgElement.alt;
            
            modal.classList.remove('invisible', 'opacity-0');
            modal.classList.add('opacity-100');
            modal.querySelector('.relative').classList.remove('scale-95');
            modal.querySelector('.relative').classList.add('scale-100');
            
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modal.querySelector('.relative').classList.remove('scale-100');
            modal.querySelector('.relative').classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('invisible');
                document.body.style.overflow = '';
            }, 300);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });

        document.getElementById('modalImage').addEventListener('click', function(event) {
            event.stopPropagation();
        });
    </script>
    <?php
}
?>
</body>
</html>