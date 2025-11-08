<?php
if(!isset($base)) $base = "https://www.extrag.one"; //defaut)
if(!isset($url_canon)) $url_canon = "https://www.extrag.one"; //defaut)
if(!isset($recherche)) $recherche = "";
if(!isset($image_seo)) $image_seo = "$base/assets/img/image-og.png";

if(substr($image_seo, 0,5) !== 'https') {
  $new_url = 'https://extrag.one';
  if(substr($image_seo,-1) !== '/') $new_url .= '/';
  $image_seo = $new_url.$image_seo;
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
  <head>
    <title><?=$title?></title>
    <!-- Meta -->
    <meta charset="UTF-8" />
    
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

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?=$title?>">
    <meta name="twitter:description" content="<?=$description?>">
    <meta name="twitter:image" content="<?=$image_seo?>">
    <meta name="twitter:url" content="<?=$url_canon?>">

    <!-- Link -->
    <link rel="canonical" href="<?=$url_canon?>" />
    <link rel="icon" href="assets/img/extragone.ico">

    <!-- Balise Base -->
    <base href="<?=$base?>">

    <!-- Tailwind CSS -->
    <script src="assets/js/talwind3.4.16.js"></script>
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

    <?php
    if(!is_admin_logged_in()) {
      ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Y899RD39ZS"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-Y899RD39ZS');
    </script>
    <?php
    }
    ?>
    <!-- Gradiant CSS -->
    <link rel="stylesheet" href="assets/css/gradient-background.css">

    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    
  </head>
  <body class="bg-slate-50 text-slate-900 dark:bg-gray-900 dark:text-white min-h-screen flex flex-col">

    <!-- Gradiant Background -->
    <div class="gradient-bg"></div>
    
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
            <a href="" class="transition-transform duration-300 hover:scale-105 hover:brightness-110"><img src="assets/img/logo.webp" class="w-[50px]" alt="Logo d'Extragone"></a>
            <a href="" class="mt-2">eXtragone</a>
        </div>

        <!-- Menu (desktop) -->
        <nav class="hidden md:flex space-x-8 pr-4">
          <a href="outils" class="hover:text-blue-500 transition-colors duration-300">Liste des outils</a>
          <a href="categories" class="hover:text-blue-500 transition-colors duration-300">Catégories</a>
          <a href="ajouter" class="hover:text-blue-500 transition-colors duration-300">Ajouter un outil</a>
          <?php if(is_admin_logged_in()): ?>
            <a href="admin" class="text-blue-500 hover:text-blue-600 font-bold transition-colors duration-300 flex items-center gap-1">Admin</a>
          <?php endif; ?>
        </nav>

        <!-- Menu mobile -->
        <nav id="mobile-menu" class="md:hidden hidden mt-4 flex flex-col space-y-2">
            <a href="outils" class="hover:text-blue-500 transition-colors duration-300">Liste des outils</a>
            <a href="categories" class="hover:text-blue-500 transition-colors duration-300">Catégories</a>
            <a href="ajouter" class="hover:text-blue-500 transition-colors duration-300">Ajouter un outil</a>
        </nav>

      </div>


      <div id="side_desktop" class="hidden md:flex items-center space-x-4">
        <!-- Formulaire de recherche -->
        <form method="GET" action="outils.php">
            <input type="text" value="<?php echo $recherche; ?>" name="q" placeholder="Chercher un outil&hellip;" class="px-3 py-1 rounded-md text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring" />
        </form>

        <!-- Thème clair/foncé -->
        <span id="themeToggle" class="cursor-pointer">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
        </span>

      </div>
    </header>
    <main class="flex-grow">