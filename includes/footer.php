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
$page_php = basename($_SERVER['PHP_SELF']);

// Si page contact : Captcha
if($page_php == 'contact.php') {
    echo '
        <!-- Google reCaptcha -->
        <script src="https://www.google.com/recaptcha/api.js?render=6Le4HBsrAAAAAIarr1KSSiPaloaocI6Arm_VQ2tQ"></script>
    ';
}

// Si page outil : modal Image
// Si j'ajoute des pages : créer un fichier .js pour le script
if($page_php == 'outil.php') {
    ?>
    <!-- Modal Popup -->
    <div id="imageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 opacity-0 invisible transition-all duration-300 ease-out">
        <div class="absolute inset-0" onclick="closeImageModal()"></div>
        <div class="relative max-w-screen-lg max-h-screen-lg mx-4 transform scale-95 transition-transform duration-300 ease-out">
            <button onclick="closeImageModal()" 
                    class="absolute -top-4 -right-4 z-10 w-10 h-10 bg-white hover:bg-gray-100 rounded-full shadow-lg flex items-center justify-center transition-colors duration-200 text-gray-700 hover:text-gray-900">
                <i class="fas fa-times text-lg"></i>
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
