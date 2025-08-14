<?php
include 'includes/config.php';

$title = "Trouver des outils et des alternatives françaises !";
$description = "Extragone recense les meilleures alternatives françaises aux outils web. Toi aussi, soutiens la French tech' !";
include 'includes/header.php';

?>

<div class="w-full lg:w-1/2 mx-auto">
    <!-- Accroche -->
    <p class="p-4 m-4 mx-auto text-xl md:text-4xl  text-center font-bold tracking-tight dark:text-slate-500">
        <img src="assets/img/logo.webp" class="w-1/3 mx-auto" alt="Logo d'Extragone">
        Trouve <span class="dark:text-white font-semibold">l'équivalent français</span> d'un outil !
    </p>

  <!-- Barre de recherche -->
<div class="w-full mx-auto mb-8 sm:px-6 lg:px-8 relative">
    <form action="outils.php">
        <label
            class="mx-auto mt-8 relative bg-white dark:bg-slate-800 flex flex-col md:flex-row items-center justify-center border dark:border-slate-500 py-2 px-2 rounded-2xl gap-2 shadow-2xl focus-within:border-gray-300 dark:focus-within:border-slate-500"
            for="search-bar">

            <input id="search-bar" placeholder="Exemple: ChatGPT, Slack, Notion, &hellip;" name="q"
                class="px-6 py-2 w-full rounded-md flex-1 outline-none bg-white dark:bg-slate-800"   oninput="handleSearch(this.value)" required="">
            <button type="submit"
                class="w-full md:w-auto px-6 py-3 bg-blue-500 border-blue-600 dark:bg-slate-900 dark:border-slate-950 text-white fill-white active:scale-95 duration-100 border will-change-transform overflow-hidden relative rounded-xl transition-all">
                <div class="flex items-center transition-all opacity-1">
                    <span class="text-sm font-semibold whitespace-nowrap truncate mx-auto">
                        Chercher
                    </span>
                </div>
            </button>
        </label>
    </form>
    <!-- Résultats dynamiques -->
    <div id="search-results" class="hidden w-full max-w-3xl mx-auto bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-600 rounded-2xl shadow-lg absolute">
      <!-- Les résultats JS viendront ici -->
    </div>
</div>


    <!-- Separateur -->
    <hr class="mt-16 h-[1px] border-0 bg-gradient-to-r from-primary via-white to-secondary">

    <!-- Explications -->
    <div class="w-full px-5 py-5">
        <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">extragone, c'est quoi ?</p>
        <p class="p-2 m-2">
            eXtragone a pour objectif de recenser les outils web les plus utilisés, de les classer par catégorie et de les rendre facilement accessibles. Mais surtout, son moteur de recherche permet de <span class="font-bold">trouver des alternatives françaises</span>, pour mettre en valeur nos solutions locales et favoriser l’usage de produits made in France.
        </p>
        <a href="outils" class="border-b-2 border-blue-500 hover:border-dotted">&rarr; Consulter l'ensemble des outils</a>
    </div>

    <!-- Plugin -->
    <div class="w-full px-5 py-5">
        <p class="flex items-center gap-2 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400">Un plugin, vraiment ?</p>
        <p class="p-2 m-2">
            Pour trouver systématiquement les alternatives françaises, quoi de mieux qu'une extension Google Chrome ? L'icône clignote pour vous informer qu'un équivalent existe, et d'un simple clic, vous pouvez consulter les propositions. <span class="font-bold">De quoi ne plus passer à côté des outils locaux !</span> L'extension est légère, rapide, et ne collecte aucune donnée personnelle. N'hésitez pas à donner votre avis sur la fiche Google pour encourager le projet !
        </p>
        <a href="https://chromewebstore.google.com/detail/fjcfkhkhplfpngmffdkfekcpelkaggof?utm_source=item-share-cb" target="_blank" class="border-b-2 border-blue-500 hover:border-dotted">&rarr; Consulter l'extension Google Chrome</a>
    </div>
</div>

<?php
include 'includes/footer.php';
?>