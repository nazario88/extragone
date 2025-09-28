<?php
$base = "https://nomi.extrag.one"; //defaut
if(!isset($url_canon)) $url_canon = "https://nomi.extrag.one"; //defaut
if(!isset($recherche)) $recherche = "";
if(!isset($image_seo)) $image_seo = "https://www.extrag.one/assets/img/image-og.png"; // a changer apres MEP
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
    <link rel="icon" href="https://www.extrag.one/assets/img/extragone.ico">

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
        const theme = localStorage.getItem('theme');
        if (theme === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      })();
    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Y899RD39ZS"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-Y899RD39ZS');
    </script>

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    
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
            <a href="<?=$base?>" class="transition-transform duration-300 hover:scale-105 hover:brightness-110"><img src="https://www.extrag.one/assets/img/logo.webp" class="w-[50px]" alt="Logo d'Extragone"></a>
            <div class="space-y-0">
                <h1><a href="<?=$base?>">Nomi</a></h1>
                <a href="https://www.extrag.one" class="flex items-center gap-1 font-mono text-xs/6 font-medium tracking-widest text-gray-500 uppercase dark:text-gray-400 hover:font-bold transition">
                    by eXtrag.one
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>
        </div>

        <!-- Menu (desktop) -->
        <nav class="hidden md:flex space-x-8">
          <a href="outils" class="hover:text-blue-500 transition-colors duration-300">Liste des outils</a>
          <a href="categories" class="hover:text-blue-500 transition-colors duration-300">Catégories</a>
          <a href="ajouter" class="hover:text-blue-500 transition-colors duration-300">Ajouter un outil</a>
        </nav>

        <!-- Menu mobile -->
        <nav id="mobile-menu" class="md:hidden hidden mt-4 flex flex-col space-y-2">
            <a href="outils" class="hover:text-blue-500 transition-colors duration-300">Liste des outils</a>
            <a href="categories" class="hover:text-blue-500 transition-colors duration-300">Catégories</a>
            <a href="ajouter" class="hover:text-blue-500 transition-colors duration-300">Ajouter un outil</a>
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