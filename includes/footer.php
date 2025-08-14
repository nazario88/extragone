    </main>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-slate-950 shadow mt-6 px-6 py-4 flex items-center justify-between">
        <div class="container mx-auto flex flex-wrap items-center justify-between">
            <div class="w-full md:w-1/2 md:text-center md:mb-0 mb-8">
                <?php //"Le soleil inonde l'eXtragone ☀️, réchauffant les coeurs ❤️ !"</p> ?>
                <p class="text-center text-xs">
                    <img class="mx-auto w-auto h-auto inline" src="assets/img/extrag.one.png" alt="eXtragone, portail pour trouver des outils et équivalents français" title="eXtrag.one" />
                    <a href="https://chromewebstore.google.com/detail/fjcfkhkhplfpngmffdkfekcpelkaggof?utm_source=item-share-cb" target="_blank" rel="noopener noreferrer" class="hover:underline ml-4">
                        <i class="fa-brands fa-chrome"></i> Extension Google Chrome
                    </a>
                </p>

                <?php // codé en 2 casses croutes ? 🥖🧀 aussi verre de vin rouge 🍷 ?>
            </div>
            <div class="w-full md:w-1/2 md:text-center md:mb-0 mb-8">
                <ul class="list-reset flex justify-center flex-wrap text-xs gap-3">
                    <li class="hidden md:block"><a class="hover:text-blue-500 transition-colors duration-300" href="ajouter" rel="nofollow">Proposer un outil</a></li>
                    <li class="hidden md:block"><a class="hover:text-blue-500 transition-colors duration-300" href="contact" rel="nofollow">Contact</a></li>
                    &#151;
                    <li><a class="hover:text-blue-500 transition-colors duration-300" href="https://www.innospira.fr" title="Tests et partages d'outils : Innovation & Inspiration">🟡 InnoSpira</a></li>
                    <li><a class="hover:text-blue-500 transition-colors duration-300" href="https://dailyheroes.io" title="Découvrir le pouvoir des habitudes pour devenir un héros !">🟠 DailyHeroes</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Fonctions -->    
    <script type="text/javascript" src="assets/js/fonctions.js"></script>
<?php
if(basename($_SERVER['PHP_SELF']) == 'contact.php') {
    echo '
        <!-- Google reCaptcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=6Le4HBsrAAAAAIarr1KSSiPaloaocI6Arm_VQ2tQ"></script>
    ';
}
?>
</body>
</html>
