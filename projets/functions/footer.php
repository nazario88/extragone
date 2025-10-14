</main>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-slate-950 shadow mt-12 px-6 py-6">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Colonne 1 : À propos -->
                <div>
                    <h3 class="font-bold text-lg mb-3">À propos</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Une plateforme communautaire pour partager et découvrir des projets créatifs, avec des revues détaillées par notre équipe.
                    </p>
                </div>
                
                <!-- Colonne 2 : Liens rapides -->
                <div>
                    <h3 class="font-bold text-lg mb-3">Liens rapides</h3>
                    <ul class="text-sm space-y-2">
                        <li><a href="https://projets.extrag.one" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors">Tous les projets</a></li>
                        <li><a href="https://projets.extrag.one/top-reviewers" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors">Top Reviewers</a></li>
                        <li><a href="https://projets.extrag.one/soumettre" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors">Soumettre un projet</a></li>
                        <li><a href="https://projets.extrag.one/devenir-reviewer" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors">Devenir reviewer</a></li>
                    </ul>
                </div>
                
                <!-- Colonne 3 : Communauté -->
                <div>
                    <h3 class="font-bold text-lg mb-3">Communauté eXtragone</h3>
                    <ul class="text-sm space-y-2">
                        <li>
                            <a href="https://www.extrag.one" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors">
                                🔵 eXtragone - Outils & Alternatives
                            </a>
                        </li>
                        <li>
                            <a href="https://nomi.extrag.one" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors">
                                🟢 Nomi - Générateur de noms
                            </a>
                        </li>
                        <li>
                            <a href="https://www.innospira.fr" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors">
                                🟡 InnoSpira - Innovation & Inspiration
                            </a>
                        </li>
                        <li>
                            <a href="https://dailyheroes.io" class="text-gray-600 dark:text-gray-400 hover:text-blue-500 transition-colors">
                                🟠 DailyHeroes - Habitudes & Productivité
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Séparateur -->
            <hr class="my-6 border-slate-300 dark:border-slate-700">
            
            <!-- Copyright & Contact -->
            <div class="flex flex-col md:flex-row justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                <p>&copy; <?= date('Y') ?> eXtragone - Tous droits réservés</p>
                <div class="flex gap-4 mt-2 md:mt-0">
                    <a href="https://www.extrag.one/contact" class="hover:text-blue-500 transition-colors">Contact</a>
                    <a href="https://www.extrag.one/mentions-legales" class="hover:text-blue-500 transition-colors">Mentions légales</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Script pour le thème -->
    <script src="https://www.extrag.one/assets/js/fonctions.js"></script>
</body>
</html>