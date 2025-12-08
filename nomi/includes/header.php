<?php
$base = "https://nomi.extrag.one"; //defaut
if(!isset($url_canon)) $url_canon = "https://nomi.extrag.one"; //defaut
if(!isset($image_seo)) $image_seo = "https://nomi.extrag.one/images/nomi-og.png"; // a changer apres MEP
if(!isset($noindex)) $noindex = FALSE;
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
  <head>
    <title><?=$title?></title>
    <!-- Meta -->
    <meta charset="UTF-8" />

    <?php
    if($noindex) {
      echo '
    <meta name="robots" content="noindex">
      ';
    }
    ?>
    
    <meta name="application-name" content="eXtragone"/>
    <meta name="author"           content="InnoSpira"/>
    <meta name="description"      content="<?=$description?>"/>
    <meta name="viewport"         content="width=device-width, initial-scale=1">

    <meta property="og:site_name"   content="eXtragone">
    <meta property="og:title"       content="<?=$title?>">
    <meta property="og:description" content="<?=$description?>">
    <meta property="og:image"       content="<?=$image_seo?>">
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="<?=$url_canon?>">


    <!-- Link -->
    <link rel="canonical" href="<?=$url_canon?>" />
    <link rel="icon" href="images/favicon.ico">

    <!-- Balise Base -->
    <base href="<?=$base?>">

    <!-- Tailwind CSS -->
    <script src="https://www.extrag.one/assets/js/talwind3.4.16.js"></script>
    <script>
      // Configuration Tailwind 
      tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
              colors: {
                'primary': '#335ca3',
                'secondary': '#d83b39'
              }
            }
        }
      };
      // Gestion du thème
      (function () {
        // Fonction pour récupérer un cookie
        function getCookie(name) {
          const nameEQ = name + "=";
          const ca = document.cookie.split(';');
          for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
          }
          return null;
        }
        
        const theme = getCookie('theme') || localStorage.getItem('theme') || 'light';
        
        if (theme === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      })();
    </script>

    <!-- Statistiques -->
    <script src="https://www.extrag.one/assets/js/analytics.js" defer></script>

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://www.extrag.one/assets/fontawesome/css/all.min.css">
    
  </head>
  <body class="bg-slate-50 text-slate-900 dark:bg-gray-900 dark:text-white min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-gray-100 dark:bg-slate-950 shadow px-6 py-4 flex items-center justify-between">
      <div class="container mx-auto flex items-center justify-between px-4">

        <!-- Bouton hamburger (mobile uniquement) -->
        <button class="md:hidden" id="menu-toggle">
          <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- Titre -->
        <div id="menu-title" class="flex gap-3 text-2xl bg-gradient-to-r from-primary to-slate-500 dark:from-slate-600 dark:to-slate-300 text-transparent bg-clip-text">
            <a href="<?=$base?>" class="transition-transform duration-300 hover:scale-105 hover:brightness-110"><img src="images/nomi_logo.webp" class="w-[50px]" alt="Logo d'Extragone"></a>
            <div class="space-y-0">
                <h1><a href="<?=$base?>">Nomi</a></h1>
                <a href="https://www.extrag.one" class="flex items-center gap-1 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400 hover:font-bold transition">
                    by eXtrag.one
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>
        </div>

        <!-- Menu (desktop) -->
        <nav class="hidden md:flex space-x-8 pr-4">
          <a href="https://www.extrag.one" title="eXtragone, trouve un outil et son alternative française" class="hover:text-blue-500 transition-colors duration-300">Retour à eXtragone</a>
          <a href="https://nomi.extrag.one" title="Génère des noms pour ton projet avec Nomi" class="hover:text-blue-500 transition-colors duration-300">Générer des noms</a>
        </nav>

        <!-- Menu mobile -->
        <nav id="mobile-menu" class="md:hidden hidden mt-4 flex flex-col space-y-2">
            <a href="outils" title="eXtragone, trouve un outil et son alternative française"  class="hover:text-blue-500 transition-colors duration-300">Retour à eXtragone</a>
            <a href="categories" title="Génère des noms pour ton projet avec Nomi" class="hover:text-blue-500 transition-colors duration-300">Générer des noms</a>
        </nav>

      </div>


      <div id="side_desktop" class="hidden md:flex items-center space-x-4">
        <!-- Thème clair/foncé -->
        <span id="themeToggle" class="cursor-pointer">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
        </span>
      </div>
    </header>
    <main class="flex-grow">